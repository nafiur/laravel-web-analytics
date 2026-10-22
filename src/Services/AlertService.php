<?php

namespace Nafiur\WebAnalytics\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AlertService
{
    /**
     * Dispatch alert for high error rate or server crash.
     */
    public static function notifyErrorSpike(int $statusCode, string $url, string $ip, string $error = ''): void
    {
        if (!config('laravel-web-analytics.alerts.enabled')) {
            return;
        }

        $channel = config('laravel-web-analytics.alerts.channel', 'telegram');
        $message = "⚠️ *Laravel Web Analytics Alert*\n"
                 . "🚨 High Severity Status: `{$statusCode}`\n"
                 . "🔗 URL: `{$url}`\n"
                 . "🌐 IP: `{$ip}`\n"
                 . "⏰ Time: " . now()->toDateTimeString();

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
            } elseif (in_array($channel, ['slack', 'discord'])) {
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
}
