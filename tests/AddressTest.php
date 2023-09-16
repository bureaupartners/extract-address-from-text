<?php

declare(strict_types=1);

namespace BureauPartners\ExtractAddressFromText\Tests;

use BureauPartners\ExtractAddressFromText\AddressExtractor;
use PHPUnit\Framework\TestCase;

final class AddressTest extends TestCase
{

    private $test_addresses = [
        // NL
        [
            'text'   => 'BureauPartners B.V.' . PHP_EOL . 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 175' . PHP_EOL . '3316DD Dordrecht',
            'result' => [
                'recipient'             => [
                    'BureauPartners B.V.',
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '175',
                'house_number_addition' => '',
                'postalcode'            => '3316DD',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 175' . PHP_EOL . '3316CC Dordrecht',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '175',
                'house_number_addition' => '',
                'postalcode'            => '3316CC',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => 'Retouradres Postbus 8090, 3300AA Dordrecht' . PHP_EOL . 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 175' . PHP_EOL . '3316BB Dordrecht',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '175',
                'house_number_addition' => '',
                'postalcode'            => '3316BB',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 175 A' . PHP_EOL . '3316 AA Dordrecht' . PHP_EOL . 'Nederland',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '175',
                'house_number_addition' => 'A',
                'postalcode'            => '3316AA',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => 'BureauPartners' . PHP_EOL . 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 1 - 75' . PHP_EOL . '3300 AA Dordrecht' . PHP_EOL . 'Nederland',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '1',
                'house_number_addition' => '- 75',
                'postalcode'            => '3300AA',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => 'BureauPartners' . PHP_EOL . 'M. Hameetman' . PHP_EOL . '1e Kruisweg 36' . PHP_EOL . '3300 AA Dordrecht' . PHP_EOL . 'Nederland',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => '1e Kruisweg',
                'house_number'          => '36',
                'house_number_addition' => '',
                'postalcode'            => '3300AA',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        // BE
        [
            'text'   => 'BureauPartners' . PHP_EOL . 'M. Hameetman' . PHP_EOL . 'Koningin Astridlaan 49' . PHP_EOL . '1780 Wemmel' . PHP_EOL . 'Belgiė',
            'result' => [
                'recipient'             => [
                    'BureauPartners',
                    'M. Hameetman',
                ],
                'street'                => 'Koningin Astridlaan',
                'house_number'          => '49',
                'house_number_addition' => '',
                'postalcode'            => '1780',
                'city'                  => 'Wemmel',
                'country'               => 'BE',
            ],
        ],
        [
            'text'   => 'M. Hameetman' . PHP_EOL . 'Koningin Astridlaan 491 2' . PHP_EOL . 'B-1780 Wemmel' . PHP_EOL . 'Belgié',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Koningin Astridlaan',
                'house_number'          => '491',
                'house_number_addition' => '2',
                'postalcode'            => 'B-1780',
                'city'                  => 'Wemmel',
                'country'               => 'BE',
            ],
        ],
        [
            'text'   => 'M. Hameetman' . PHP_EOL . 'Koningin Astridlaan 491' . PHP_EOL . 'B-1780 Wemmel' . PHP_EOL . 'Belgique',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Koningin Astridlaan',
                'house_number'          => '491',
                'house_number_addition' => '',
                'postalcode'            => 'B-1780',
                'city'                  => 'Wemmel',
                'country'               => 'BE',
            ],
        ],
        [
            'text' => 'Nederlandse ambassade in Berlijn' . PHP_EOL . 'Wepke Kingma' . PHP_EOL . 'Klosterstraße 50' . PHP_EOL . '10179 Berlijn' . PHP_EOL . 'Duitsland',
            'result' => [
                'recipient'             => [
                    'Nederlandse ambassade in Berlijn',
                    'Wepke Kingma'
                ],
                'street'                => 'Klosterstraße',
                'house_number'          => '50',
                'house_number_addition' => '',
                'postalcode'            => '10179',
                'city'                  => 'Berlijn',
                'country'               => 'DE',
            ],
        ],
        [
            'text' => 'Nederlandse ambassade in Parijs' . PHP_EOL . 'Pieter de Gooijer' . PHP_EOL . 'Rue Eblé 7-9' . PHP_EOL . '75007 Parijs' . PHP_EOL . 'France',
            'result' => [
                'recipient'             => [
                    'Nederlandse ambassade in Parijs',
                    'Pieter de Gooijer'
                ],
                'street'                => 'Rue Eblé',
                'house_number'          => '7',
                'house_number_addition' => '-9',
                'postalcode'            => '75007',
                'city'                  => 'Parijs',
                'country'               => 'FR',
            ],
        ],
        [
            'text' => 'Nederlandse ambassade in Madrid' . PHP_EOL . 'Jan Versteeg' . PHP_EOL . 'Pº de la Castellana 259-D' . PHP_EOL . 'Torre Espacio - Verdieping 36' . PHP_EOL . '28046 Madrid' . PHP_EOL . 'Spanje',
            'result' => [
                'recipient'             => [
                    'Nederlandse ambassade in Madrid',
                    'Jan Versteeg'
                ],
                'street'                => 'Pº de la Castellana',
                'house_number'          => '259',
                'house_number_addition' => '-D',
                'postalcode'            => '28046',
                'city'                  => 'Madrid',
                'country'               => 'ES',
            ],
        ],
        [
            'text' => 'Nederlandse ambassade in Londen' . PHP_EOL . 'Karel J.G. Van Oosterom' . PHP_EOL . '38 Hyde Park Gate' . PHP_EOL . 'Londen SW75DP' . PHP_EOL . 'United Kingdom',
            'result' => [
                'recipient'             => [
                    'Nederlandse ambassade in Madrid',
                    'Karel J.G. Van Oosterom'
                ],
                'street'                => 'Hyde Park Gate',
                'house_number'          => '38',
                'house_number_addition' => '',
                'postalcode'            => 'SW75DP',
                'city'                  => 'Londen',
                'country'               => 'GB',
            ],
        ],
        [
            'text'   => 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 17.500' . PHP_EOL . '3316 GZ Dordrecht' . PHP_EOL . 'Nederland',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '17500',
                'house_number_addition' => '',
                'postalcode'            => '3316GZ',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => 'Postbus 1234, 1234AB Utrecht' . PHP_EOL . 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 17.500' . PHP_EOL . '3316 GZ Dordrecht' . PHP_EOL . 'Nederland',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '17500',
                'house_number_addition' => '',
                'postalcode'            => '3316GZ',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => '12343787292034' . PHP_EOL . 'Postbus 1234, 1234AB Utrecht' . PHP_EOL . 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 17.500' . PHP_EOL . '3316 GZ Dordrecht' . PHP_EOL . 'Nederland',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '17500',
                'house_number_addition' => '',
                'postalcode'            => '3316GZ',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => '12343787292034' . PHP_EOL . 'Postbus 1234, 1234AB Utrecht' . PHP_EOL . 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 17.500' . PHP_EOL . '3316 HH, Dordrecht' . PHP_EOL . 'Nederland',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '17500',
                'house_number_addition' => '',
                'postalcode'            => '3316HH',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 175' . PHP_EOL . '3316 DD Den Helder' . PHP_EOL . 'NL',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '175',
                'house_number_addition' => '',
                'postalcode'            => '3316DD',
                'city'                  => 'Den Helder',
                'country'               => 'NL',
            ],
        ]
    ];

    private function getTestAddresses()
    {
        foreach ($this->test_addresses as &$address) {
            $address_lines = explode(PHP_EOL, $address['text']);
            $text          = [];
            foreach ($address_lines as $line) {
                $text[] = $line;
            }
            $address['text'] = implode(PHP_EOL, $text);
        }
        return $this->test_addresses;
    }

    public function testExtractsStreetFromAddress(): void
    {
        foreach ($this->getTestAddresses() as $address) {
            $extractor = new AddressExtractor($address['text']);
            $this->assertEquals($address['result']['street'], $extractor->getStreet());
            $this->assertEquals($address['result']['house_number'], $extractor->getHouseNumber());
            $this->assertEquals($address['result']['house_number_addition'], $extractor->getHouseNumberAddition());
            $this->assertEquals($address['result']['postalcode'], $extractor->getPostalCode());
            $this->assertEquals($address['result']['city'], $extractor->getCity());
            $this->assertEquals($address['result']['country'], $extractor->getCountry()['code']);
        }
    }
}
