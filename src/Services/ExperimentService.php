<?php

namespace Nafiur\WebAnalytics\Services;

use Carbon\CarbonImmutable;
use Nafiur\WebAnalytics\Contracts\EventStore;
use Nafiur\WebAnalytics\Data\EventQuery;
use Nafiur\WebAnalytics\Models\Analytics\Experiment;
use Nafiur\WebAnalytics\Models\Analytics\FeatureFlag;

/**
 * Reads A/B results from recorded lwa_events only. Exposures come from
 * `feature_flag_evaluated`; conversions come from the configured goal event
 * carrying the same variant property. No outcomes are inferred.
 */
class ExperimentService
{
    private const CAP = 20000;

    public function __construct(private EventStore $events)
    {
    }

    /** @return array<string, mixed> */
    public function results(?int $websiteId, ?string $tenantId, string $range, ?int $experimentId = null): array
    {
        $query = Experiment::query()->forWebsite($websiteId)->forTenant($tenantId);
        if ($experimentId !== null) {
            $query->where('id', $experimentId);
        }
        $experiments = $query->orderBy('name')->get();

        [$from, $to] = $this->window($range);
        $exposureEvents = $this->fetch($websiteId, 'feature_flag_evaluated', $from, $to);
        $exposures = [];
        foreach ($exposureEvents as $event) {
            $properties = $event->properties ?? [];
            $flagKey = (string) ($properties['flag_key'] ?? '');
            $variant = (string) ($properties['variant'] ?? '');
            if ($flagKey !== '' && $variant !== '') {
                $exposures[$flagKey][$variant] = ($exposures[$flagKey][$variant] ?? 0) + 1;
            }
        }

        $goalCache = [];
        $rows = [];
        foreach ($experiments as $experiment) {
            $goalEvent = (string) $experiment->goal_event;
            if (! isset($goalCache[$goalEvent])) {
                $goalCache[$goalEvent] = $this->fetch($websiteId, $goalEvent, $from, $to);
            }
            $conversions = [];
            foreach ($goalCache[$goalEvent] as $event) {
                $properties = $event->properties ?? [];
                $variant = (string) ($properties[$experiment->variant_property] ?? '');
                if ($variant !== '') {
                    $conversions[$variant] = ($conversions[$variant] ?? 0) + 1;
                }
            }

            $flag = FeatureFlag::query()
                ->forWebsite($websiteId)
                ->forTenant($tenantId)
                ->where('key', $experiment->flag_key)
                ->first();
            $variants = [];
            foreach (is_array($flag?->variants) ? $flag->variants : [] as $variant) {
                $key = is_array($variant) ? (string) ($variant['key'] ?? '') : (string) $variant;
                if ($key !== '') {
                    $variants[] = $key;
                }
            }
            foreach (array_keys($exposures[$experiment->flag_key] ?? []) as $key) {
                if (! in_array($key, $variants, true)) {
                    $variants[] = $key;
                }
            }

            $control = (string) $experiment->control_variant;
            $variantRows = [];
            foreach ($variants as $variant) {
                $variantRows[] = $this->variantStats(
                    $variant,
                    $variants,
                    $exposures[$experiment->flag_key] ?? [],
                    $conversions,
                    $control,
                );
            }
            usort($variantRows, fn (array $a, array $b): int => $b['exposures'] <=> $a['exposures']);

            $rows[] = [
                'id'              => $experiment->id,
                'name'            => $experiment->name,
                'flag_key'        => $experiment->flag_key,
                'goal_event'      => $goalEvent,
                'variant_property' => $experiment->variant_property,
                'control_variant' => $control,
                'status'          => $experiment->status,
                'variants'        => $variantRows,
                'exposure_events' => count($exposureEvents),
                'goal_events'     => count($goalCache[$goalEvent]),
            ];
        }

        return [
            'range'       => $range,
            'experiments' => $rows,
            'definitions' => [
                'source'      => 'lwa_events only; exposures are feature_flag_evaluated, conversions are the configured goal event.',
                'sample'      => 'Each event name is bounded to the 20,000 most recent matching rows. Counts are lower bounds when a cap is reached.',
                'conversion'  => 'Conversion rate is goal events / exposures for the same variant property value.',
                'lift'        => 'Lift is (variant rate - control rate) / control rate. It is hidden when the control rate is zero.',
                'significance' => 'Two-sided two-proportion z-test with p < 0.05, at least 30 exposures and 5 conversions in both groups.',
                'attribution' => 'The goal event must carry the configured variant property; no conversion is inferred from navigation.',
            ],
        ];
    }

    /** @return array<int, \Nafiur\WebAnalytics\Models\Analytics\AnalyticEvent> */
    private function fetch(?int $websiteId, string $name, string $from, string $to): array
    {
        return iterator_to_array($this->events->query(new EventQuery(
            websiteId: (int) $websiteId,
            name: $name,
            from: $from,
            to: $to,
            limit: self::CAP,
        )));
    }

    /** @return array{0: string, 1: string} */
    private function window(string $range): array
    {
        $days = ['7d' => 7, '30d' => 30, '90d' => 90][$range] ?? 3650;
        $now = CarbonImmutable::now();

        return [$now->subDays($days)->toIso8601String(), $now->toIso8601String()];
    }

    /**
     * @param array<int, string> $allVariants
     * @param array<string, int> $exposures
     * @param array<string, int> $conversions
     * @return array<string, mixed>
     */
    private function variantStats(
        string $variant,
        array $allVariants,
        array $exposures,
        array $conversions,
        string $control,
    ): array {
        $exposure = (int) ($exposures[$variant] ?? 0);
        $conversion = (int) ($conversions[$variant] ?? 0);
        $rate = $exposure > 0 ? $conversion / $exposure : 0.0;

        $controlExposure = (int) ($exposures[$control] ?? 0);
        $controlConversion = (int) ($conversions[$control] ?? 0);
        $controlRate = $controlExposure > 0 ? $controlConversion / $controlExposure : 0.0;

        $isControl = $variant === $control;
        $lift = ($isControl || $controlRate <= 0)
            ? null
            : round((($rate - $controlRate) / $controlRate) * 100, 2);

        $pValue = null;
        $significant = false;
        if (! $isControl && $exposure > 0 && $controlExposure > 0) {
            $pooled = ($conversion + $controlConversion) / ($exposure + $controlExposure);
            $se = sqrt($pooled * (1 - $pooled) * (1 / $exposure + 1 / $controlExposure));
            if ($se > 0) {
                $z = ($rate - $controlRate) / $se;
                $pValue = round(2 * (1 - $this->normalCdf(abs($z))), 4);
                $significant = $exposure >= 30 && $conversion >= 5
                    && $controlExposure >= 30 && $controlConversion >= 5
                    && $pValue < 0.05;
            }
        }

        return [
            'variant'      => $variant,
            'is_control'   => $isControl,
            'exposures'    => $exposure,
            'conversions'  => $conversion,
            'rate'         => round($rate * 100, 2),
            'lift_percent' => $lift,
            'p_value'      => $pValue,
            'significant'  => $significant,
        ];
    }

    private function normalCdf(float $x): float
    {
        $t = 1 / (1 + 0.2316419 * $x);
        $d = 0.3989423 * exp(-$x * $x / 2);
        $probability = $d * $t * (0.3193815 + $t * (-0.3565638 + $t * (1.781478 + $t * (-1.821256 + $t * 1.330274))));

        return $x > 0 ? 1 - $probability : $probability;
    }
}
