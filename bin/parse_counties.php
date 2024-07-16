#!/usr/bin/env php
<?php



use PragmaRX\Countries\Package\Countries;
use PragmaRX\Countries\Package\Support\Collection;

include_once __DIR__ . '/../vendor/autoload.php';

$name_keys = ['name.common', 'name.official', 'alt_spellings', 'name_*'];
$countries = [];

function decode_encoded_utf8($string)
{
    return preg_replace_callback('#\\\\u([0-9a-f]{4})#ism', function ($matches) {
        return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE");
    }, $string);
}

function clean_name($string)
{
    $string = utf8_to_ansi($string);
    $string = iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE", decode_encoded_utf8($string));
    $string = preg_replace('/\pM*/u', '', normalizer_normalize($string, Normalizer::FORM_D));
    $string = trim(mb_strtolower($string));
    return $string;
}

function utf8_to_ansi($string)
{
    $utf8_ansi2 = array(
        "\u00c0" => "À",
        "\u00c1" => "Á",
        "\u00c2" => "Â",
        "\u00c3" => "Ã",
        "\u00c4" => "Ä",
        "\u00c5" => "Å",
        "\u00c6" => "Æ",
        "\u00c7" => "Ç",
        "\u00c8" => "È",
        "\u00c9" => "É",
        "\u00ca" => "Ê",
        "\u00cb" => "Ë",
        "\u00cc" => "Ì",
        "\u00cd" => "Í",
        "\u00ce" => "Î",
        "\u00cf" => "Ï",
        "\u00d1" => "Ñ",
        "\u00d2" => "Ò",
        "\u00d3" => "Ó",
        "\u00d4" => "Ô",
        "\u00d5" => "Õ",
        "\u00d6" => "Ö",
        "\u00d8" => "Ø",
        "\u00d9" => "Ù",
        "\u00da" => "Ú",
        "\u00db" => "Û",
        "\u00dc" => "Ü",
        "\u00dd" => "Ý",
        "\u00df" => "ß",
        "\u00e0" => "à",
        "\u00e1" => "á",
        "\u00e2" => "â",
        "\u00e3" => "ã",
        "\u00e4" => "ä",
        "\u00e5" => "å",
        "\u00e6" => "æ",
        "\u00e7" => "ç",
        "\u00e8" => "è",
        "\u00e9" => "é",
        "\u00ea" => "ê",
        "\u00eb" => "ë",
        "\u00ec" => "ì",
        "\u00ed" => "í",
        "\u00ee" => "î",
        "\u00ef" => "ï",
        "\u00f0" => "ð",
        "\u00f1" => "ñ",
        "\u00f2" => "ò",
        "\u00f3" => "ó",
        "\u00f4" => "ô",
        "\u00f5" => "õ",
        "\u00f6" => "ö",
        "\u00f8" => "ø",
        "\u00f9" => "ù",
        "\u00fa" => "ú",
        "\u00fb" => "û",
        "\u00fc" => "ü",
        "\u00fd" => "ý",
        "\u00ff" => "ÿ"
    );
    $replace = [
        '&lt;' => '', '&gt;' => '', '&#039;' => '', '&amp;' => '',
        '&quot;' => '', 'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'Ae',
        '&Auml;' => 'A', 'Å' => 'A', 'Ā' => 'A', 'Ą' => 'A', 'Ă' => 'A', 'Æ' => 'Ae',
        'Ç' => 'C', 'Ć' => 'C', 'Č' => 'C', 'Ĉ' => 'C', 'Ċ' => 'C', 'Ď' => 'D', 'Đ' => 'D',
        'Ð' => 'D', 'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E', 'Ē' => 'E',
        'Ę' => 'E', 'Ě' => 'E', 'Ĕ' => 'E', 'Ė' => 'E', 'Ĝ' => 'G', 'Ğ' => 'G',
        'Ġ' => 'G', 'Ģ' => 'G', 'Ĥ' => 'H', 'Ħ' => 'H', 'Ì' => 'I', 'Í' => 'I',
        'Î' => 'I', 'Ï' => 'I', 'Ī' => 'I', 'Ĩ' => 'I', 'Ĭ' => 'I', 'Į' => 'I',
        'İ' => 'I', 'Ĳ' => 'IJ', 'Ĵ' => 'J', 'Ķ' => 'K', 'Ł' => 'L', 'Ľ' => 'L',
        'Ĺ' => 'L', 'Ļ' => 'L', 'Ŀ' => 'L', 'Ñ' => 'N', 'Ń' => 'N', 'Ň' => 'N',
        'Ņ' => 'N', 'Ŋ' => 'N', 'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O',
        'Ö' => 'Oe', '&Ouml;' => 'Oe', 'Ø' => 'O', 'Ō' => 'O', 'Ő' => 'O', 'Ŏ' => 'O',
        'Œ' => 'OE', 'Ŕ' => 'R', 'Ř' => 'R', 'Ŗ' => 'R', 'Ś' => 'S', 'Š' => 'S',
        'Ş' => 'S', 'Ŝ' => 'S', 'Ș' => 'S', 'Ť' => 'T', 'Ţ' => 'T', 'Ŧ' => 'T',
        'Ț' => 'T', 'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'Ue', 'Ū' => 'U',
        '&Uuml;' => 'Ue', 'Ů' => 'U', 'Ű' => 'U', 'Ŭ' => 'U', 'Ũ' => 'U', 'Ų' => 'U',
        'Ŵ' => 'W', 'Ý' => 'Y', 'Ŷ' => 'Y', 'Ÿ' => 'Y', 'Ź' => 'Z', 'Ž' => 'Z',
        'Ż' => 'Z', 'Þ' => 'T', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ã' => 'a',
        'ä' => 'ae', '&auml;' => 'ae', 'å' => 'a', 'ā' => 'a', 'ą' => 'a', 'ă' => 'a',
        'æ' => 'ae', 'ç' => 'c', 'ć' => 'c', 'č' => 'c', 'ĉ' => 'c', 'ċ' => 'c',
        'ď' => 'd', 'đ' => 'd', 'ð' => 'd', 'è' => 'e', 'é' => 'e', 'ê' => 'e',
        'ë' => 'e', 'ē' => 'e', 'ę' => 'e', 'ě' => 'e', 'ĕ' => 'e', 'ė' => 'e',
        'ƒ' => 'f', 'ĝ' => 'g', 'ğ' => 'g', 'ġ' => 'g', 'ģ' => 'g', 'ĥ' => 'h',
        'ħ' => 'h', 'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ī' => 'i',
        'ĩ' => 'i', 'ĭ' => 'i', 'į' => 'i', 'ı' => 'i', 'ĳ' => 'ij', 'ĵ' => 'j',
        'ķ' => 'k', 'ĸ' => 'k', 'ł' => 'l', 'ľ' => 'l', 'ĺ' => 'l', 'ļ' => 'l',
        'ŀ' => 'l', 'ñ' => 'n', 'ń' => 'n', 'ň' => 'n', 'ņ' => 'n', 'ŉ' => 'n',
        'ŋ' => 'n', 'ò' => 'o', 'ó' => 'o', 'ô' => 'o', 'õ' => 'o', 'ö' => 'oe',
        '&ouml;' => 'oe', 'ø' => 'o', 'ō' => 'o', 'ő' => 'o', 'ŏ' => 'o', 'œ' => 'oe',
        'ŕ' => 'r', 'ř' => 'r', 'ŗ' => 'r', 'š' => 's', 'ù' => 'u', 'ú' => 'u',
        'û' => 'u', 'ü' => 'ue', 'ū' => 'u', '&uuml;' => 'ue', 'ů' => 'u', 'ű' => 'u',
        'ŭ' => 'u', 'ũ' => 'u', 'ų' => 'u', 'ŵ' => 'w', 'ý' => 'y', 'ÿ' => 'y',
        'ŷ' => 'y', 'ž' => 'z', 'ż' => 'z', 'ź' => 'z', 'þ' => 't', 'ß' => 'ss',
        'ſ' => 'ss', 'ый' => 'iy', 'А' => 'A', 'Б' => 'B', 'В' => 'V', 'Г' => 'G',
        'Д' => 'D', 'Е' => 'E', 'Ё' => 'YO', 'Ж' => 'ZH', 'З' => 'Z', 'И' => 'I',
        'Й' => 'Y', 'К' => 'K', 'Л' => 'L', 'М' => 'M', 'Н' => 'N', 'О' => 'O',
        'П' => 'P', 'Р' => 'R', 'С' => 'S', 'Т' => 'T', 'У' => 'U', 'Ф' => 'F',
        'Х' => 'H', 'Ц' => 'C', 'Ч' => 'CH', 'Ш' => 'SH', 'Щ' => 'SCH', 'Ъ' => '',
        'Ы' => 'Y', 'Ь' => '', 'Э' => 'E', 'Ю' => 'YU', 'Я' => 'YA', 'а' => 'a',
        'б' => 'b', 'в' => 'v', 'г' => 'g', 'д' => 'd', 'е' => 'e', 'ё' => 'yo',
        'ж' => 'zh', 'з' => 'z', 'и' => 'i', 'й' => 'y', 'к' => 'k', 'л' => 'l',
        'м' => 'm', 'н' => 'n', 'о' => 'o', 'п' => 'p', 'р' => 'r', 'с' => 's',
        'т' => 't', 'у' => 'u', 'ф' => 'f', 'х' => 'h', 'ц' => 'c', 'ч' => 'ch',
        'ш' => 'sh', 'щ' => 'sch', 'ъ' => '', 'ы' => 'y', 'ь' => '', 'э' => 'e',
        'ю' => 'yu', 'я' => 'ya'
    ];

    $string = str_replace(array_keys($replace), $replace, $string);

    return strtr($string, $utf8_ansi2);
}

