<?php

namespace BureauPartners\ExtractAddressFromText;

class AddressExtractor
{
    private $recipient             = [];
    private $recipient_lines       = [];
    private $street                = null;
    private $street_line           = null;
    private $house_number          = null;
    private $house_number_addition = null;
    private $postalcode            = null;
    private $postalcode_line       = null;
    private $city                  = null;
    private $city_line             = null;
    private $country               = null;
    private $country_code          = null;
    private $country_line          = null;
    private $possible_streets      = [];

    private static $riskkix_numbers = null;
    private static $country_names   = null;

    /**
     * Postcode formats per country, following the UPU addressing standards.
     *
     * - postalcode:  the postcode itself.
     * - prefix:      the country prefix some senders write in front of it
     *                ("D-10117", "CH-3003", "LV-1050"); optional when reading.
     * - city_first:  the city may also come before the postcode on the same
     *                line ("LONDON SW7 5DP", "Rīga, LV-1050"). Every
     *                country also accepts the postcode first.
     * - city_above_street: the city may be on the line above the street.
     * - distinctive: the postcode cannot be mistaken for one of another
     *                country, so it may decide the country when the address
     *                has no (readable) country line. A written prefix is
     *                always distinctive.
     *
     * The order matters for that last step: the first country whose
     * postcode matches wins, so the Crown Dependencies come before GB.
     */
    private const POSTALCODE_FORMATS = [
        // A space between the digits and the letters makes the letters a
        // word of their own: "1040 Brussel" is not "1040 BR" + "ussel".
        'NL' => ['postalcode' => '[1-9]\d{3}(?:(?:[A-EGHJ-NPRTVWXZ][A-EGHJ-NPRSTVWXZ]|S[BCEGHJ-NPRTVWXZ])|\s(?:[A-EGHJ-NPRTVWXZ][A-EGHJ-NPRSTVWXZ]|S[BCEGHJ-NPRTVWXZ])(?!\pL))', 'prefix' => 'NL'],
        'GG' => ['postalcode' => 'GY\d[\dA-Z]? ?\d[ABD-HJLNP-UW-Z]{2}(?![\pL\d])', 'city_first' => true, 'distinctive' => true],
        'JE' => ['postalcode' => 'JE\d[\dA-Z]? ?\d[ABD-HJLNP-UW-Z]{2}(?![\pL\d])', 'city_first' => true, 'distinctive' => true],
        'IM' => ['postalcode' => 'IM\d[\dA-Z]? ?\d[ABD-HJLNP-UW-Z]{2}(?![\pL\d])', 'city_first' => true, 'distinctive' => true],
        'GI' => ['postalcode' => 'GX11 ?1AA(?![\pL\d])', 'city_first' => true, 'distinctive' => true],
        // Also covers Guernsey (GY), Jersey (JE) and the Isle of Man (IM). The space
        // between the outward and inward code is optional, because text extraction
        // loses it as often as not. Inspiration: https://rgxdb.com/r/316F0I2N
        'GB' => [
            'postalcode'  => '(?:GIR ?0AA|(?:(?:A[BL]|B[ABDHLNRSTX]?|C[ABFHMORTVW]|D[ADEGHLNTY]|E[HNX]?|F[KY]|G[LUY]?|H[ADGPRSUX]|I[GMPV]|JE|K[ATWY]|L[ADELNSU]?|M[EKL]?|N[EGNPRW]?|O[LX]|P[AEHLOR]|R[GHM]|S[AEGK-PRSTY]?|T[ADFNQRSW]|UB|W[ADFNRSV]|YO|ZE)[1-9]?\d|(?:(?:E|N|NW|SE|SW|W)1|EC[1-4]|WC[12])[A-HJKMNPR-Y]|(?:SW|W)(?:[2-9]|[1-9]\d)|EC[1-9]\d) ?\d[ABD-HJLNP-UW-Z]{2})(?![\pL\d])',
            'city_first'  => true,
            'distinctive' => true,
        ],
        // Eircode: routing key and unique identifier, e.g. "D02 X285".
        'IE' => ['postalcode' => '(?:[AC-FHKNPRTV-Y]\d{2}|D6W) ?[0-9AC-FHKNPRTV-Y]{4}(?![\pL\d])', 'city_first' => true, 'distinctive' => true],
        'AD' => ['postalcode' => 'AD ?\d{3}', 'distinctive' => true],
        'PL' => ['postalcode' => '\d{2}-\d{3}', 'prefix' => 'PL', 'distinctive' => true],
        'PT' => ['postalcode' => '\d{4}-\d{3}', 'prefix' => 'P|PT', 'distinctive' => true],
        'AL' => ['postalcode' => '\d{4}', 'prefix' => 'AL'],
        'AT' => ['postalcode' => '[1-9]\d{3}', 'prefix' => 'A|AT'],
        'AX' => ['postalcode' => '22\d{3}', 'prefix' => 'AX'],
        'BA' => ['postalcode' => '\d{5}', 'prefix' => 'BA|BIH'],
        'BE' => ['postalcode' => '[1-9]\d{3}', 'prefix' => 'B|BE'],
        'BG' => ['postalcode' => '\d{4}', 'prefix' => 'BG', 'city_first' => true],
        'BY' => ['postalcode' => '\d{6}', 'prefix' => 'BY', 'city_first' => true],
        'CH' => ['postalcode' => '[1-9]\d{3}', 'prefix' => 'CH'],
        'CY' => ['postalcode' => '\d{4}', 'prefix' => 'CY'],
        'CZ' => ['postalcode' => '\d{3} ?\d{2}', 'prefix' => 'CZ'],
        'DE' => ['postalcode' => '\d{5}', 'prefix' => 'D|DE'],
        'DK' => ['postalcode' => '\d{4}', 'prefix' => 'DK'],
        'EE' => ['postalcode' => '\d{5}', 'prefix' => 'EE'],
        'ES' => ['postalcode' => '(?:0[1-9]|[1-4]\d|5[0-2])\d{3}', 'prefix' => 'E|ES'],
        'FI' => ['postalcode' => '\d{5}', 'prefix' => 'FI|FIN'],
        'FO' => ['postalcode' => '\d{3}', 'prefix' => 'FO'],
        'FR' => ['postalcode' => '(?:[0-8]\d|9[0-8])\d{3}', 'prefix' => 'F|FR'],
        'GR' => ['postalcode' => '\d{3} ?\d{2}', 'prefix' => 'GR'],
        'HR' => ['postalcode' => '\d{5}', 'prefix' => 'HR'],
        'HU' => ['postalcode' => '\d{4}', 'prefix' => 'H|HU', 'city_first' => true, 'city_above_street' => true],
        'IS' => ['postalcode' => '\d{3}', 'prefix' => 'IS'],
        'IT' => ['postalcode' => '\d{5}', 'prefix' => 'I|IT'],
        'LI' => ['postalcode' => '94(?:8[5-9]|9\d)', 'prefix' => 'FL|LI'],
        'LT' => ['postalcode' => '\d{5}', 'prefix' => 'LT', 'city_first' => true],
        'LU' => ['postalcode' => '\d{4}', 'prefix' => 'L|LU'],
        'LV' => ['postalcode' => '\d{4}', 'prefix' => 'LV', 'city_first' => true],
        'MC' => ['postalcode' => '980\d{2}', 'prefix' => 'MC'],
        'MD' => ['postalcode' => '\d{4}', 'prefix' => 'MD', 'city_first' => true],
        'ME' => ['postalcode' => '\d{5}', 'prefix' => 'ME|MNE'],
        'MK' => ['postalcode' => '\d{4}', 'prefix' => 'MK'],
        // Three letters for the locality and four digits, e.g. "VLT 1117".
        'MT' => ['postalcode' => '[A-Z]{3} ?\d{4}', 'city_first' => true],
        'NO' => ['postalcode' => '\d{4}', 'prefix' => 'N|NO'],
        'RO' => ['postalcode' => '\d{6}', 'prefix' => 'RO', 'city_first' => true],
        'RS' => ['postalcode' => '\d{5}', 'prefix' => 'RS|SRB'],
        'RU' => ['postalcode' => '\d{6}', 'prefix' => 'RU', 'city_first' => true],
        'SE' => ['postalcode' => '\d{3} ?\d{2}', 'prefix' => 'S|SE'],
        'SI' => ['postalcode' => '\d{4}', 'prefix' => 'SI'],
        'SK' => ['postalcode' => '\d{3} ?\d{2}', 'prefix' => 'SK'],
        'SM' => ['postalcode' => '4789\d', 'prefix' => 'RSM|SM'],
        'TR' => ['postalcode' => '\d{5}', 'prefix' => 'TR'],
        'UA' => ['postalcode' => '\d{5}', 'prefix' => 'UA', 'city_first' => true],
        'VA' => ['postalcode' => '00120', 'prefix' => 'V|VA|SCV'],
        'XK' => ['postalcode' => '\d{5}', 'prefix' => 'XK|RKS'],
    ];

