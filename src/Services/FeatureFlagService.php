<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\UnifiedEvent;
use Nafiur\WebAnalytics\Models\Analytics\FeatureFlag;
use Nafiur\WebAnalytics\Models\Analytics\FlagOverride;
use Nafiur\WebAnalytics\Models\Analytics\Website;

/**
 * Deterministic flag evaluation with website/tenant scoping. Every evaluation
 * is logged to lwa_events as `feature_flag_evaluated` with a hashed subject,
 * so exposure can be analyzed by the same bounded Trends engine.
 */
class FeatureFlagService
{
    /** @var array<string, bool> */
    private static array $logged = [];

    public function __construct(private EventStore $events)
    {
    }

    /** Clears the per-process dedupe cache; mainly useful in tests/long workers. */
    public function resetLogged(): void
    {
        self::$logged = [];
    }

    /** @return array<string, mixed> */
    public function evaluate(
        string $key,
        ?string $subjectKey = null,
        array $attributes = [],
        ?int $websiteId = null,
        ?string $tenantId = null,
        bool $log = true,
    ): array {
        $websiteId ??= (int) (config('laravel-web-analytics.analytics.website_id') ?: Website::query()->orderBy('domain')->value('id')) ?: 0;
        $tenantId ??= TenantResolver::resolveTrusted();

        $flag = FeatureFlag::query()
            ->forWebsite($websiteId > 0 ? $websiteId : null)
            ->forTenant($tenantId)
            ->where('key', $key)
            ->first();

        if ($flag === null) {
            return $this->result($key, false, null, 'missing', false, $websiteId, $tenantId, $subjectKey);
        }

        $subject = $subjectKey ?: 'anonymous';
        $bucket = $this->bucket($key, $subject);

        $override = FlagOverride::query()->where('flag_id', $flag->id)->where('subject_key', $subject)->first();
        if ($override !== null) {
            if ($override->variant === 'off') {
                return $this->result($key, false, null, 'override_off', $log, $websiteId, $tenantId, $subject);
            }

            return $this->result($key, true, $override->variant, 'override', $log, $websiteId, $tenantId, $subject);
        }

        $targeted = $this->matchesTargeting($flag, $subject, $attributes);
        if (! $flag->enabled && ! $targeted) {
            return $this->result($key, false, null, 'disabled', $log, $websiteId, $tenantId, $subject);
        }

        $rollout = (int) $flag->rollout;
        if (! $targeted && $rollout < 100 && $bucket >= $rollout) {
            return $this->result($key, false, null, 'rollout', $log, $websiteId, $tenantId, $subject);
        }

        $variant = $flag->type === 'multivariate'
            ? $this->pickVariant($flag, $bucket)
            : 'on';

        return $this->result(
            $key,
            true,
            $variant,
            $targeted ? 'targeted' : 'enabled',
            $log,
            $websiteId,
            $tenantId,
            $subject,
        );
    }

    /** @return array<int, FeatureFlag> */
    public function all(?int $websiteId, ?string $tenantId): array
    {
        return FeatureFlag::query()
            ->forWebsite($websiteId)
            ->forTenant($tenantId)
            ->orderBy('key')
            ->get()
            ->all();
    }

