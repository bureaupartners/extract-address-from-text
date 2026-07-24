#!/usr/bin/env php
<?php

/**
 * Generates src/data/riskkix.json from src/riskkix/riskkix.txt.
 *
 * riskkix.txt is tab separated (POSTCODE<TAB>value, CRLF line endings) and lists,
 * per postcode, the number(s) that are part of the street name and must NOT be
 * read as a house number. A value can hold multiple numbers separated by "&" and
 * "/", e.g. "1940/40&1945/45" (street "Plein 1940-1945") flattens to
 * [1940, 40, 1945, 45].
 *
 * Output: {"POSTCODE": ["number", ...]} with postcodes normalised to uppercase
 * without whitespace and the numbers deduplicated per postcode (union across
 * duplicate rows).
 */

$source = __DIR__ . '/../src/riskkix/riskkix.txt';
$target = __DIR__ . '/../src/data/riskkix.json';

$lines   = file($source, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$riskkix = [];

foreach ($lines as $line) {
    $line  = trim($line);
    $parts = explode("\t", $line);
    if (count($parts) < 2) {
        continue;
    }

    $postalcode = strtoupper(preg_replace('/\s+/', '', $parts[0]));
    // Skip anything that is not a valid Dutch postalcode (e.g. the corrupt header row).
    if (!preg_match('/^[1-9]\d{3}[A-Z]{2}$/', $postalcode)) {
        continue;
    }

    $numbers = preg_split('#[&/]#', trim($parts[1]));
    foreach ($numbers as $number) {
        $number = trim($number);
        if ($number === '' || !ctype_digit($number)) {
            continue;
        }
        if (!isset($riskkix[$postalcode])) {
            $riskkix[$postalcode] = [];
        }
        if (!in_array($number, $riskkix[$postalcode], true)) {
            $riskkix[$postalcode][] = $number;
        }
    }
}

file_put_contents($target, json_encode($riskkix));
echo count($riskkix) . ' riskkix postalcodes indexed and written to src/data/riskkix.json' . PHP_EOL;
