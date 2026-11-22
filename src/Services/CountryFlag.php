<?php
namespace Nafiur\WebAnalytics\Services;

/**
 * Resolves country names/codes to Unicode regional-indicator flag emoji.
 * No external assets, CDN or PHP extension required.
 */
class CountryFlag
{
    /** ISO 3166-1 alpha-2 code : English name */
    private const COUNTRIES = 'AF:Afghanistan|AX:Åland Islands|AL:Albania|DZ:Algeria|AS:American Samoa|AD:Andorra|AO:Angola|AI:Anguilla|AQ:Antarctica|AG:Antigua and Barbuda|AR:Argentina|AM:Armenia|AW:Aruba|AU:Australia|AT:Austria|AZ:Azerbaijan|BS:Bahamas|BH:Bahrain|BD:Bangladesh|BB:Barbados|BY:Belarus|BE:Belgium|BZ:Belize|BJ:Benin|BM:Bermuda|BT:Bhutan|BO:Bolivia|BQ:Bonaire, Sint Eustatius and Saba|BA:Bosnia and Herzegovina|BW:Botswana|BV:Bouvet Island|BR:Brazil|IO:British Indian Ocean Territory|BN:Brunei Darussalam|BG:Bulgaria|BF:Burkina Faso|BI:Burundi|CV:Cabo Verde|KH:Cambodia|CM:Cameroon|CA:Canada|KY:Cayman Islands|CF:Central African Republic|TD:Chad|CL:Chile|CN:China|CX:Christmas Island|CC:Cocos (Keeling) Islands|CO:Colombia|KM:Comoros|CG:Congo|CD:Congo, Democratic Republic of the|CK:Cook Islands|CR:Costa Rica|CI:Côte d\'Ivoire|HR:Croatia|CU:Cuba|CW:Curaçao|CY:Cyprus|CZ:Czechia|DK:Denmark|DJ:Djibouti|DM:Dominica|DO:Dominican Republic|EC:Ecuador|EG:Egypt|SV:El Salvador|GQ:Equatorial Guinea|ER:Eritrea|EE:Estonia|SZ:Eswatini|ET:Ethiopia|FK:Falkland Islands|FO:Faroe Islands|FJ:Fiji|FI:Finland|FR:France|GF:French Guiana|PF:French Polynesia|TF:French Southern Territories|GA:Gabon|GM:Gambia|GE:Georgia|DE:Germany|GH:Ghana|GI:Gibraltar|GR:Greece|GL:Greenland|GD:Grenada|GP:Guadeloupe|GU:Guam|GT:Guatemala|GG:Guernsey|GN:Guinea|GW:Guinea-Bissau|GY:Guyana|HT:Haiti|HM:Heard Island and McDonald Islands|VA:Holy See|HN:Honduras|HK:Hong Kong|HU:Hungary|IS:Iceland|IN:India|ID:Indonesia|IR:Iran|IQ:Iraq|IE:Ireland|IM:Isle of Man|IL:Israel|IT:Italy|JM:Jamaica|JP:Japan|JE:Jersey|JO:Jordan|KZ:Kazakhstan|KE:Kenya|KI:Kiribati|KP:Korea, Democratic People\'s Republic of|KR:Korea, Republic of|KW:Kuwait|KG:Kyrgyzstan|LA:Lao People\'s Democratic Republic|LV:Latvia|LB:Lebanon|LS:Lesotho|LR:Liberia|LY:Libya|LI:Liechtenstein|LT:Lithuania|LU:Luxembourg|MO:Macao|MG:Madagascar|MW:Malawi|MY:Malaysia|MV:Maldives|ML:Mali|MT:Malta|MH:Marshall Islands|MQ:Martinique|MR:Mauritania|MU:Mauritius|YT:Mayotte|MX:Mexico|FM:Micronesia|MD:Moldova|MC:Monaco|MN:Mongolia|ME:Montenegro|MS:Montserrat|MA:Morocco|MZ:Mozambique|MM:Myanmar|NA:Namibia|NR:Nauru|NP:Nepal|NL:Netherlands|NC:New Caledonia|NZ:New Zealand|NI:Nicaragua|NE:Niger|NG:Nigeria|NU:Niue|NF:Norfolk Island|MK:North Macedonia|MP:Northern Mariana Islands|NO:Norway|OM:Oman|PK:Pakistan|PW:Palau|PS:Palestine|PA:Panama|PG:Papua New Guinea|PY:Paraguay|PE:Peru|PH:Philippines|PN:Pitcairn|PL:Poland|PT:Portugal|PR:Puerto Rico|QA:Qatar|RE:Réunion|RO:Romania|RU:Russian Federation|RW:Rwanda|BL:Saint Barthélemy|SH:Saint Helena|KN:Saint Kitts and Nevis|LC:Saint Lucia|MF:Saint Martin|PM:Saint Pierre and Miquelon|VC:Saint Vincent and the Grenadines|WS:Samoa|SM:San Marino|ST:Sao Tome and Principe|SA:Saudi Arabia|SN:Senegal|RS:Serbia|SC:Seychelles|SL:Sierra Leone|SG:Singapore|SX:Sint Maarten|SK:Slovakia|SI:Slovenia|SB:Solomon Islands|SO:Somalia|ZA:South Africa|GS:South Georgia and the South Sandwich Islands|SS:South Sudan|ES:Spain|LK:Sri Lanka|SD:Sudan|SR:Suriname|SJ:Svalbard and Jan Mayen|SE:Sweden|CH:Switzerland|SY:Syrian Arab Republic|TW:Taiwan|TJ:Tajikistan|TZ:Tanzania|TH:Thailand|TL:Timor-Leste|TG:Togo|TK:Tokelau|TO:Tonga|TT:Trinidad and Tobago|TN:Tunisia|TR:Türkiye|TM:Turkmenistan|TC:Turks and Caicos Islands|TV:Tuvalu|UG:Uganda|UA:Ukraine|AE:United Arab Emirates|GB:United Kingdom|US:United States|UM:United States Minor Outlying Islands|UY:Uruguay|UZ:Uzbekistan|VU:Vanuatu|VE:Venezuela|VN:Viet Nam|VG:Virgin Islands, British|VI:Virgin Islands, U.S.|WF:Wallis and Futuna|EH:Western Sahara|YE:Yemen|ZM:Zambia|ZW:Zimbabwe';

