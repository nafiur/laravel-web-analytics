<?php

namespace Nafiur\WebAnalytics\Services;

/**
 * Approximate country centroids (ISO alpha-2 => lat,lng). Used as a keyless
 * fallback when only a country code/name is available (e.g. Cloudflare
 * headers) or when a local MaxMind database is not configured.
 *
 * These are country-level approximations, never city-level precision.
 */
class CountryCoordinates
{
    private const CENTROIDS = 'AF:33.9,67.7|AL:41.0,20.0|DZ:28.0,3.0|AR:-38.0,-64.0|AM:40.0,45.0|AU:-25.0,134.0|AT:47.5,14.0|AZ:40.0,47.5|BD:23.7,90.4|BE:50.5,4.5|BO:-16.0,-64.0|BR:-10.0,-52.0|BG:42.7,25.5|KH:12.6,104.9|CM:5.7,12.7|CA:56.0,-106.0|CL:-35.0,-71.0|CN:35.0,105.0|CO:4.0,-74.0|CR:9.9,-84.0|HR:45.1,15.2|CU:21.5,-79.0|CY:35.0,33.0|CZ:49.8,15.5|DK:56.0,10.0|DO:19.0,-70.7|EC:-1.0,-78.0|EG:27.0,30.0|SV:13.8,-89.2|ET:8.6,39.6|FI:64.0,26.0|FR:46.0,2.0|DE:51.0,10.0|GH:7.9,-1.0|GR:39.0,22.0|GT:15.5,-90.4|HN:15.0,-86.5|HK:22.3,114.2|HU:47.0,19.0|IS:65.0,-18.0|IN:22.0,79.0|ID:-2.0,118.0|IR:32.0,53.0|IQ:33.0,44.0|IE:53.0,-8.0|IL:31.5,35.0|IT:42.8,12.5|JM:18.1,-77.3|JP:36.0,138.0|JO:31.2,36.5|KZ:48.0,68.0|KE:0.2,37.9|KR:36.5,127.9|KW:29.3,47.5|KG:41.4,64.6|LA:18.5,103.7|LV:56.9,24.6|LB:33.9,35.9|LY:27.0,17.0|LT:55.2,23.9|LU:49.6,6.1|MY:4.2,101.9|MV:3.2,73.2|ML:17.0,-4.0|MT:35.9,14.4|MX:23.0,-102.0|MD:47.0,29.0|MN:46.9,103.8|MA:32.0,-6.0|MZ:-17.0,35.0|MM:21.9,95.9|NA:-22.0,17.0|NP:28.4,84.1|NL:52.0,5.0|NZ:-41.0,174.0|NI:12.9,-85.0|NG:9.0,8.0|NO:62.0,10.0|OM:21.0,57.0|PK:30.0,69.0|PA:8.5,-80.0|PY:-23.0,-58.0|PE:-9.0,-75.0|PH:12.9,121.8|PL:52.0,19.0|PT:39.5,-8.0|QA:25.3,51.2|RO:46.0,25.0|RU:61.0,105.0|SA:24.0,45.0|SN:14.5,-14.5|RS:44.0,21.0|SG:1.35,103.8|SK:48.7,19.5|SI:46.1,14.8|ZA:-29.0,24.0|ES:40.0,-4.0|LK:7.9,80.8|SD:15.0,30.0|SE:62.0,15.0|CH:46.8,8.2|SY:35.0,38.0|TW:23.7,121.0|TJ:38.5,71.0|TZ:-6.0,35.0|TH:15.0,101.0|TN:34.0,9.0|TR:39.0,35.0|TM:39.0,60.0|UG:1.0,32.0|UA:49.0,32.0|AE:24.0,54.0|US:38.0,-97.0|UY:-33.0,-56.0|UZ:41.4,64.6|VE:7.0,-66.0|VN:16.0,107.0|YE:15.0,47.0|ZM:-13.0,28.0|ZW:-19.0,30.0';

    /** @return array{latitude: float, longitude: float}|null */
    public static function for(?string $country): ?array
    {
        if ($country === null || trim($country) === '') {
            return null;
        }

        $code = CountryFlag::code($country);
        if ($code === null) {
            return null;
        }

        foreach (explode('|', self::CENTROIDS) as $entry) {
            [$iso, $coords] = explode(':', $entry, 2);
            if ($iso === $code) {
                [$lat, $lng] = explode(',', $coords, 2);

                return ['latitude' => (float) $lat, 'longitude' => (float) $lng];
            }
        }

        return null;
    }
}
