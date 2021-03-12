<?php

namespace BureauPartners\ExtractAddressFromText;

class AddressExtractor
{
    private $recipient             = [];
    private $street                = null;
    private $house_number          = null;
    private $house_number_addition = null;
    private $postalcode            = null;
    private $city                  = null;
    private $country               = null;
    private $country_code          = null;
    private $street_matched        = false;

    private $postalcode_regex_per_country = [
        // Inspiration extract zipcodes (https://rgxdb.com/r/316F0I2N)

        'NL' => [
            'pattern'    => "/^((?:NL-)?(?:[1-9]\d{3} ?(?:[A-EGHJ-NPRTVWXZ][A-EGHJ-NPRSTVWXZ]|S[BCEGHJ-NPRTVWXZ]))) ([a-zA-Z \-‘'\.]+)/i",
            'postalcode' => 1,
            'city'       => 2,
        ],
        'BE' => [
            'pattern'    => "/^((?:B-)?(?:(?:[1-9])(?:\d{3}))) ?([a-zA-Z \-‘'\.]+)/i",
            'postalcode' => 1,
            'city'       => 2,
        ],
        'DE' => [
            'pattern'    => "/^((?:(?:[1-9])(?:\d{4}))) ?([a-zA-Z \-‘'\.]+)/i",
            'postalcode' => 1,
            'city'       => 2,
        ],
        'FR' => [
            'pattern'    => "/^((?:[0-8]\d|9[0-8])\d{3}) ?([a-zA-Z \-‘'\.]+)/i",
            'postalcode' => 1,
            'city'       => 2,
        ],
        'ES' => [
            'pattern'    => "/^((?:0[1-9]|[1-4]\d|5[0-2])\d{3}) ?([a-zA-Z \-‘'\.]+)/i",
            'postalcode' => 1,
            'city'       => 2,
        ],
        'GB' => [
            'pattern'    => "/^(.*) (GIR 0AA|(?:(?:(?:A[BL]|B[ABDHLNRSTX]?|C[ABFHMORTVW]|D[ADEGHLNTY]|E[HNX]?|F[KY]|G[LUY]?|H[ADGPRSUX]|I[GMPV]|JE|K[ATWY]|L[ADELNSU]?|M[EKL]?|N[EGNPRW]?|O[LX]|P[AEHLOR]|R[GHM]|S[AEGK-PRSTY]?|T[ADFNQRSW]|UB|W[ADFNRSV]|YO|ZE)[1-9]?\d|(?:(?:E|N|NW|SE|SW|W)1|EC[1-4]|WC[12])[A-HJKMNPR-Y]|(?:SW|W)(?:[2-9]|[1-9]\d)|EC[1-9]\d)\d[ABD-HJLNP-UW-Z]{2}))$/i",
            'postalcode' => 2,
            'city'       => 1,
        ],
    ];

    private $street_house_numer_occurrence_first_number = [
        'GB'
    ];

    public function __construct(string $address, string $default_country = 'NL')
    {
        $address = explode(PHP_EOL, $address);
        if (count($address) < 3) {
            return false;
        }
        if ($default_country !== null) {
            $this->country_code = $default_country;
        }
        // Determine country
        $this->determineCountry($address);
        foreach ($address as $address_line) {
            // Determine street and housenumber
            $this->determineStreet($address_line);
            // Determine recipient
            $this->determineRecipient($address_line);
            // Determine postalcode
            $this->determinePostalcode($address_line);
        }
        return $address;
    }

    private function determineRecipient(string $address_line) : void
    {
        if ($this->street_matched === false && strpos(strtolower($address_line), 'retour') === false) {
            // Check if the line contains a postalcode to be a return address
            if (preg_match("/((?:NL-)?(?:[1-9]\d{3} ?(?:[A-EGHJ-NPRTVWXZ][A-EGHJ-NPRSTVWXZ]|S[BCEGHJ-NPRTVWXZ])))/i", $address_line) === 0) {
                $this->recipient[] = $address_line;
            }
        }
    }

