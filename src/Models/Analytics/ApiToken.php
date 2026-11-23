<?php

namespace Nafiur\WebAnalytics\Models\Analytics;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class ApiToken extends Model
{
    protected $table = 'lwa_api_tokens';

    protected $guarded = ['id'];

    protected $casts = [
        'abilities'    => 'array',
        'expires_at'   => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope(new WebsiteScope());
    }

    public function website()
    {
        return $this->belongsTo(Website::class, 'website_id');
    }

    public function can(string $ability): bool
    {
        $abilities = $this->abilities ?? [];

        return in_array('*', $abilities, true) || in_array($ability, $abilities, true);
    }

    public static function hash(string $plainToken): string
    {
        return hash('sha256', $plainToken);
    }

    /** @param string[] $abilities */
    public static function issue(int $websiteId, string $name = 'API token', array $abilities = ['read'], ?\Carbon\Carbon $expiresAt = null): array
    {
        $plain = 'lwa_' . Str::random(40);

        $token = static::withoutGlobalScope(WebsiteScope::class)->create([
            'website_id' => $websiteId,
            'name'       => $name,
            'token_hash' => static::hash($plain),
            'abilities'  => $abilities,
            'expires_at' => $expiresAt,
        ]);

        return [$token, $plain];
    }
}
