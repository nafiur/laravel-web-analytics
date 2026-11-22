<?php

namespace Nafiur\WebAnalytics\Services;

class IpAddress
{
    /**
     * True for loopback, private and reserved ranges (RFC1918 includes the
     * whole 172.16.0.0/12 block, not just 172.16.x.x).
     */
    public static function isPrivate(string $ip): bool
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }
}