    /**
     * @param array<string, mixed> $data
     * @return array{ok: bool, message?: string, flag?: FeatureFlag}
     */
    public function save(array $data, ?FeatureFlag $flag = null): array
    {
        $key = trim((string) ($data['key'] ?? ''));
        $websiteId = (int) ($data['website_id'] ?? 0);
        $tenantId = trim((string) ($data['tenant_id'] ?? ''));

        if ($key === '' || strlen($key) > 100) {
            return ['ok' => false, 'message' => 'Flag key must be 1–100 characters.'];
        }

        $duplicate = FeatureFlag::query()
            ->forWebsite($websiteId > 0 ? $websiteId : null)
            ->forTenant($tenantId === '' ? null : $tenantId)
            ->where('key', $key)
            ->when($flag !== null, fn ($query) => $query->where('id', '!=', $flag->id))
            ->exists();
        if ($duplicate) {
            return ['ok' => false, 'message' => 'This flag key already exists in the selected scope.'];
        }

        $type = (string) ($data['type'] ?? 'boolean');
        if (! in_array($type, ['boolean', 'multivariate'], true)) {
            return ['ok' => false, 'message' => 'Unknown flag type.'];
        }

        $variants = $data['variants'] ?? [];
        if ($type === 'multivariate' && (! is_array($variants) || $variants === [])) {
            return ['ok' => false, 'message' => 'Multivariate flags need at least one variant.'];
        }

        $payload = [
            'website_id'  => $websiteId > 0 ? $websiteId : null,
            'tenant_id'   => $tenantId === '' ? null : $tenantId,
            'key'         => $key,
            'name'        => mb_substr(trim((string) ($data['name'] ?? $key)), 0, 150),
            'description' => $data['description'] ?? null,
            'type'        => $type,
            'variants'    => $type === 'multivariate' ? array_values($variants) : null,
            'enabled'     => ! empty($data['enabled']),
            'rollout'     => max(0, min(100, (int) ($data['rollout'] ?? 0))),
            'targeting'   => is_array($data['targeting'] ?? null) ? array_values($data['targeting']) : null,
            'created_by'  => $data['created_by'] ?? null,
        ];

        $flag ??= new FeatureFlag();
        $flag->fill($payload);
        $flag->save();

        return ['ok' => true, 'flag' => $flag];
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function result(
        string $key,
        bool $enabled,
        ?string $variant,
        string $reason,
        bool $log,
        int $websiteId,
        ?string $tenantId,
        ?string $subject,
    ): array {
        $result = [
            'key'     => $key,
            'enabled' => $enabled,
            'variant' => $variant,
            'reason'  => $reason,
        ];

        if ($log && $subject !== null) {
            $cacheKey = $websiteId . '|' . ($tenantId ?? '') . '|' . $key . '|' . $subject . '|' . ($variant ?? '') . '|' . $reason;
            if (! isset(self::$logged[$cacheKey])) {
                self::$logged[$cacheKey] = true;
                $now = CarbonImmutable::now();
                $this->events->insert([
                    new UnifiedEvent(
                        uuid: (string) Str::uuid(),
                        name: 'feature_flag_evaluated',
                        timestamp: $now,
                        distinctId: null,
                        anonymousId: 'flag_' . substr(hash('sha256', $subject), 0, 16),
                        sessionId: null,
                        websiteId: $websiteId,
                        properties: [
                            'flag_key'    => $key,
                            'variant'     => $variant,
                            'reason'      => $reason,
                            'subject_hash' => hash('sha256', $subject),
                        ],
                        context: [],
                        value: null,
                        createdAt: $now,
                    ),
                ]);
            }
        }

        return $result;
    }

    /** @param array<string, mixed> $attributes */
    private function matchesTargeting(FeatureFlag $flag, string $subject, array $attributes): bool
    {
        $rules = is_array($flag->targeting) ? $flag->targeting : [];
        if ($rules === []) {
            return false;
        }

        foreach ($rules as $rule) {
            if (! is_array($rule)) {
                return false;
            }
            $attribute = (string) ($rule['attribute'] ?? 'subject_key');
            $actual = $attribute === 'subject_key'
                ? $subject
                : (is_array($attributes[$attribute] ?? null) ? '' : (string) ($attributes[$attribute] ?? ''));
            $expected = $rule['value'] ?? null;
            $operator = (string) ($rule['operator'] ?? 'equals');

            $matches = match ($operator) {
                'equals'     => $actual === (string) $expected,
                'not_equals' => $actual !== (string) $expected,
                'contains'   => str_contains($actual, (string) $expected),
                'starts_with' => str_starts_with($actual, (string) $expected),
                'in'         => is_array($expected) && in_array($actual, $expected, true),
                default      => false,
            };
            if (! $matches) {
                return false;
            }
        }

        return true;
    }

    /** @return string|null */
    private function pickVariant(FeatureFlag $flag, int $bucket): ?string
    {
        $variants = is_array($flag->variants) ? $flag->variants : [];
        if ($variants === []) {
            return null;
        }

        $total = 0;
        foreach ($variants as $variant) {
            $total += max(0, (int) (is_array($variant) ? ($variant['weight'] ?? 0) : 0));
        }
        if ($total <= 0) {
            $first = reset($variants);

            return is_array($first) ? (string) ($first['key'] ?? 'on') : (string) $first;
        }

        $target = $bucket % $total;
        $running = 0;
        foreach ($variants as $variant) {
            $weight = max(0, (int) (is_array($variant) ? ($variant['weight'] ?? 0) : 0));
            $running += $weight;
            if ($target < $running) {
                return is_array($variant) ? (string) ($variant['key'] ?? 'on') : (string) $variant;
            }
        }

        $last = end($variants);

        return is_array($last) ? (string) ($last['key'] ?? 'on') : (string) $last;
    }

    private function bucket(string $key, string $subject): int
    {
        return hexdec(substr(hash('sha256', $key . ':' . $subject), 0, 8)) % 100;
    }
}