    /** @var array<string, string>|null */
    private static ?array $map = null;

    /**
     * Resolve a country name or code to an ISO alpha-2 code.
     */
    public static function code(?string $country): ?string
    {
        if ($country === null || trim($country) === '') {
            return null;
        }

        $value = strtolower(trim($country));

        $aliases = [
            'usa'            => 'us', 'u.s.a.'       => 'us', 'united states of america' => 'us',
            'uk'             => 'gb', 'u.k.'         => 'gb', 'great britain'            => 'gb', 'england' => 'gb',
            'uae'            => 'ae', 'russia'       => 'ru', 'iran'                     => 'ir', 'syria'   => 'sy',
            'vietnam'        => 'vn', 'laos'         => 'la', 'moldova'                  => 'md',
            'czech republic' => 'cz', 'turkey'       => 'tr', 'ivory coast'              => 'ci',
            'cote divoire'   => 'ci', 'cape verde'   => 'cv', 'swaziland'                => 'sz',
            'macedonia'      => 'mk', 'burma'        => 'mm', 'palestine'                => 'ps',
            'korea'          => 'kr', 'south korea'  => 'kr', 'north korea'              => 'kp',
            'unknown'        => '', 'global / other' => '', 'localhost / internal'       => '',
            'local'          => '', 'direct'         => '', '-'                          => '',
        ];

        if (isset($aliases[$value])) {
            $code = $aliases[$value];
            return $code === '' ? null : $code;
        }

        //  / GeoIP country codes arrive as two-letter uppercase strings.
        if (preg_match('/^[a-z]{2}$/', $value)) {
            $code = strtoupper($value);
            return in_array($code, self::codes(), true) ? $code : null;
        }

        $map = self::map();
        if (isset($map[$value])) {
            return $map[$value];
        }

        $normalized = preg_replace('/[^a-z0-9 ]+/', '', $value) ?? $value;
        $normalized = preg_replace('/\s+(the|republic of|republic)$/', '', $normalized) ?? $normalized;
        $normalized = trim($normalized);

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        foreach ($map as $name => $code) {
            if (str_starts_with($name, $normalized) || str_starts_with($normalized, $name)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Return the flag emoji for a country, or an empty string when unknown.
     */
    public static function emoji(?string $country): string
    {
        $code = self::code($country);
        if ($code === null) {
            return '';
        }

        return self::regional($code[0]) . self::regional($code[1]);
    }

    /** @return array<string, string> */
    private static function map(): array
    {
        if (self::$map === null) {
            self::$map = [];
            foreach (explode('|', self::COUNTRIES) as $entry) {
                [$code, $name]                = explode(':', $entry, 2);
                self::$map[strtolower($name)] = $code;
            }
        }

        return self::$map;
    }

    /** @return array<int, string> */
    private static function codes(): array
    {
        return array_values(self::map());
    }

    private static function regional(string $letter): string
    {
        $codepoint = 0x1F1E6 + (ord(strtoupper($letter)) - 65);

        // JSON \u escapes are limited to 4 hex digits, so encode the
        // supplementary code point as a UTF-16 surrogate pair.
        $offset = $codepoint - 0x10000;
        $high   = 0xD800 + ($offset >> 10);
        $low    = 0xDC00 + ($offset & 0x3FF);

        return json_decode('"\u' . dechex($high) . '\u' . dechex($low) . '"') ?? '';
    }
}
