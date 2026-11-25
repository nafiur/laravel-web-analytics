<?php

namespace Nafiur\WebAnalytics\Services;

use RuntimeException;

/**
 * HMAC-signed replay configuration. The payload is signed for integrity,
 * not encrypted: it travels with every upload so ingest needs no session,
 * cookie or CSRF token and works behind full-page caches.
 */
class ReplaySigner
{
    public function make(array $payload, ?int $ttlMinutes = null): string
    {
        $ttl = $ttlMinutes ?? (int) config('laravel-web-analytics.replay.signature_ttl_minutes', 15);
        $payload['v'] = 1;
        $payload['exp'] = time() + max(1, $ttl) * 60;
        ksort($payload);

        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            throw new RuntimeException('Unable to encode replay configuration.');
        }

        return $this->encode($json) . '.' . $this->encode(hash_hmac('sha256', $json, $this->key(), true));
    }

    public function verify(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return null;
        }

        $json = $this->decode($parts[0]);
        $signature = $this->decode($parts[1]);
        if ($json === null || $signature === null || strlen($signature) !== 32) {
            return null;
        }

        if (! hash_equals(hash_hmac('sha256', $json, $this->key(), true), $signature)) {
            return null;
        }

        $payload = json_decode($json, true);
        if (! is_array($payload) || ($payload['v'] ?? null) !== 1) {
            return null;
        }
        if (! isset($payload['exp']) || (int) $payload['exp'] < time()) {
            return null;
        }

        return $payload;
    }

    private function key(): string
    {
        $key = (string) config('app.key');
        if (str_starts_with($key, 'base64:')) {
            $decoded = base64_decode(substr($key, 7), true);
            $key = $decoded === false ? '' : $decoded;
        }

        if ($key === '') {
            throw new RuntimeException('APP_KEY is required for session replay.');
        }

        return $key;
    }

    private function encode(string $bytes): string
    {
        return rtrim(strtr(base64_encode($bytes), '+/', '-_'), '=');
    }

    private function decode(string $segment): ?string
    {
        $padded = $segment . str_repeat('=', (4 - strlen($segment) % 4) % 4);
        $decoded = base64_decode(strtr($padded, '-_', '+/'), true);

        return $decoded === false ? null : $decoded;
    }
}
