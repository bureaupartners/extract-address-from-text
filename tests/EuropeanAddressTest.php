<?php

declare(strict_types=1);

namespace BureauPartners\ExtractAddressFromText\Tests;

use BureauPartners\ExtractAddressFromText\AddressExtractor;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * A real address in every European country, written the way that country's
 * post wants it (UPU addressing standards). Mostly the Dutch embassy there;
 * where there is none, a Dutch honorary consulate or a public institution.
 * The source of each address is in the comment above it.
 */
final class EuropeanAddressTest extends TestCase
{
    public static function addresses(): array
    {
        return [
            // https://www.hcch.net/en/states/authorities/details3/?aid=355
            'AD' => [
                ['Ministeri d\'Afers Exteriors', 'Carrer Prat de la Creu 62-64', 'AD500 Andorra la Vella', 'ANDORRA'],
                [
                    'recipient'             => ['Ministeri d\'Afers Exteriors'],
                    'street'                => 'Carrer Prat de la Creu',
                    'house_number'          => 62,
                    'house_number_addition' => '-64',
                    'postalcode'            => 'AD500',
                    'city'                  => 'Andorra la Vella',
                    'country'               => 'AD',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/albania/embassy-tirana
            // No official source publishes the postcode of this street, and an
            // address without one gives the country and the addressee only.
            'AL' => [
                ['Embassy of the Kingdom of the Netherlands', 'Rruga Asim Zeneli 10', 'Tirana', 'ALBANIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => '',
                    'house_number'          => 0,
                    'house_number_addition' => '',
                    'postalcode'            => '',
                    'city'                  => '',
                    'country'               => 'AL',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/austria/embassy-vienna
            'AT' => [
                ['Embassy of the Kingdom of the Netherlands', 'Opernring 5', '1010 Wien', 'AUSTRIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Opernring',
                    'house_number'          => 5,
                    'house_number_addition' => '',
                    'postalcode'            => '1010',
                    'city'                  => 'Wien',
                    'country'               => 'AT',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/bosnia-and-herzegovina/embassy-sarajevo
            'BA' => [
                ['Embassy of the Kingdom of the Netherlands', 'Grbavička 4', '71000 Sarajevo', 'BOSNIA AND HERZEGOVINA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Grbavička',
                    'house_number'          => 4,
                    'house_number_addition' => '',
                    'postalcode'            => '71000',
                    'city'                  => 'Sarajevo',
                    'country'               => 'BA',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/belgium/embassy-brussels
            'BE' => [
                ['Embassy of the Kingdom of the Netherlands', 'Trierstraat 31-35', '1040 Brussel', 'BELGIUM'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Trierstraat',
                    'house_number'          => 31,
                    'house_number_addition' => '-35',
                    'postalcode'            => '1040',
                    'city'                  => 'Brussel',
                    'country'               => 'BE',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/bulgaria/embassy-sofia
            'BG' => [
                ['Embassy of the Kingdom of the Netherlands', 'ul. Oborishte 15', '1504 Sofia', 'BULGARIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'ul. Oborishte',
                    'house_number'          => 15,
                    'house_number_addition' => '',
                    'postalcode'            => '1504',
                    'city'                  => 'Sofia',
                    'country'               => 'BG',
                ],
            ],
            // https://netherlandsworldwide.nl/contact/embassies-consulates-general/belarus/embassy-office-minsk
            'BY' => [
                ['Embassy Office of the Kingdom of the Netherlands', 'ul. Myasnikova 70', '220030, Minsk', 'BELARUS'],
                [
                    'recipient'             => ['Embassy Office of the Kingdom of the Netherlands'],
                    'street'                => 'ul. Myasnikova',
                    'house_number'          => 70,
                    'house_number_addition' => '',
                    'postalcode'            => '220030',
                    'city'                  => 'Minsk',
                    'country'               => 'BY',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/switzerland/embassy-bern
            'CH' => [
                ['Embassy of the Kingdom of the Netherlands', 'Seftigenstrasse 7', '3007 Bern', 'SWITZERLAND'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Seftigenstrasse',
                    'house_number'          => 7,
                    'house_number_addition' => '',
                    'postalcode'            => '3007',
                    'city'                  => 'Bern',
                    'country'               => 'CH',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/cyprus/embassy-nicosia
            'CY' => [
                ['Embassy of the Kingdom of the Netherlands', 'Dimosthenis Severis 34', 'CY-1080 Nicosia', 'CYPRUS'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Dimosthenis Severis',
                    'house_number'          => 34,
                    'house_number_addition' => '',
                    'postalcode'            => 'CY-1080',
                    'city'                  => 'Nicosia',
                    'country'               => 'CY',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/czech-republic/embassy-prague
            'CZ' => [
                ['Embassy of the Kingdom of the Netherlands', 'Gotthardská 6/27', '160 00 Praha 6', 'CZECH REPUBLIC'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Gotthardská',
                    'house_number'          => 6,
                    'house_number_addition' => '/27',
                    'postalcode'            => '16000',
                    'city'                  => 'Praha 6',
                    'country'               => 'CZ',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/germany/embassy-berlin
            'DE' => [
                ['Embassy of the Kingdom of the Netherlands', 'Klosterstraße 50', '10179 Berlin', 'GERMANY'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Klosterstraße',
                    'house_number'          => 50,
                    'house_number_addition' => '',
                    'postalcode'            => '10179',
                    'city'                  => 'Berlin',
                    'country'               => 'DE',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/denmark/embassy-copenhagen
            'DK' => [
                ['Embassy of the Kingdom of the Netherlands', 'Toldbodgade 33', '1253 København K', 'DENMARK'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Toldbodgade',
                    'house_number'          => 33,
                    'house_number_addition' => '',
                    'postalcode'            => '1253',
                    'city'                  => 'København K',
                    'country'               => 'DK',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/estonia/embassy-tallinn
            'EE' => [
                ['Embassy of the Kingdom of the Netherlands', 'Maakri 30', '10145 Tallinn', 'ESTONIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Maakri',
                    'house_number'          => 30,
                    'house_number_addition' => '',
                    'postalcode'            => '10145',
                    'city'                  => 'Tallinn',
                    'country'               => 'EE',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/spain/embassy-madrid
            'ES' => [
                ['Embassy of the Kingdom of the Netherlands', 'Torre Emperador, planta 36', 'Paseo de la Castellana 259-D', '28046 Madrid', 'SPAIN'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands', 'Torre Emperador, planta 36'],
                    'street'                => 'Paseo de la Castellana',
                    'house_number'          => 259,
                    'house_number_addition' => '-D',
                    'postalcode'            => '28046',
                    'city'                  => 'Madrid',
                    'country'               => 'ES',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/finland/embassy-helsinki
            'FI' => [
                ['Embassy of the Kingdom of the Netherlands', 'Erottajankatu 19 B', '00130 Helsinki', 'FINLAND'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Erottajankatu',
                    'house_number'          => 19,
                    'house_number_addition' => 'B',
                    'postalcode'            => '00130',
                    'city'                  => 'Helsinki',
                    'country'               => 'FI',
                ],
            ],
            // https://rigsombudsmanden.fo/media/d3nc1lag/orientering-nr-01-2026-fra-rigsombudsmanden-paa-faeroeerne.pdf
            'FO' => [
                ['Rigsombudsmanden på Færøerne', 'Amtmansbrekkan 6', 'FO-100 Tórshavn', 'FAROE ISLANDS'],
                [
                    'recipient'             => ['Rigsombudsmanden på Færøerne'],
                    'street'                => 'Amtmansbrekkan',
                    'house_number'          => 6,
                    'house_number_addition' => '',
                    'postalcode'            => 'FO-100',
                    'city'                  => 'Tórshavn',
                    'country'               => 'FO',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/france/embassy-paris
            'FR' => [
                ['Embassy of the Kingdom of the Netherlands', '7-9 rue Eblé', '75007 Paris', 'FRANCE'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'rue Eblé',
                    'house_number'          => 7,
                    'house_number_addition' => '-9',
                    'postalcode'            => '75007',
                    'city'                  => 'Paris',
                    'country'               => 'FR',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/united-kingdom/embassy-london
            'GB' => [
                ['Embassy of the Kingdom of the Netherlands', '38 Hyde Park Gate', 'LONDON', 'SW7 5DP', 'UNITED KINGDOM'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Hyde Park Gate',
                    'house_number'          => 38,
                    'house_number_addition' => '',
                    'postalcode'            => 'SW75DP',
                    'city'                  => 'LONDON',
                    'country'               => 'GB',
                ],
            ],
            // https://www.gov.gg/article/119707/Cant-find-the-contact-details-you-need
            'GG' => [
                ['States of Guernsey', 'Sir Charles Frossard House', 'La Charroterie', 'St Peter Port', 'GY1 1FH', 'GUERNSEY'],
                [
                    'recipient'             => ['States of Guernsey', 'Sir Charles Frossard House'],
                    'street'                => 'La Charroterie',
                    'house_number'          => 0,
                    'house_number_addition' => '',
                    'postalcode'            => 'GY11FH',
                    'city'                  => 'St Peter Port',
                    'country'               => 'GG',
                ],
            ],
            // https://www.gibraltar.gov.gi/post-office
            'GI' => [
                ['Royal Gibraltar Post Office', '104 Main Street', 'Gibraltar', 'GX11 1AA', 'GIBRALTAR'],
                [
                    'recipient'             => ['Royal Gibraltar Post Office'],
                    'street'                => 'Main Street',
                    'house_number'          => 104,
                    'house_number_addition' => '',
                    'postalcode'            => 'GX111AA',
                    'city'                  => 'Gibraltar',
                    'country'               => 'GI',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/greece/embassy-athens
            'GR' => [
                ['Embassy of the Kingdom of the Netherlands', 'Leoforos Vassileos Konstantinou 5-7', '106 74 Athina', 'GREECE'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Leoforos Vassileos Konstantinou',
                    'house_number'          => 5,
                    'house_number_addition' => '-7',
                    'postalcode'            => '10674',
                    'city'                  => 'Athina',
                    'country'               => 'GR',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/croatia/embassy-zagreb
            'HR' => [
                ['Embassy of the Kingdom of the Netherlands', 'Ban Centar', 'Augusta Cesarca 6', 'HR-10000 Zagreb', 'CROATIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands', 'Ban Centar'],
                    'street'                => 'Augusta Cesarca',
                    'house_number'          => 6,
                    'house_number_addition' => '',
                    'postalcode'            => 'HR-10000',
                    'city'                  => 'Zagreb',
                    'country'               => 'HR',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/hungary/embassy-budapest
            'HU' => [
                ['Embassy of the Kingdom of the Netherlands', 'Budapest', 'Kapás u. 6-12.', '1027', 'HUNGARY'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Kapás u.',
                    'house_number'          => 6,
                    'house_number_addition' => '-12',
                    'postalcode'            => '1027',
                    'city'                  => 'Budapest',
                    'country'               => 'HU',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/ireland/embassy-dublin
            'IE' => [
                ['Embassy of the Kingdom of the Netherlands', '8 Claremont Road', 'Sandymount', 'Dublin 4', 'D04 XY86', 'IRELAND'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Claremont Road',
                    'house_number'          => 8,
                    'house_number_addition' => '',
                    'postalcode'            => 'D04XY86',
                    'city'                  => 'Dublin 4',
                    'country'               => 'IE',
                ],
            ],
            // https://gov.im/about-the-government/council-of-ministers/the-council-of-ministers/council-of-ministers-line-up
            'IM' => [
                ['Isle of Man Government', 'Government Office', 'Bucks Road', 'Douglas', 'IM1 3PN', 'ISLE OF MAN'],
                [
                    'recipient'             => ['Isle of Man Government', 'Government Office'],
                    'street'                => 'Bucks Road',
                    'house_number'          => 0,
                    'house_number_addition' => '',
                    'postalcode'            => 'IM13PN',
                    'city'                  => 'Douglas',
                    'country'               => 'IM',
                ],
            ],
            // https://consular-protection.ec.europa.eu/netherlands-nl-representation-norway-nor-honorary-consul-reykjavik_en
            'IS' => [
                ['Honorary Consulate of the Kingdom of the Netherlands', 'Tryggvagata 11', '101 Reykjavík', 'ICELAND'],
                [
                    'recipient'             => ['Honorary Consulate of the Kingdom of the Netherlands'],
                    'street'                => 'Tryggvagata',
                    'house_number'          => 11,
                    'house_number_addition' => '',
                    'postalcode'            => '101',
                    'city'                  => 'Reykjavík',
                    'country'               => 'IS',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/italy/embassy-rome
            'IT' => [
                ['Embassy of the Kingdom of the Netherlands', 'Via Michele Mercati 8', '00197 Roma RM', 'ITALY'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Via Michele Mercati',
                    'house_number'          => 8,
                    'house_number_addition' => '',
                    'postalcode'            => '00197',
                    'city'                  => 'Roma RM',
                    'country'               => 'IT',
                ],
            ],
            // https://www.vote.je/polling-stations
            'JE' => [
                ['Jersey Electoral Authority', 'Morier House', 'Halkett Place', 'St Helier', 'JE1 1DD', 'JERSEY'],
                [
                    'recipient'             => ['Jersey Electoral Authority', 'Morier House'],
                    'street'                => 'Halkett Place',
                    'house_number'          => 0,
                    'house_number_addition' => '',
                    'postalcode'            => 'JE11DD',
                    'city'                  => 'St Helier',
                    'country'               => 'JE',
                ],
            ],
            // https://netherlandsandyou.nl/web/liechtenstein/contact
            'LI' => [
                ['Honorary Consulate of the Kingdom of the Netherlands', 'Zollstrasse 84', '9494 Schaan', 'LIECHTENSTEIN'],
                [
                    'recipient'             => ['Honorary Consulate of the Kingdom of the Netherlands'],
                    'street'                => 'Zollstrasse',
                    'house_number'          => 84,
                    'house_number_addition' => '',
                    'postalcode'            => '9494',
                    'city'                  => 'Schaan',
                    'country'               => 'LI',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/lithuania/embassy-vilnius
            'LT' => [
                ['Embassy of the Kingdom of the Netherlands', 'Jasinskio g. 2', 'LT-01112 Vilnius', 'LITHUANIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Jasinskio g.',
                    'house_number'          => 2,
                    'house_number_addition' => '',
                    'postalcode'            => 'LT-01112',
                    'city'                  => 'Vilnius',
                    'country'               => 'LT',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/luxembourg/embassy-luxembourg
            'LU' => [
                ['Embassy of the Kingdom of the Netherlands', '6, rue Sainte-Zithe', 'L-2763 Luxembourg', 'LUXEMBOURG'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'rue Sainte-Zithe',
                    'house_number'          => 6,
                    'house_number_addition' => '',
                    'postalcode'            => 'L-2763',
                    'city'                  => 'Luxembourg',
                    'country'               => 'LU',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/latvia/embassy-riga
            'LV' => [
                ['Embassy of the Kingdom of the Netherlands', 'Torņa iela 4, block 1A', 'Rīga, LV-1050', 'LATVIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Torņa iela',
                    'house_number'          => 4,
                    'house_number_addition' => 'block 1A',
                    'postalcode'            => 'LV-1050',
                    'city'                  => 'Rīga',
                    'country'               => 'LV',
                ],
            ],
            // https://netherlandsandyou.nl/web/monaco/contact
            'MC' => [
                ['Honorary Consulate-General of the Kingdom of the Netherlands', '3 avenue de la Costa', 'MC 98000 Monaco', 'MONACO'],
                [
                    'recipient'             => ['Honorary Consulate-General of the Kingdom of the Netherlands'],
                    'street'                => 'avenue de la Costa',
                    'house_number'          => 3,
                    'house_number_addition' => '',
                    'postalcode'            => 'MC98000',
                    'city'                  => 'Monaco',
                    'country'               => 'MC',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/moldova/embassy-chisinau
            'MD' => [
                ['Embassy of the Kingdom of the Netherlands', 'NBC Building', 'bd. Ștefan cel Mare 73/1', 'MD-2001, Chișinău', 'MOLDOVA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands', 'NBC Building'],
                    'street'                => 'bd. Ștefan cel Mare',
                    'house_number'          => 73,
                    'house_number_addition' => '/1',
                    'postalcode'            => 'MD-2001',
                    'city'                  => 'Chișinău',
                    'country'               => 'MD',
                ],
            ],
            // https://www.netherlandsandyou.nl/web/montenegro/contact
            'ME' => [
                ['Honorary Consulate of the Kingdom of the Netherlands', 'Trg Božane Vučinić 10/1', '81000 Podgorica', 'MONTENEGRO'],
                [
                    'recipient'             => ['Honorary Consulate of the Kingdom of the Netherlands'],
                    'street'                => 'Trg Božane Vučinić',
                    'house_number'          => 10,
                    'house_number_addition' => '/1',
                    'postalcode'            => '81000',
                    'city'                  => 'Podgorica',
                    'country'               => 'ME',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/north-macedonia/embassy-skopje
            'MK' => [
                ['Embassy of the Kingdom of the Netherlands', 'Aminta III 69/71', '1000 Skopje', 'NORTH MACEDONIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Aminta III',
                    'house_number'          => 69,
                    'house_number_addition' => '/71',
                    'postalcode'            => '1000',
                    'city'                  => 'Skopje',
                    'country'               => 'MK',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/malta/embassy-valletta
            'MT' => [
                ['Embassy of the Kingdom of the Netherlands', 'Whitehall Mansions, 2nd floor', 'Ta\' Xbiex Seafront', 'Ta\' Xbiex', 'XBX 1026', 'MALTA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands', 'Whitehall Mansions, 2nd floor'],
                    'street'                => 'Ta\' Xbiex Seafront',
                    'house_number'          => 0,
                    'house_number_addition' => '',
                    'postalcode'            => 'XBX1026',
                    'city'                  => 'Ta\' Xbiex',
                    'country'               => 'MT',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/norway/embassy-oslo
            'NO' => [
                ['Embassy of the Kingdom of the Netherlands', 'Oscars gate 29', '0352 Oslo', 'NORWAY'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Oscars gate',
                    'house_number'          => 29,
                    'house_number_addition' => '',
                    'postalcode'            => '0352',
                    'city'                  => 'Oslo',
                    'country'               => 'NO',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/poland/embassy-warsaw
            'PL' => [
                ['Embassy of the Kingdom of the Netherlands', 'ul. Kawalerii 10', '00-468 Warszawa', 'POLAND'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'ul. Kawalerii',
                    'house_number'          => 10,
                    'house_number_addition' => '',
                    'postalcode'            => '00-468',
                    'city'                  => 'Warszawa',
                    'country'               => 'PL',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/portugal/embassy-lisbon
            'PT' => [
                ['Embassy of the Kingdom of the Netherlands', 'Avenida Infante Santo 43 5º', '1399-011 Lisboa', 'PORTUGAL'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Avenida Infante Santo',
                    'house_number'          => 43,
                    'house_number_addition' => '5º',
                    'postalcode'            => '1399-011',
                    'city'                  => 'Lisboa',
                    'country'               => 'PT',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/romania/embassy-bucharest
            'RO' => [
                ['Embassy of the Kingdom of the Netherlands', 'Bd. Dimitrie Pompeiu nr. 5-7', 'Hermes Business Campus, Clădirea B, etaj 8', 'Sector 2', '020335 București', 'ROMANIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Bd. Dimitrie Pompeiu',
                    'house_number'          => 5,
                    'house_number_addition' => '-7',
                    'postalcode'            => '020335',
                    'city'                  => 'București',
                    'country'               => 'RO',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/serbia/embassy-belgrade
            'RS' => [
                ['Embassy of the Kingdom of the Netherlands', 'Milentija Popovića 5b', '11070 Novi Beograd', 'SERBIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Milentija Popovića',
                    'house_number'          => 5,
                    'house_number_addition' => 'b',
                    'postalcode'            => '11070',
                    'city'                  => 'Novi Beograd',
                    'country'               => 'RS',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/russian-federation/embassy-moscow
            'RU' => [
                ['Embassy of the Kingdom of the Netherlands', 'Kalashny pereulok 6', 'Moskva', '115127', 'RUSSIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Kalashny pereulok',
                    'house_number'          => 6,
                    'house_number_addition' => '',
                    'postalcode'            => '115127',
                    'city'                  => 'Moskva',
                    'country'               => 'RU',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/sweden/embassy-stockholm
            'SE' => [
                ['Embassy of the Kingdom of the Netherlands', 'Götgatan 16A', '118 46 Stockholm', 'SWEDEN'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Götgatan',
                    'house_number'          => 16,
                    'house_number_addition' => 'A',
                    'postalcode'            => '11846',
                    'city'                  => 'Stockholm',
                    'country'               => 'SE',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/slovenia/embassy-ljubljana
            'SI' => [
                ['Embassy of the Kingdom of the Netherlands', 'Palača Kapitelj', 'Poljanski nasip 6', '1000 Ljubljana', 'SLOVENIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands', 'Palača Kapitelj'],
                    'street'                => 'Poljanski nasip',
                    'house_number'          => 6,
                    'house_number_addition' => '',
                    'postalcode'            => '1000',
                    'city'                  => 'Ljubljana',
                    'country'               => 'SI',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/slovakia/embassy-bratislava
            'SK' => [
                ['Embassy of the Kingdom of the Netherlands', 'Fraňa Kráľa 5', '811 05 Bratislava 1', 'SLOVAKIA'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Fraňa Kráľa',
                    'house_number'          => 5,
                    'house_number_addition' => '',
                    'postalcode'            => '81105',
                    'city'                  => 'Bratislava 1',
                    'country'               => 'SK',
                ],
            ],
            // https://esteri.sm/pub1/EsteriSM/en/Contatti/Contatti.html
            'SM' => [
                ['Segreteria di Stato per gli Affari Esteri', 'Palazzo Begni', 'Contrada Omerelli 31', '47890 San Marino', 'SAN MARINO'],
                [
                    'recipient'             => ['Segreteria di Stato per gli Affari Esteri', 'Palazzo Begni'],
                    'street'                => 'Contrada Omerelli',
                    'house_number'          => 31,
                    'house_number_addition' => '',
                    'postalcode'            => '47890',
                    'city'                  => 'San Marino',
                    'country'               => 'SM',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/turkiye/embassy-ankara
            'TR' => [
                ['Embassy of the Kingdom of the Netherlands', 'Yukarı Dikmen Mahallesi', '639 Cadde - 647 Sokak No. 10', '06550 Çankaya/Ankara', 'TURKEY'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands', 'Yukarı Dikmen Mahallesi'],
                    'street'                => '639 Cadde - 647 Sokak',
                    'house_number'          => 10,
                    'house_number_addition' => '',
                    'postalcode'            => '06550',
                    'city'                  => 'Çankaya/Ankara',
                    'country'               => 'TR',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/ukraine/embassy-kyiv
            'UA' => [
                ['Embassy of the Kingdom of the Netherlands', 'Kontraktova ploshcha 7', 'Kyiv', '04070', 'UKRAINE'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Kontraktova ploshcha',
                    'house_number'          => 7,
                    'house_number_addition' => '',
                    'postalcode'            => '04070',
                    'city'                  => 'Kyiv',
                    'country'               => 'UA',
                ],
            ],
            // https://www.vatican.va/content/romancuria/en/istituzioni-collegate-con-la-santa-sede/pontificia-academia-provita/struttura.html
            'VA' => [
                ['Pontificia Accademia per la Vita', 'Piazza San Calisto 16', '00120 Città del Vaticano', 'VATICAN CITY'],
                [
                    'recipient'             => ['Pontificia Accademia per la Vita'],
                    'street'                => 'Piazza San Calisto',
                    'house_number'          => 16,
                    'house_number_addition' => '',
                    'postalcode'            => '00120',
                    'city'                  => 'Città del Vaticano',
                    'country'               => 'VA',
                ],
            ],
            // https://www.netherlandsworldwide.nl/contact/embassies-consulates-general/kosovo/embassy-pristina
            'XK' => [
                ['Embassy of the Kingdom of the Netherlands', 'Xhemajl Berisha 12', 'Velania', '10000 Prishtina', 'KOSOVO'],
                [
                    'recipient'             => ['Embassy of the Kingdom of the Netherlands'],
                    'street'                => 'Xhemajl Berisha',
                    'house_number'          => 12,
                    'house_number_addition' => '',
                    'postalcode'            => '10000',
                    'city'                  => 'Prishtina',
                    'country'               => 'XK',
                ],
            ],
        ];
    }

    #[DataProvider('addresses')]
    public function testExtractsTheAddress(array $lines, array $expected): void
    {
        $address = (new AddressExtractor(implode(PHP_EOL, $lines)))->getAddress();
        $address['country'] = $address['country']['code'];

        $this->assertSame($expected, $address, implode(' / ', $lines));
    }
}
