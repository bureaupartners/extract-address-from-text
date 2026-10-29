<?php

declare(strict_types=1);

namespace BureauPartners\ExtractAddressFromText\Tests;

use BureauPartners\ExtractAddressFromText\AddressExtractor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * How the country is decided: from the country line, from a country behind
 * the city, or from a postcode that can only belong to one country. When
 * none of these say anything, the country is unknown rather than the
 * default country.
 */
final class CountryDetectionTest extends TestCase
{
    private const EMBASSY = 'Embassy of the Kingdom of the Netherlands';

    public static function addresses(): array
    {
        return [
            // Text from a PDF often keeps the space after the last word.
            'country line with a trailing space' => [
                [self::EMBASSY, 'Opernring 5', '1010 Wien', 'AUSTRIA '],
                ['country' => 'AT', 'postalcode' => '1010', 'city' => 'Wien', 'street' => 'Opernring', 'house_number' => 5],
            ],
            'non-breaking spaces' => [
                [self::EMBASSY . "\u{00A0}", "Opernring\u{00A0}5", "1010\u{00A0}Wien", "AUSTRIA\u{00A0}"],
                ['country' => 'AT', 'postalcode' => '1010', 'city' => 'Wien', 'recipient' => [self::EMBASSY]],
            ],
            // "1040 BR" + "ussel" used to be read as a Dutch postcode.
            'no country line, Belgian postcode' => [
                [self::EMBASSY, 'Trierstraat 31-35', '1040 Brussel'],
                ['country' => '', 'postalcode' => '', 'city' => ''],
            ],
            'no country line, Austrian postcode' => [
                [self::EMBASSY, 'Opernring 5', '1010 Wien'],
                ['country' => '', 'postalcode' => ''],
            ],
            'country behind the city' => [
                [self::EMBASSY, 'Opernring 5', '1010 Wien, Austria'],
                ['country' => 'AT', 'postalcode' => '1010', 'city' => 'Wien'],
            ],
            'country behind the city without a comma' => [
                [self::EMBASSY, 'Opernring 5', '1010 Wien Oostenrijk'],
                ['country' => 'AT', 'postalcode' => '1010', 'city' => 'Wien'],
            ],
            // The city is the country: keep it as the city.
            'city state behind the postcode' => [
                [self::EMBASSY, '6, rue Sainte-Zithe', 'L-2763 Luxembourg'],
                ['country' => 'LU', 'postalcode' => 'L-2763', 'city' => 'Luxembourg'],
            ],
            'country prefix on the postcode' => [
                [self::EMBASSY, 'Opernring 5', 'A-1010 Wien'],
                ['country' => 'AT', 'postalcode' => 'A-1010', 'city' => 'Wien'],
            ],
            'british postcode before the city' => [
                [self::EMBASSY, '38 Hyde Park Gate', 'SW7 5DP LONDON', 'UNITED KINGDOM'],
                [
                    'country'               => 'GB',
                    'postalcode'            => 'SW75DP',
                    'city'                  => 'LONDON',
                    'street'                => 'Hyde Park Gate',
                    'house_number'          => 38,
                    'house_number_addition' => '',
                    'recipient'             => [self::EMBASSY],
                ],
            ],
            // The country line can fall outside the envelope window.
            'british postcode without a country line' => [
                [self::EMBASSY, '38 Hyde Park Gate', 'SW7 5DP LONDON'],
                ['country' => 'GB', 'postalcode' => 'SW75DP', 'city' => 'LONDON'],
            ],
            'british postcode on a line of its own' => [
                [self::EMBASSY, '38 Hyde Park Gate', 'LONDON', 'SW7 5DP', 'U.K.'],
                ['country' => 'GB', 'postalcode' => 'SW75DP', 'city' => 'LONDON', 'street' => 'Hyde Park Gate'],
            ],
            'british postcode after the city' => [
                [self::EMBASSY, '38 Hyde Park Gate', 'London SW7 5DP', 'England'],
                ['country' => 'GB', 'postalcode' => 'SW75DP', 'city' => 'London'],
            ],
            'polish postcode without a country line' => [
                [self::EMBASSY, 'ul. Kawalerii 10', '00-468 Warszawa'],
                ['country' => 'PL', 'postalcode' => '00-468', 'city' => 'Warszawa'],
            ],
            // "EU" in countries.json used to share its names with France.
            'France in Dutch' => [
                [self::EMBASSY, '7-9 rue Eblé', '75007 Parijs', 'Frankrijk'],
                ['country' => 'FR', 'postalcode' => '75007'],
            ],
            'Faroe Islands in Dutch' => [
                ['Rigsombudsmanden på Færøerne', 'Amtmansbrekkan 6', 'FO-100 Tórshavn', 'Faeröer'],
                ['country' => 'FO', 'postalcode' => 'FO-100', 'city' => 'Tórshavn'],
            ],
            'country in cyrillic' => [
                [self::EMBASSY, 'Kalashny pereulok 6', '115127 Moskva', 'Россия'],
                ['country' => 'RU', 'postalcode' => '115127', 'city' => 'Moskva'],
            ],
            // Short names only count on the last line.
            'a company called MAN' => [
                ['MAN', 'Pieter Zeemanweg 175', '3316 DD Dordrecht'],
                ['country' => 'NL', 'postalcode' => '3316DD', 'recipient' => ['MAN']],
            ],
            'an IT department' => [
                ['Postbode', 'IT', 'Pieter Zeemanweg 175', '3316 DD Dordrecht'],
                ['country' => 'NL', 'postalcode' => '3316DD', 'recipient' => ['Postbode', 'IT']],
            ],
            // Text extraction can lose the space between postcode and city.
            'dutch postcode glued to the city' => [
                ['M. Hameetman', 'Pieter Zeemanweg 175', '3316DDDordrecht'],
                ['country' => 'NL', 'postalcode' => '3316DD', 'city' => 'Dordrecht'],
            ],
            'not an address' => [
                ['Geachte heer,', 'Hierbij ontvangt u', 'de factuur'],
                ['country' => '', 'postalcode' => ''],
            ],
        ];
    }

    #[DataProvider('addresses')]
    public function testDeterminesTheCountry(array $lines, array $expected): void
    {
        $address = (new AddressExtractor(implode(PHP_EOL, $lines)))->getAddress();
        $address['country'] = $address['country']['code'];

        foreach ($expected as $field => $value) {
            $this->assertSame($value, $address[$field], $field . ' of: ' . implode(' / ', $lines));
        }
    }
}
