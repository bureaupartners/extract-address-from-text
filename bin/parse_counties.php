#!/usr/bin/env php
<?php

use PragmaRX\Countries\Package\Countries;
use PragmaRX\Countries\Package\Support\Collection;

include_once __DIR__ . '/../vendor/autoload.php';

$name_keys = ['name.common', 'name.official', 'alt_spellings', 'name_*'];
$countries = [];

foreach (Countries::all() as $country) {
    foreach ($name_keys as $key) {
        if ($country->get($key) instanceof Collection) {
            foreach ($country->get($key) as $name) {
                if (strlen($name) > 1) {
                    $countries[$country->get('cca2')][] = mb_strtolower($name);
                }
            }
        } elseif (strlen($country->get($key)) > 1) {
            $countries[$country->get('cca2')][] = mb_strtolower($country->get($key));
        } elseif (strpos($key, '*') !== false) {
            foreach ($country as $country_key => $country_value) {
                if (strpos($country_key, str_replace('*', '', $key)) !== false) {
                    if (is_string($country->get($country_key))) {
                        $countries[$country->get('cca2')][] = mb_strtolower($country->get($country_key));
                    }
                }
            }
        }
    }
}

file_put_contents(__DIR__ . '/../src/data/countries.json', json_encode($countries));
echo count($countries) . ' countries indexed and written to src/data/countries.json' . PHP_EOL;