    private function determineStreet(string $address_line) : void
    {
        if(!in_array($this->country_code, $this->street_house_numer_occurrence_first_number)){
            $street_extraction_success = preg_match('/(?P<street>(.\S)+?([\S.]+)) (?P<housenumber>\d+)\s*(?P<housenumber_addition>(.)+)?/i', $address_line, $street_parts);
        }else{
            $street_extraction_success = preg_match('/(?P<housenumber>\d+)\s*(?P<street>(.)+)?/i', $address_line, $street_parts);
        }
        if ($street_extraction_success && count($this->recipient) > 0 && $this->street_matched === false && strpos(strtolower($address_line), 'retour') === false) {

            if (isset($street_parts['street'])) {
                if (strlen($street_parts['street']) > 2 && !in_array($this->country_code, $this->street_house_numer_occurrence_first_number)) {
                    $street_parts['street'] = substr($address_line, 0, (strpos($address_line, $street_parts['street']) + strlen($street_parts['street'])));
                }
                $this->street = $street_parts['street'];
            }
            if (isset($street_parts['housenumber'])) {
                $this->house_number = $street_parts['housenumber'];
            }
            if (isset($street_parts['housenumber_addition'])) {
                $this->house_number_addition = $street_parts['housenumber_addition'];
            }
            $this->street_matched = true;
        }
    }

    private function determinePostalcode(string $address_line): void
    {
        if (key_exists($this->country_code, $this->postalcode_regex_per_country) && strpos(strtolower($address_line), 'retour') === false) {
            if (preg_match($this->postalcode_regex_per_country[$this->country_code]['pattern'], $address_line, $matches)) {
                if (key_exists($this->postalcode_regex_per_country[$this->country_code]['postalcode'], $matches)) {
                    $this->postalcode = $matches[$this->postalcode_regex_per_country[$this->country_code]['postalcode']];
                }
                if (key_exists($this->postalcode_regex_per_country[$this->country_code]['city'], $matches)) {
                    $this->city = $matches[$this->postalcode_regex_per_country[$this->country_code]['city']];
                }
            }
        }
    }

    private function determineCountry(array $address) : void
    {
        foreach ($address as &$address_line) {
            $address_line = mb_strtolower($address_line, 'UTF-8');
            if (strlen($address_line) < 1) {
                unset($address_line);
            }

        }

        foreach (json_decode(file_get_contents(__DIR__ . '/data/countries.json')) as $country_code => $country_names) {
            foreach ($country_names as $country_name) {
                if (strlen($country_name) > 2 && false !== $country_key = array_search($country_name, $address)) {
                    $this->country_code = $country_code;
                    $this->country      = $address[$country_key];
                }
            }
        }

        if($this->country == null){
            foreach($address as &$address_line){
                $address_line  = iconv('utf-8', 'ASCII//IGNORE//TRANSLIT', $address_line);
            }
            foreach (json_decode(file_get_contents(__DIR__ . '/data/countries.json')) as $country_code => $country_names) {
                foreach ($country_names as $country_name) {
                    if (strlen($country_name) > 2 && false !== $country_key = array_search($country_name, $address)) {
                        $this->country_code = $country_code;
                        $this->country      = $address[$country_key];
                    }
                }
            }
        }
    }

    public function getRecipient() : array
    {
        return $this->recipient;
    }

    public function getStreet() : string
    {
        return trim($this->street);
    }

    public function getHouseNumber() : int
    {
        return intval(trim($this->house_number));
    }

    public function getHouseNumberAddition() : string
    {
        return trim($this->house_number_addition);
    }

    public function getCity() : string
    {
        return trim($this->city);
    }

    public function getPostalCode() : string
    {
        return preg_replace('/\s+/', '', $this->postalcode);
    }

    public function getCountry() : array
    {
        return [
            'code' => $this->country_code,
            'name' => ucwords($this->country),
        ];
    }

    public function getAddress() : array
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