    /**
     * Countries that write the house number before the street name
     * ("38 Hyde Park Gate", "7 bis, rue Eblé"). The street name first is
     * still understood, as senders often use their own format.
     */
    private $street_house_numer_occurrence_first_number = [
        'FR', 'GB', 'GG', 'GI', 'IE', 'IM', 'JE', 'LU', 'MC', 'MT',
    ];

    public function __construct(string $address, string $default_country = 'NL')
    {
        $lines = $this->splitLines($address);
        if (count($lines) < 3) {
            return;
        }

        $this->country_code = $default_country;
        $this->determineCountry($lines);
        $this->determinePostalcode($lines, $this->country_code);

        // Without a country line the postcode is all there is. If the
        // default country's postcode isn't there, look for a country behind
        // the city and then for a postcode that can only be one country's.
        if ($this->country_line === null && $this->postalcode === null) {
            $this->determineCountryBehindCity($lines);
            if ($this->country !== null) {
                $this->determinePostalcode($lines, $this->country_code);
            } else {
                $this->determineCountryFromPostalcode($lines);
            }
        }

        $this->determineCityAbovePostalcode($lines);

        foreach ($lines as $key => $address_line) {
            if ($address_line === '' || $key === $this->country_line || $this->isReturnAddress($address_line)) {
                continue;
            }
            $this->determineStreet($address_line, $key);
            $this->collectRecipientLine($address_line, $key);
        }
        $this->determineFinalStreet($lines);
        $this->determineCityAboveStreet($lines);
        $this->determineFinalRecipient();

        // Neither a country line nor a postcode said where this address is,
        // so claiming the default country would only be a guess.
        if ($this->country === null && $this->postalcode === null) {
            $this->country_code = '';
        }
    }

