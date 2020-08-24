<?php
declare (strict_types = 1);
namespace BureauPartners\ExtractAddressFromText\Tests;

use BureauPartners\ExtractAddressFromText\AddressExtractor;
use PHPUnit\Framework\TestCase;

final class AddressTest extends TestCase
{

    private $test_addresses = [
        // NL
        [
            'text'   => 'BureauPartners B.V.' . PHP_EOL . 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 175' . PHP_EOL . '3316GZ Dordrecht',
            'result' => [
                'recipient'             => [
                    'BureauPartners B.V.',
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '175',
                'house_number_addition' => '',
                'postalcode'            => '3316GZ',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 175' . PHP_EOL . '3316GZ Dordrecht',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '175',
                'house_number_addition' => '',
                'postalcode'            => '3316GZ',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => 'Retouradres Postbus 8090, 3300AA Dordrecht' . PHP_EOL . 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 175' . PHP_EOL . '3316GZ Dordrecht',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '175',
                'house_number_addition' => '',
                'postalcode'            => '3316GZ',
                'city'                  => 'Dordrecht',
                'country'               => 'NL',
            ],
        ],
        [
            'text'   => 'M. Hameetman' . PHP_EOL . 'Pieter Zeemanweg 175 A' . PHP_EOL . '3316 GZ Dordrecht' . PHP_EOL . 'Nederland',
            'result' => [
                'recipient'             => [
                    'M. Hameetman',
                ],
                'street'                => 'Pieter Zeemanweg',
                'house_number'          => '175',
                'house_number_addition' => 'A',
                'postalcode'            => '3316GZ',
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
            'text'   => 'BureauPartners' . PHP_EOL . 'M. Hameetman' . PHP_EOL . 'Koningin Astridlaan 49' . PHP_EOL . '1780 Wemmel' . PHP_EOL . 'BELGIUM',
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
            'text'   => 'M. Hameetman' . PHP_EOL . 'Koningin Astridlaan 491 2' . PHP_EOL . 'B-1780 Wemmel' . PHP_EOL . 'België',
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
    ];

    private function getTestAddresses()
    {
        foreach ($this->test_addresses as &$address) {
            $address_lines = explode(PHP_EOL, $address['text']);
            $text          = [];
            foreach ($address_lines as $line) {
                // add random text to lines
                if (rand(0, 100) < 40) {
                    //    $text[] = $this->generateWords(rand(1, 10));
                }
                $text[] = $line;
            }
            $address['text'] = implode(PHP_EOL, $text);
        }
        return $this->test_addresses;
    }

    private function generateWords($words = 4)
    {
        $string = '';
        for ($x = 1; $x <= $words; $x++) {
            $string .= $this->generateWord(rand(3, 6)) . ' ';
        }
        return trim($string);
    }

    private function generateWord($length = 6)
    {
        $string     = '';
        $vowels     = array('a', 'e', 'i', 'o', 'u');
        $consonants = array('b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm', 'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z');

        $max = $length / 2;
        for ($i = 1; $i <= $max; $i++) {
            $string .= $consonants[rand(0, 19)];
            $string .= $vowels[rand(0, 4)];
        }

        return $string;
    }

    public function testExtractsStreetFromAddress(): void
    {
        foreach ($this->getTestAddresses() as $address) {
            $extractor = new AddressExtractor($address['text']);
            echo implode(',', explode(PHP_EOL, $address['text'])) . PHP_EOL;
            //print_r($extractor->getAddress());
            $this->assertEquals($address['result']['street'], $extractor->getStreet());
            $this->assertEquals($address['result']['house_number'], $extractor->getHouseNumber());
            $this->assertEquals($address['result']['house_number_addition'], $extractor->getHouseNumberAddition());
            $this->assertEquals($address['result']['postalcode'], $extractor->getPostalCode());
            $this->assertEquals($address['result']['city'], $extractor->getCity());
            $this->assertEquals($address['result']['country'], $extractor->getCountry()['code']);
        }
    }
}