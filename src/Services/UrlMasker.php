<?php

namespace Nafiur\WebAnalytics\Services;

class UrlMasker
{
    /**
     * Replace sensitive query parameter values with [REDACTED] while keeping
     * the rest of the URL (including UTM parameters) intact.
     */
    public static function mask(?string $url, ?array $keys = null): ?string
    {
        if ($url === null || $url === '') {
            return $url;
        }

        $keys = $keys ?? config('laravel-web-analytics.privacy.masked_query_parameters', []);
        if (empty($keys)) {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false || empty($parts['query'])) {
            return $url;
        }

        parse_str($parts['query'], $query);
        $changed = false;
        foreach ($query as $key => $value) {
            if (in_array(strtolower((string) $key), array_map('strtolower', $keys), true)) {
                $query[$key] = '[REDACTED]';
                $changed = true;
            }
        }

        if (!$changed) {
            return $url;
        }

        $parts['query'] = http_build_query($query);

        return self::build($parts);
    }

    /** @param array<string, mixed> $parts */
    private static function build(array $parts): string
    {
        $url = '';
        if (isset($parts['scheme'])) {
            $url .= $parts['scheme'] . '://';
        }
        if (isset($parts['user'])) {
            $url .= $parts['user'];
            if (isset($parts['pass'])) {
                $url .= ':' . $parts['pass'];
            }
            $url .= '@';
        }
        if (isset($parts['host'])) {
            $url .= $parts['host'];
        }
        if (isset($parts['port'])) {
            $url .= ':' . $parts['port'];
        }
        $url .= $parts['path'] ?? '';
        if (isset($parts['query'])) {
            $url .= '?' . $parts['query'];
        }
        if (isset($parts['fragment'])) {
            $url .= '#' . $parts['fragment'];
        }

        return $url;
    }
}