    /**
     * Splits the text into trimmed lines. Text from a PDF often ends a line
     * in a space or uses a non-breaking space, which made "AUSTRIA " miss
     * the country list.
     */
    private function splitLines(string $address): array
    {
        $lines = preg_split('/\R/u', $address);
        if ($lines === false) {
            $lines = explode(PHP_EOL, $address);
        }
        foreach ($lines as &$line) {
            $line = trim(preg_replace('/[\x{00A0}\x{2007}\x{202F}\s]+/u', ' ', $line) ?? $line);
        }
        unset($line);

        return $lines;
    }

    /**
     * Remembers a line that could be part of the addressee.
     *
     * Which lines actually are the addressee cannot be decided here: that
     * depends on where the street line turns out to be, and that is only known
     * after determineFinalStreet(). See determineFinalRecipient().
     */
    private function collectRecipientLine(string $address_line, int $address_line_key): void
    {
        if (strpos(strtolower($address_line), 'retour') !== false) {
            return;
        }
        // A line without a single letter (a barcode or a sequence number, for
        // example) is never an addressee.
        if (preg_match('/\pL/u', $address_line) === 0) {
            return;
        }
        // Check if the line contains a postalcode to be a return address
        if (preg_match("/((?:NL-)?(?:[1-9]\d{3} ?(?:[A-EGHJ-NPRTVWXZ][A-EGHJ-NPRSTVWXZ]|S[BCEGHJ-NPRTVWXZ])))/i", $address_line) !== 0) {
            return;
        }
        $this->recipient_lines[$address_line_key] = $address_line;
    }

