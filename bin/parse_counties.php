#!/usr/bin/env php
<?php

use PragmaRX\Countries\Package\Countries;
use PragmaRX\Countries\Package\Support\Collection;

include_once __DIR__ . '/../vendor/autoload.php';

$name_keys = ['name.common', 'name.official', 'alt_spellings', 'name_*'];
$countries = [];

function decode_encoded_utf8($string){
        return preg_replace_callback('#\\\\u([0-9a-f]{4})#ism', function($matches) { return mb_convert_encoding(pack("H*", $matches[1]), "UTF-8", "UCS-2BE"); }, $string);
}

function utf8_to_ansi($string)
{
    $utf8_ansi2 = array(
    "\u00c0" =>"À",
    "\u00c1" =>"Á",
    "\u00c2" =>"Â",
    "\u00c3" =>"Ã",
    "\u00c4" =>"Ä",
    "\u00c5" =>"Å",
    "\u00c6" =>"Æ",
    "\u00c7" =>"Ç",
    "\u00c8" =>"È",
    "\u00c9" =>"É",
    "\u00ca" =>"Ê",
    "\u00cb" =>"Ë",
    "\u00cc" =>"Ì",
    "\u00cd" =>"Í",
    "\u00ce" =>"Î",
    "\u00cf" =>"Ï",
    "\u00d1" =>"Ñ",
    "\u00d2" =>"Ò",
    "\u00d3" =>"Ó",
    "\u00d4" =>"Ô",
    "\u00d5" =>"Õ",
    "\u00d6" =>"Ö",
    "\u00d8" =>"Ø",
    "\u00d9" =>"Ù",
    "\u00da" =>"Ú",
    "\u00db" =>"Û",
    "\u00dc" =>"Ü",
    "\u00dd" =>"Ý",
    "\u00df" =>"ß",
    "\u00e0" =>"à",
    "\u00e1" =>"á",
    "\u00e2" =>"â",
    "\u00e3" =>"ã",
    "\u00e4" =>"ä",
    "\u00e5" =>"å",
    "\u00e6" =>"æ",
    "\u00e7" =>"ç",
    "\u00e8" =>"è",
    "\u00e9" =>"é",
    "\u00ea" =>"ê",
    "\u00eb" =>"ë",
    "\u00ec" =>"ì",
    "\u00ed" =>"í",
    "\u00ee" =>"î",
    "\u00ef" =>"ï",
    "\u00f0" =>"ð",
    "\u00f1" =>"ñ",
    "\u00f2" =>"ò",
    "\u00f3" =>"ó",
    "\u00f4" =>"ô",
    "\u00f5" =>"õ",
    "\u00f6" =>"ö",
    "\u00f8" =>"ø",
    "\u00f9" =>"ù",
    "\u00fa" =>"ú",
    "\u00fb" =>"û",
    "\u00fc" =>"ü",
    "\u00fd" =>"ý",
    "\u00ff" =>"ÿ");

    return strtr($string, $utf8_ansi2);
}

function clean_name($string)
{
    $string = utf8_to_ansi($string);
    $string = iconv("UTF-8", "ISO-8859-1//TRANSLIT//IGNORE", decode_encoded_utf8($string));
    $string = preg_replace('/\pM*/u', '', normalizer_normalize($string, Normalizer::FORM_D));
    $string = trim(mb_strtolower($string));
    return $string;
}

foreach (Countries::all() as $country) {
    $countries[$country->get('cca2')] = [];

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
