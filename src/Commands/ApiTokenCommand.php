<?php

namespace Nafiur\WebAnalytics\Commands;

use Illuminate\Console\Command;
use Nafiur\WebAnalytics\Models\Analytics\ApiToken;
use Nafiur\WebAnalytics\Services\WebsiteScope;

class ApiTokenCommand extends Command
{
    /** @var string[] */
    protected $aliases = ['analytics:token'];

    protected $signature = 'lwa:token
                            {--create : Issue a new token}
                            {--list : List tokens (default)}
                            {--rotate= : Token ID to rotate}
                            {--revoke= : Token ID to revoke}
                            {--website= : Website ID for new tokens}
                            {--name= : Token name}
                            {--abilities=read : Comma-separated abilities: read,write,admin or *}';

    protected $description = 'Manage analytics REST API tokens';

    public function handle(): int
    {
        if ($rotate = $this->option('rotate')) {
            $token = $this->find((int) $rotate);
            if ($token === null) {
                $this->error('Token not found.');
                return self::FAILURE;
            }
            $plain = $token->rotate();
            $this->info('Token rotated. Copy the new value now — it is shown only once:');
            $this->line('<info>' . $plain . '</info>');
            return self::SUCCESS;
        }

        if ($revoke = $this->option('revoke')) {
            $token = $this->find((int) $revoke);
            if ($token === null) {
                $this->error('Token not found.');
                return self::FAILURE;
            }
            $token->revoke();
            $this->info('Token revoked.');
            return self::SUCCESS;
        }

        if ($this->option('create')) {
            $websiteId = (int) ($this->option('website') ?: config('laravel-web-analytics.analytics.website_id', 0));
            if ($websiteId <= 0) {
                $this->error('Pass --website or set WEB_ANALYTICS_WEBSITE_ID.');
                return self::FAILURE;
            }
            $abilities = array_values(array_filter(array_map('trim', explode(',', (string) $this->option('abilities')))));
            [$token, $plain] = ApiToken::issue($websiteId, (string) ($this->option('name') ?: 'API token'), $abilities);
            $this->info('Token #' . $token->id . ' created. Copy the value now — it is shown only once:');
            $this->line('<info>' . $plain . '</info>');
            return self::SUCCESS;
        }

        $tokens = ApiToken::withoutGlobalScope(WebsiteScope::class)
            ->select('id', 'website_id', 'name', 'abilities', 'expires_at', 'last_used_at')
            ->orderBy('id')
            ->get()
            ->map(fn ($token) => [
                $token->id,
                $token->website_id,
                $token->name,
                implode(',', $token->abilities ?? []),
                $token->expires_at?->toDateTimeString() ?? 'never',
                $token->last_used_at?->toDateTimeString() ?? 'never',
            ])
            ->all();

        $this->table(['ID', 'Website', 'Name', 'Abilities', 'Expires', 'Last used'], $tokens);

        return self::SUCCESS;
    }

    private function find(int $id): ?ApiToken
    {
        return ApiToken::withoutGlobalScope(WebsiteScope::class)->find($id);
    }
}