    /**
     * The addressee is everything above the street line.
     *
     * Deciding this per line while looping (on the first line that looks like a
     * street) loses the addressee whenever a name itself contains a number:
     * "XYZ P20 B.V." reads as street "XYZ P" with house number 20, after
     * which no addressee line is collected at all. determineFinalStreet() has
     * already picked the real street line by now, so use that as the boundary,
     * and fall back to the city or postalcode line when there is no street.
     */
    private function determineFinalRecipient(): void
    {
        $boundary = $this->street_line;
        if ($boundary === null || ($this->city_line !== null && $this->city_line < $boundary)) {
            $boundary = $this->city_line ?? $this->postalcode_line;
        }
        if ($boundary === null && $this->possible_streets !== []) {
            // No postalcode and no street: keep the old cut-off at the first
            // line that looked like a street.
            $boundary = array_key_first($this->possible_streets);
        }

        foreach ($this->recipient_lines as $address_line_key => $address_line) {
            if ($boundary !== null && $address_line_key >= $boundary) {
                continue;
            }
            $this->recipient[] = $address_line;
        }
    }

    private function isReturnAddress(string $address_line): bool
    {
        // Only dutch return addresses are supported at the moment
        $return_address = preg_match('/(?P<street>(.\S)+?([\S.]+)) (?P<housenumber>\d+\.?\d*), (?P<postalcode>[1-9][0-9]{3} ?(?!sa|sd|ss)[a-z]{2}) (?P<city>(.\S)+?([\S.]+))?/i', $address_line, $return_address_parts);
        if ($return_address_parts !== array()) {
            return true;
        }
        return false;
    }

    private function determineStreet(string $address_line, int $address_line_key): void
    {
        if (strpos(strtolower($address_line), 'retour') !== false) {
            return;
        }
        // "Bd. Dimitrie Pompeiu nr. 5-7", "639 Cadde - 647 Sokak No. 10": the
        // house number is marked, so the other numbers belong to the street.
        if (preg_match('/^(?P<street>.*\pL.*?)[,\s]+(?:nr|no|nº|n°|n(?=\.))\.?:?\s*(?P<housenumber>\d+)(?P<housenumber_addition>.*)$/iu', $address_line, $street_parts)) {
            $this->possible_streets[$address_line_key] = [
                'street'                => trim($street_parts['street']),
                'house_number'          => $street_parts['housenumber'],
                'house_number_addition' => trim($street_parts['housenumber_addition']),
                'line'                  => $address_line,
            ];
            return;
        }
        if (in_array($this->country_code, $this->street_house_numer_occurrence_first_number)) {
            // "38 Hyde Park Gate", "7 bis, rue Eblé", "Flat 3, 12 High Street".
            // A line without a street name after the number ("Office 1")
            // falls through to the street-first reading.
            if (preg_match('/(?:^|[\s,])(?P<housenumber>\d+)(?P<housenumber_addition>-\d+[a-z]?|\s?-?(?:bis|ter|quater)(?!\pL)|[a-z](?![\pL\d]))?[,\s]+(?P<street>\pL.*)$/iu', $address_line, $street_parts)) {
                $this->possible_streets[$address_line_key] = [
                    'street'                => trim($street_parts['street']),
                    'house_number'          => $street_parts['housenumber'],
                    'house_number_addition' => trim($street_parts['housenumber_addition'] ?? ''),
                    'line'                  => $address_line,
                ];
                return;
            }
        }
        if (preg_match('/(?P<street>[^\d]+)\s*(?P<housenumber>\d+\.?\d*)\s*(?P<housenumber_addition>(.)+)?/i', $address_line, $street_parts)) {
            // A floor, unit or district is not a street: "etaj 8", "Sector 2",
            // "planta 36", "Whitehall Mansions, 2nd floor".
            if (
                preg_match('/(?:^|[\s,])(?:apartament|apartment|apt|etage|étage|etaj|floor|office|piso|planta|room|sector|sektor|stock|stockwerk|suite|unit|verdieping|zimmer)\.?\s*$/iu', $street_parts['street'])
                || preg_match('/^(?:st|nd|rd|th)\b/i', $street_parts['housenumber_addition'] ?? '')
            ) {
                return;
            }
            $street = '';
            $house_number = '';
            $house_number_addition = '';
            if (isset($street_parts['street'])) {
                if (strlen($street_parts['street']) > 2) {
                    $street_parts['street'] = substr($address_line, 0, (strpos($address_line, $street_parts['street']) + strlen($street_parts['street'])));
                }
                $street = $street_parts['street'];
            }
            if (isset($street_parts['housenumber'])) {
                $house_number = preg_replace('/\D/', '', $street_parts['housenumber']);
            }
            if (isset($street_parts['housenumber_addition'])) {
                $house_number_addition = $street_parts['housenumber_addition'];
            }
            $this->possible_streets[$address_line_key] = [
                'street'                => $street,
                'house_number'          => $house_number,
                'house_number_addition' => $house_number_addition,
                'line'                  => $address_line,
            ];
        }
    }

