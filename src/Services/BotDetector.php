<?php

namespace Nafiur\WebAnalytics\Services;

class BotDetector
{
    /** Search engines and social preview crawlers we consider "known". */
    private const KNOWN = [
        'googlebot', 'bingbot', 'yandexbot', 'yandex', 'baiduspider', 'duckduckbot',
        'slurp', 'facebookexternalhit', 'twitterbot', 'linkedinbot', 'pinterestbot',
        'slackbot', 'whatsapp', 'telegrambot', 'discordbot', 'applebot', 'ahrefsbot',
        'semrushbot', 'mj12bot', 'dotbot', 'petalbot', 'bytespider',
    ];

    /** Scrapers, downloaders, CLI tools and generic automation. */
    private const SCRAPER = [
        'scrapy', 'python-requests', 'python-urllib', 'httpx', 'wget', 'curl',
        'postmanruntime', 'insomnia', 'httpie', 'node-fetch', 'axios', 'headlesschrome',
        'phantomjs', 'selenium', 'puppeteer', 'lwp::useragent', 'java/', 'go-http-client',
        'okhttp', 'libwww-perl', 'masscan', 'nmap',
    ];

    public static function isBot(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return false;
        }

        $ua = strtolower($userAgent);

        foreach (array_merge(self::KNOWN, self::SCRAPER) as $needle) {
            if (str_contains($ua, $needle)) {
                return true;
            }
        }

        return (bool) preg_match('/(bot|crawler|crawling|spider|slurp|scraper|preview|monitor|uptime)/i', $ua);
    }

    public static function isKnownBot(?string $userAgent): bool
    {
        if (empty($userAgent)) {
            return false;
        }

        $ua = strtolower($userAgent);
        foreach (self::KNOWN as $needle) {
            if (str_contains($ua, $needle)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Decide whether this request should be captured based on the config.
     */
    public static function shouldCapture(?string $userAgent): bool
    {
        $setting = config('laravel-web-analytics.capture.bots', false);

        if ($setting === true) {
            return true;
        }

        if (!self::isBot($userAgent)) {
            return true;
        }

        return $setting === 'known' && self::isKnownBot($userAgent);
    }
}