foreach (Countries::all() as $country) {
    $countries[$country->get('cca2')] = [];

    $native_names = $country->get('name.native');
    if ($native_names !== null) {
        foreach ($native_names as $native) {
            foreach ($native as $key => $value) {
                $countries[$country->get('cca2')][] = clean_name($value);
            }
        }
    }

    foreach ($name_keys as $key) {
        if ($country->get($key) instanceof Collection) {
            foreach ($country->get($key) as $name) {
                if (strlen($name) > 1) {
                    $country_name = clean_name($name);
                    if (
                        !is_numeric($country_name) &&
                        strlen($country_name) > 2 &&
                        !in_array($country_name, $countries[$country->get('cca2')]) &&
                        mb_detect_encoding($country_name, 'UTF-8', true)
                    ) {
                        $countries[$country->get('cca2')][] = $country_name;
                    }
                }
            }
        } elseif ($country->get($key) !== null && strlen($country->get($key)) > 1) {
            $country_name = clean_name($country->get($key));
            if (
                !is_numeric($country_name) &&
                strlen($country_name) > 2 &&
                !in_array($country_name, $countries[$country->get('cca2')]) &&
                mb_detect_encoding($country_name, 'UTF-8', true)

            ) {
                $countries[$country->get('cca2')][] = $country_name;
            }
        } elseif (strpos($key, '*') !== false) {
            foreach ($country as $country_key => $country_value) {
                if (strpos($country_key, str_replace('*', '', $key)) !== false) {

                    if (is_string($country->get($country_key))) {
                        $country_name = clean_name($country->get($country_key));
                        if (
                            !is_numeric($country_name) &&
                            strlen($country_name) > 2 &&
                            !in_array($country_name, $countries[$country->get('cca2')]) &&
                            mb_detect_encoding($country_name, 'UTF-8', true)

                        ) {
                            $countries[$country->get('cca2')][] = $country_name;
                        }
                    }
                }
            }
        }
        // Translations
        if ($country->get('translations') !== null) {
            foreach ($country->get('translations') as $language => $translations) {
                $country_name = clean_name($translations->common);
                if (
                    !is_numeric($country_name) &&
                    strlen($country_name) > 2 &&
                    !in_array($country_name, $countries[$country->get('cca2')]) &&
                    mb_detect_encoding($country_name, 'UTF-8', true)

                ) {
                    $countries[$country->get('cca2')][] = $country_name;
                }
                $country_name = clean_name($translations->official);
                if (
                    !is_numeric($country_name) &&
                    strlen($country_name) > 2 &&
                    !in_array($country_name, $countries[$country->get('cca2')]) &&
                    mb_detect_encoding($country_name, 'UTF-8', true)

                ) {
                    $countries[$country->get('cca2')][] = $country_name;
                }
            }
        }
    }
}

file_put_contents(__DIR__ . '/../src/data/countries.json', json_encode($countries));
echo count($countries) . ' countries indexed and written to src/data/countries.json' . PHP_EOL;