    private function determineFinalStreet(array $lines)
    {
        // The street is the closest street-like line above the city, which
        // is usually the postalcode line.
        $boundary = $this->city_line ?? $this->postalcode_line;
        if ($boundary === null) {
            return;
        }
        $this->determineStreetAbove($boundary);

        // "Halkett Place" above "St Helier": where the house number goes
        // first, a street without one is common. It is the line above the
        // city, as long as a line above it is left for the addressee.
        if ($this->street === null && in_array($this->country_code, $this->street_house_numer_occurrence_first_number)) {
            $key = $this->previousLineKey($lines, $boundary);
            if ($key !== null && $this->previousLineKey($lines, $key) !== null && preg_match('/\pL/u', $lines[$key]) === 1) {
                $this->street_line = $key;
                $this->street = $lines[$key];
                $this->house_number = '';
                $this->house_number_addition = '';
            }
        }
    }

    /**
     * Hungary writes the city above the street, and the postcode on its own
     * line below it ("Budapest / Kapás u. 6-12. / 1027").
     */
    private function determineCityAboveStreet(array $lines): void
    {
        if ($this->city !== '' || $this->street_line === null || empty(self::POSTALCODE_FORMATS[$this->country_code]['city_above_street'])) {
            return;
        }
        $key = $this->previousLineKey($lines, $this->street_line);
        if ($key !== null && preg_match('/\pL/u', $lines[$key]) === 1 && preg_match('/\d/', $lines[$key]) === 0) {
            $this->city = $lines[$key];
            $this->city_line = $key;
        }
    }

    private function previousLineKey(array $lines, int $key): ?int
    {
        for ($previous = $key - 1; $previous >= 0; $previous--) {
            if ($lines[$previous] !== '') {
                return $previous;
            }
        }
        return null;
    }

    /**
     * The closest street-like line above the boundary line.
     */
    private function determineStreetAbove(int $boundary): void
    {
        $possible_streets = array_reverse($this->possible_streets, true);
        foreach ($possible_streets as $address_line_key => $possible_street) {
            if ($boundary > $address_line_key && $this->street === null) {
                $this->street_line = $address_line_key;
                $this->street = $possible_street['street'];
                $this->house_number = $possible_street['house_number'];
                $this->house_number_addition = $possible_street['house_number_addition'];

                // Correct street names that end in a number (e.g. "Punter 34",
                // "Plein 1940-1945") using the riskkix data for this postalcode.
                if ($this->country_code === 'NL') {
                    $riskkix = $this->applyRiskkix(
                        $possible_street['line'],
                        $this->getRiskkixNumbers((string) $this->postalcode)
                    );
                    if ($riskkix !== null) {
                        $this->street = $riskkix['street'];
                        $this->house_number = $riskkix['house_number'];
                        $this->house_number_addition = $riskkix['house_number_addition'];
                    }
                }
            }
        }
    }

