<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlertService
{
    /**
     * Record a request in a short-lived window so spike detection can compare
     * the current error rate against the configured threshold.
     */
    public static function recordRequest(bool $isError): void
    {
        if (!config('laravel-web-analytics.alerts.enabled')) {
            return;
        }

        $minute = now()->format('YmdHi');
        $totalKey = "lwa_alerts_total_{$minute}";
        $errorKey = "lwa_alerts_errors_{$minute}";

        try {
            self::ensureCounter($totalKey);
            Cache::increment($totalKey);

            if ($isError) {
                self::ensureCounter($errorKey);
                Cache::increment($errorKey);
            }
        } catch (\Throwable $e) {
            // Alerting must never affect the tracked request.
        }
    }

    /**
     * Send a single error-spike alert per minute when the configured threshold
     * is exceeded. Requires at least 10 samples to avoid noisy percentages.
     */
    public static function maybeNotifySpike(string $url, string $ip): void
    {
        if (!config('laravel-web-analytics.alerts.enabled')) {
            return;
        }

        $minute = now()->format('YmdHi');
        $threshold = (float) config('laravel-web-analytics.alerts.error_rate_threshold_percentage', 10);

        try {
            $total = (int) Cache::get("lwa_alerts_total_{$minute}", 0);
            $errors = (int) Cache::get("lwa_alerts_errors_{$minute}", 0);

            if ($total < 10 || $errors < 1) {
                return;
            }

            $rate = ($errors / $total) * 100;
            if ($rate >= $threshold && Cache::add("lwa_alerts_spike_{$minute}", 1, 300)) {
                self::send("⚠️ *Laravel Web Analytics Alert*\n"
                    . "📈 Error spike: `{$rate}%` (`{$errors}`/`{$total}`)\n"
                    . "🔗 Sample URL: `{$url}`\n"
                    . "🌐 IP: `{$ip}`\n"
                    . "⏰ Time: " . now()->toDateTimeString());
            }
        } catch (\Throwable $e) {
            // Fail silently.
        }
    }

    /**
     * Dispatch an alert for a 5xx response, deduplicated per URL/IP/window.
     */
    public static function notifyErrorSpike(int $statusCode, string $url, string $ip, string $error = ''): void
    {
        if (!config('laravel-web-analytics.alerts.enabled')) {
            return;
        }

        $dedupeKey = 'lwa_alert_5xx_' . md5($statusCode . '|' . $url . '|' . $ip);
        try {
            if (!Cache::add($dedupeKey, 1, 60)) {
                return;
            }
        } catch (\Throwable $e) {
            // If caching is unavailable, still attempt a single alert below.
        }

        $message = "⚠️ *Laravel Web Analytics Alert*\n"
                 . "🚨 High Severity Status: `{$statusCode}`\n"
                 . "🔗 URL: `{$url}`\n"
                 . "🌐 IP: `{$ip}`\n";

        if ($error !== '') {
            $message .= "📝 Error: `{$error}`\n";
        }

        $message .= "⏰ Time: " . now()->toDateTimeString();

        self::send($message);
    }

    /**
     * Deliver the message through the configured channel.
     */
    private static function send(string $message): void
    {
        $channel = config('laravel-web-analytics.alerts.channel', 'telegram');

        try {
            if ($channel === 'telegram') {
                $botToken = config('laravel-web-analytics.alerts.telegram_bot_token');
                $chatId = config('laravel-web-analytics.alerts.telegram_chat_id');
                if ($botToken && $chatId) {
                    Http::timeout(3)->post("https://api.telegram.org/bot{$botToken}/sendMessage", [
                        'chat_id'    => $chatId,
                        'text'       => $message,
                        'parse_mode' => 'Markdown',
                    ]);
                }
            } elseif (in_array($channel, ['slack', 'discord'], true)) {
                $webhook = config('laravel-web-analytics.alerts.webhook_url');
                if ($webhook) {
                    $payload = $channel === 'slack' ? ['text' => $message] : ['content' => $message];
                    Http::timeout(3)->post($webhook, $payload);
                }
            }
        } catch (\Throwable $e) {
            Log::warning('LWA Alert failed to send: ' . $e->getMessage());
        }
    }

    /**
     * Initialize a counter without overwriting an existing value.
     */
    private static function ensureCounter(string $key): void
    {
        try {
            Cache::add($key, 0, 300);
        } catch (\Throwable $e) {
            // Ignore cache driver failures.
        }
    }
}
