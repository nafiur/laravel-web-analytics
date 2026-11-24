<?php

namespace Nafiur\WebAnalytics\Services;

/**
 * Small inline SVG marks for technology breakdowns. No external requests or
 * icon fonts — the SVG is generated from a fixed, trusted set of glyphs.
 */
final class TechnologyIcon
{
    /**
     * @param  string  $kind  os|browser|device
     */
    public static function render(string $kind, string $name): string
    {
        $value = strtolower($name);

        if ($kind === 'os') {
            if (str_contains($value, 'windows')) {
                return self::svg('<path fill="#2563eb" d="M3 3h8v8H3zM13 3h8v8h-8zM3 13h8v8H3zM13 13h8v8h-8z"/>');
            }
            if (str_contains($value, 'android')) {
                return self::svg('<path fill="#22c55e" d="M6.5 9h11v7.5a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2V9Z"/><path fill="none" stroke="#22c55e" stroke-width="1.4" stroke-linecap="round" d="M8.5 9 7 6.3M15.5 9 17 6.3"/><circle cx="9.7" cy="13" r="1" fill="#fff"/><circle cx="14.3" cy="13" r="1" fill="#fff"/>');
            }
            if (str_contains($value, 'ios') || str_contains($value, 'mac os') || str_contains($value, 'macos') || str_contains($value, 'iphone')) {
                return self::svg('<path fill="#334155" d="M16.4 12.8c0-2 1.6-3 1.7-3.1-.9-1.4-2.4-1.6-2.9-1.6-1.2-.1-2.4.7-3 .7-.6 0-1.6-.7-2.6-.7-1.3 0-2.6.8-3.3 2-1.4 2.4-.4 6 1 8 .7 1 1.5 2.1 2.5 2 1-.1 1.4-.6 2.6-.6s1.5.6 2.6.6c1.1 0 1.7-1 2.4-2 .7-1.1 1-2.1 1-2.2-.1 0-2-.8-2-3.1ZM14.5 6.9c.6-.7 1-1.7.9-2.7-.9 0-2 .6-2.6 1.3-.6.6-1 1.6-.9 2.6 1 .1 2-.5 2.6-1.2Z"/>');
            }
            if (str_contains($value, 'linux')) {
                return self::svg('<path fill="#f59e0b" d="M12 3c3 0 5 3 5 7 0 3-1 5-2 7l1 2h-3l-1-2-1 2H8l1-2c-1-2-2-4-2-7 0-4 2-7 5-7Z"/><circle cx="10" cy="9" r="1" fill="#fff"/><circle cx="14" cy="9" r="1" fill="#fff"/>');
            }

            return self::badge('?', '#94a3b8');
        }

        if ($kind === 'browser') {
            if (str_contains($value, 'chrome')) {
                return self::svg('<circle cx="12" cy="12" r="9" fill="#fff" stroke="#e5e7eb"/><path fill="#ea4335" d="M12 3a9 9 0 0 1 7.8 4.5H12a4.5 4.5 0 0 0-3.9 2.2L5.7 5.6A9 9 0 0 1 12 3Z"/><path fill="#34a853" d="M5.7 5.6 8.1 9.7A4.5 4.5 0 0 0 12 16.5a4.5 4.5 0 0 0 3.9-2.2l2.4 4.1A9 9 0 0 1 5.7 5.6Z"/><path fill="#fbbc05" d="M19.8 7.5A9 9 0 0 1 18.3 18.4l-2.4-4.1A4.5 4.5 0 0 0 16.5 12c0-.8-.2-1.5-.6-2.1l3.9-2.4Z"/><circle cx="12" cy="12" r="4" fill="#4285f4"/><circle cx="12" cy="12" r="2.5" fill="#fff"/>');
            }
            if (str_contains($value, 'edge')) {
                return self::svg('<circle cx="12" cy="12" r="9" fill="#0f7bc9"/><path fill="#37c9b0" d="M4 12c2-5 7-7.5 12-5.5-3-.2-6 1.7-7 5 3-1.3 6-.6 8 1.7-4-1.2-8 .5-10.5 5A8.7 8.7 0 0 1 4 12Z"/>');
            }
            if (str_contains($value, 'firefox')) {
                return self::svg('<circle cx="12" cy="12" r="9" fill="#ff7139"/><path fill="#ffb833" d="M12 4.5c3 0 5.3 1.4 6.3 3.6-2-1-4.2-.9-5.7.2-2 1.5-2.4 4.2-.6 6.2-3.2-1-5-4.2-3.8-7 .4-1 1.1-1.8 2.1-2.2-1.2 1.6-.4 3.6 1.2 4.2-1.5-1.5-1.4-3.6.5-5Z"/>');
            }
            if (str_contains($value, 'safari')) {
                return self::svg('<circle cx="12" cy="12" r="9" fill="#1e9bf0"/><path fill="#fff" d="m14.7 9.3-2 4.4-4.4 2 2-4.4 4.4-2Z"/><circle cx="12" cy="12" r="1" fill="#ef4444"/>');
            }
            if (str_contains($value, 'bot') || str_contains($value, 'spider') || str_contains($value, 'crawler')) {
                return self::svg('<rect x="4" y="8" width="16" height="10" rx="3" fill="#64748b"/><path fill="none" stroke="#64748b" stroke-width="1.4" stroke-linecap="round" d="M8 8 6.5 5M16 8l1.5-3"/><circle cx="9" cy="13" r="1" fill="#fff"/><circle cx="15" cy="13" r="1" fill="#fff"/>');
            }

            return self::badge('?', '#64748b');
        }

        if ($kind === 'device') {
            if (str_contains($value, 'mobile')) {
                return self::svg('<rect x="7" y="2.5" width="10" height="19" rx="2.5" fill="none" stroke="#0ea5e9" stroke-width="1.8"/><path stroke="#0ea5e9" stroke-width="1.4" stroke-linecap="round" d="M10.5 5.5h3"/>');
            }
            if (str_contains($value, 'tablet')) {
                return self::svg('<rect x="4.5" y="2.5" width="15" height="19" rx="2.5" fill="none" stroke="#8b5cf6" stroke-width="1.8"/><path stroke="#8b5cf6" stroke-width="1.4" stroke-linecap="round" d="M11 5.5h2"/>');
            }
            if (str_contains($value, 'bot')) {
                return self::svg('<rect x="4" y="8" width="16" height="10" rx="3" fill="#94a3b8"/><circle cx="9" cy="13" r="1" fill="#fff"/><circle cx="15" cy="13" r="1" fill="#fff"/>');
            }

            return self::svg('<rect x="2.5" y="4" width="19" height="13" rx="2" fill="none" stroke="#2563eb" stroke-width="1.8"/><path stroke="#2563eb" stroke-width="1.8" stroke-linecap="round" d="M8 20h8M12 17v3"/>');
        }

        return '';
    }

    private static function svg(string $body): string
    {
        return '<span class="inline-flex shrink-0 items-center" aria-hidden="true">'
            . '<svg viewBox="0 0 24 24" class="w-3.5 h-3.5">' . $body . '</svg></span>';
    }

    private static function badge(string $letter, string $color): string
    {
        return '<span class="inline-flex shrink-0 items-center justify-center w-3.5 h-3.5 rounded-full text-[9px] font-bold text-white" '
            . 'style="background:' . $color . '" aria-hidden="true">' . e($letter) . '</span>';
    }
}