    /**
     * Returns the number(s) that are part of the street name for the given
     * postalcode and must not be read as a house number.
     */
    private function getRiskkixNumbers(string $postalcode): array
    {
        if (self::$riskkix_numbers === null) {
            $path = __DIR__ . '/data/riskkix.json';
            self::$riskkix_numbers = is_file($path)
                ? json_decode(file_get_contents($path), true)
                : [];
        }
        $key = strtoupper(preg_replace('/\s+/', '', $postalcode));
        return self::$riskkix_numbers[$key] ?? [];
    }

    /**
     * Re-interprets a street line, folding any leading number that belongs to the
     * street name (per $ignore) into the street and taking the next number as the
     * house number. Returns null when there is nothing to correct.
     */
    private function applyRiskkix(string $line, array $ignore): ?array
    {
        if (empty($ignore)) {
            return null;
        }
        if (!preg_match_all('/\d+/', $line, $matches, PREG_OFFSET_CAPTURE)) {
            return null;
        }

        $street_end = null;
        $house_number = null;
        $house_number_offset = null;
        $house_number_length = 0;
        foreach ($matches[0] as $match) {
            list($number, $offset) = $match;
            if (in_array($number, $ignore, true)) {
                // Part of the street name.
                $street_end = $offset + strlen($number);
                continue;
            }
            // First number that is not ignored: this is the house number.
            $house_number = $number;
            $house_number_offset = $offset;
            $house_number_length = strlen($number);
            break;
        }

        // No number was folded into the street, or everything was ignored: leave as is.
        if ($street_end === null || $house_number === null) {
            return null;
        }

        $addition = substr($line, $house_number_offset + $house_number_length);
        return [
            'street'                => trim(substr($line, 0, $street_end)),
            'house_number'          => $house_number,
            'house_number_addition' => trim($addition),
        ];
    }

    /**
     * Finds the postcode (and the city on the same line) of the given
     * country. The lowest matching line wins: the postcode line sits below
     * the street, and a post box or reference number above it can look like
     * a postcode too.
     */
    private function determinePostalcode(array $lines, ?string $country_code, bool $distinctive_only = false): bool
    {
        $patterns = $this->postalcodePatterns((string) $country_code, $distinctive_only);
        if ($patterns === []) {
            return false;
        }

        $found = false;
        foreach ($lines as $key => $address_line) {
            if ($address_line === '' || $key === $this->country_line) {
                continue;
            }
            if (strpos(strtolower($address_line), 'retour') !== false || $this->isReturnAddress($address_line)) {
                continue;
            }
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $address_line, $matches)) {
                    $this->postalcode = $matches['postalcode'];
                    $this->postalcode_line = $key;
                    $this->city = trim($matches['city'] ?? '', " \t,");
                    $found = true;
                    break;
                }
            }
        }

        return $found;
    }

    private function postalcodePatterns(string $country_code, bool $distinctive_only): array
    {
        if (!isset(self::POSTALCODE_FORMATS[$country_code])) {
            return [];
        }
        $format = self::POSTALCODE_FORMATS[$country_code];
        $prefix = $format['prefix'] ?? null;

        if ($distinctive_only && empty($format['distinctive'])) {
            if ($prefix === null) {
                return [];
            }
            // Only with the hyphen: "A-1010" is a prefix, "A 1010" may not be.
            $postalcode = '(?:' . $prefix . ') ?- ?(?:' . $format['postalcode'] . ')';
        } else {
            // "D-10117", but also "MC 98000".
            $postalcode = ($prefix !== null ? '(?:(?:' . $prefix . ')(?: ?- ?| ))?' : '') . '(?:' . $format['postalcode'] . ')';
        }
        $postalcode = '(?P<postalcode>' . $postalcode . ')(?!\d)';

        $patterns = ['/^' . $postalcode . '[\s,.\-]*(?P<city>[\pL\'‘’].*)?$/iu'];
        if (!empty($format['city_first'])) {
            // The shortest city: "Rīga, LV - 1050" is not "Rīga, LV -" + "1050".
            $patterns[] = '/^(?P<city>[\pL\'‘’].*?)[\s,]+' . $postalcode . '$/iu';
        }

        return $patterns;
    }

    /**
     * The country of a postcode that cannot belong to any other country
     * ("SW7 5DP", "00-468", "D-10179"), for an address whose country line
     * is missing, for example because it fell outside the envelope window.
     */
    private function determineCountryFromPostalcode(array $lines): void
    {
        foreach (array_keys(self::POSTALCODE_FORMATS) as $country_code) {
            if ($this->determinePostalcode($lines, $country_code, true)) {
                $this->country_code = $country_code;
                return;
            }
        }
    }

    /**
     * A postcode on a line of its own: the city is on the line above it
     * ("LONDON" above "SW7 5DP"), unless that line is the street.
     */
    private function determineCityAbovePostalcode(array $lines): void
    {
        if ($this->postalcode_line === null || $this->city !== '') {
            return;
        }
        $key = $this->previousLineKey($lines, $this->postalcode_line);
        if ($key === null || $key === $this->country_line || preg_match('/\pL/u', $lines[$key]) !== 1) {
            return;
        }
        // A postal district is a city with a number ("Dublin 4"), which only
        // reads as a street where the number goes before the street name.
        $is_district = in_array($this->country_code, $this->street_house_numer_occurrence_first_number)
            && preg_match('/^\pL[\pL\s.\'-]*\s\d{1,2}[a-z]?$/iu', $lines[$key]) === 1;
        if (preg_match('/\d/', $lines[$key]) === 0 || $is_district) {
            $this->city = $lines[$key];
            $this->city_line = $key;
        }
    }

    /**
     * The country line, searched from the bottom up as that is where it
     * belongs. Codes and other short names ("IT", "MAN") only count on the
     * last line, where they cannot be a department or company name.
     */
    private function determineCountry(array $lines): void
    {
        $last_line = $this->lastLineKey($lines);
        for ($key = $last_line; $key !== null && $key >= 0; $key--) {
            $name = $this->findCountryName($lines[$key], $key === $last_line);
            if ($name !== null) {
                $this->country_code = self::countryNames()[$name];
                $this->country = $name;
                $this->country_line = $key;
                return;
            }
        }
    }

    /**
     * "1010 Wien Austria", "L-2763 Luxembourg": the country written
     * behind the city on the last line. The country is cut off the line,
     * unless that leaves only the postcode (the city is the country).
     */
    private function determineCountryBehindCity(array &$lines): void
    {
        $key = $this->lastLineKey($lines);
        if ($key === null) {
            return;
        }
        $words = preg_split('/[\s,]+/u', $lines[$key]);
        for ($count = min(4, count($words) - 1); $count >= 1; $count--) {
            $candidate = implode(' ', array_slice($words, -$count));
            $name = $this->findCountryName($candidate, false);
            if ($name === null) {
                continue;
            }
            $this->country_code = self::countryNames()[$name];
            $this->country = $name;
            $rest = trim(preg_replace('/[\s,]*' . preg_quote($candidate, '/') . '$/u', '', $lines[$key]) ?? '');
            if (preg_match('/\pL/u', preg_replace('/^(?:[A-Z]{1,3} ?- ?)?/i', '', $rest) ?? '') === 1) {
                $lines[$key] = $rest;
            }
            return;
        }
    }

    private function lastLineKey(array $lines): ?int
    {
        for ($key = count($lines) - 1; $key >= 0; $key--) {
            if ($lines[$key] !== '') {
                return $key;
            }
        }
        return null;
    }

    /**
     * The normalized name of the country this text is, or null.
     */
    private function findCountryName(string $text, bool $allow_short): ?string
    {
        $names = self::countryNames();
        foreach (self::normalizeCountryName($text) as $name) {
            if (isset($names[$name]) && ($allow_short || mb_strlen($name, 'UTF-8') > 3)) {
                return $name;
            }
        }
        return null;
    }

    /**
     * The ways a country name can be written down to the form in the list:
     * lowercase, accents stripped ("Belgié" → "belgie") and spelled out
     * German-style ("Österreich" → "oesterreich"), as the list holds both.
     */
    private static function normalizeCountryName(string $text): array
    {
        $text = mb_strtolower($text, 'UTF-8');
        $text = str_replace('.', '', $text);
        $text = trim(preg_replace('/\s+/u', ' ', $text) ?? $text, " \t,;:-()");
        if ($text === '') {
            return [];
        }

        $spelled_out = strtr($text, ['ä' => 'ae', 'ö' => 'oe', 'ü' => 'ue']);
        $variants = [];
        foreach ([$text, $spelled_out] as $variant) {
            $variant = strtr($variant, ['ß' => 'ss', 'ø' => 'o', 'æ' => 'ae', 'œ' => 'oe', 'ł' => 'l', 'đ' => 'd', 'ı' => 'i']);
            if (function_exists('normalizer_normalize')) {
                $decomposed = normalizer_normalize($variant, \Normalizer::FORM_D);
                if ($decomposed !== false) {
                    $variant = preg_replace('/\pM+/u', '', $decomposed) ?? $variant;
                }
            }
            $variants[] = $variant;
        }

        return array_values(array_unique($variants));
    }

    /**
     * Normalized country name => country code, from data/countries.json
     * (built by bin/parse_counties.php).
     */
    private static function countryNames(): array
    {
        if (self::$country_names !== null) {
            return self::$country_names;
        }

        $names = [];
        $countries = json_decode(file_get_contents(__DIR__ . '/data/countries.json'), true) ?: [];
        foreach ($countries as $country_code => $country_names) {
            foreach ($country_names as $country_name) {
                foreach (self::normalizeCountryName((string) $country_name) as $name) {
                    if (strlen($name) > 1) {
                        $names[$name] = $country_code;
                    }
                }
            }
        }
        return self::$country_names = $names;
    }

    public function getRecipient(): array
    {
        return $this->recipient;
    }

    public function getStreet(): string
    {
        // "Avenida Infante Santo, 43": the comma belongs to neither.
        return trim((string) $this->street, " \t,");
    }

    public function getHouseNumber(): int
    {
        return intval(trim((string) $this->house_number));
    }

    public function getHouseNumberAddition(): string
    {
        // "Torņa iela 4, block 1A", "Kapás u. 6-12.": not part of the addition.
        return trim((string) $this->house_number_addition, " \t.,");
    }

    public function getCity(): string
    {
        return trim((string) $this->city);
    }

    public function getPostalCode(): string
    {
        return preg_replace('/\s+/', '', (string) $this->postalcode);
    }

    /**
     * The code is empty when the address gives no country: no country line
     * and no postcode of the default country or of one that is unmistakable.
     */
    public function getCountry(): array
    {
        return [
            'code' => (string) $this->country_code,
            'name' => ucwords((string) $this->country),
        ];
    }

    public function getAddress(): array
    {
        return [
            'recipient'             => $this->getRecipient(),
            'street'                => $this->getStreet(),
            'house_number'          => $this->getHouseNumber(),
            'house_number_addition' => $this->getHouseNumberAddition(),
            'postalcode'            => $this->getPostalCode(),
            'city'                  => $this->getCity(),
            'country'               => $this->getCountry(),
        ];
    }
}
