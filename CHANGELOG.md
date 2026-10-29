# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [3.0.0] - 2026-10-29

### Changed
- **Breaking:** `getCountry()['code']` is empty when the address does not
  tell the country: there is no country line, and no postcode of the default
  country or of a country whose postcode cannot be mistaken for another's. It
  used to return the default country (`NL`), so a foreign address whose
  country line was missed came out as Dutch.
- The country line is searched from the bottom up. Names of three letters or
  fewer ("IT", "MAN") only count on the last line, so an IT department or a
  company called MAN is no longer taken for a country.

### Added
- Postcode formats for every European country: AD, AL, AT, AX, BA, BE, BG,
  BY, CH, CY, CZ, DE, DK, EE, ES, FI, FO, FR, GB, GG, GI, GR, HR, HU, IE, IM,
  IS, IT, JE, LI, LT, LU, LV, MC, MD, ME, MK, MT, NL, NO, PL, PT, RO, RS, RU,
  SE, SI, SK, SM, TR, UA, VA and XK. The postcode may come before the city,
  after it where the country writes it that way ("LONDON SW7 5DP",
  "Rīga, LV-1050"), or on a line of its own with the city on the line above.
- A country prefix on the postcode ("D-10117", "CH-3003", "LV-1050") is read
  for every country.
- Without a country line, the country is taken from behind the city ("1010
  Wien, Austria") or from a postcode that can only be one country's: UK and
  the Crown Dependencies, Gibraltar, Ireland, Poland, Portugal, Andorra, or any
  postcode with a country prefix.
- Country names that were missing from `countries.json`: UK, England,
  Scotland, Wales, Northern Ireland, Groot-Brittannië, Großbritannien,
  Faeröer, Heilige Stoel, and the Greek and Cyrillic names of the countries
  that use those scripts. They are listed in `bin/parse_counties.php`, which
  adds them when building the file; `php bin/parse_counties.php
  --aliases-only` adds them to the existing file without rebuilding it.
- House number before the street ("38 Hyde Park Gate", "7 bis, rue Eblé") for
  FR, GB, GG, GI, IE, IM, JE, LU, MC and MT. The street first still works.
  In these countries a street without a house number ("Halkett Place") is
  the line above the city.
- A marked house number ("Bd. Dimitrie Pompeiu nr. 5-7", "647 Sokak No. 10")
  is the house number, whatever other numbers the line holds.
- A line with a floor, unit or district ("etaj 8", "Sector 2", "planta 36",
  "2nd floor") is never the street.
- Hungarian addresses with the city above the street ("Budapest / Kapás u.
  6-12. / 1027").
- A country prefix followed by a space ("MC 98000").
- `tests/CountryDetectionTest.php` and `tests/EuropeanAddressTest.php`, with an
  address per country.

### Fixed
- Lines are trimmed and non-breaking spaces normalized before the country is
  looked up: "AUSTRIA " with a trailing space was not recognized.
- A Dutch postcode with a space before the letters must be followed by a whole
  word: "1040 Brussel" was read as postcode "1040 BR" in "ussel".
- The British postcode was only recognized without the space between the
  outward and inward code, and only after the city.
- "Frankrijk" and other names of France came out as `EU`, an entry in
  `countries.json` that is not a country but shares France's names. It is
  removed from the file and left out by `bin/parse_counties.php`.
- A city with letters outside ASCII ("Zürich", "København Ø") was cut off at
  the first such letter.
- Punctuation around the house number addition (", block 1A", "-12.") and a
  comma after the street ("Avenida Infante Santo,") are left out.
- `countries.json` is read once per process instead of twice per address.
- The getters no longer raise deprecation notices for an address of fewer than
  three lines.

## [2.1.0] - 2026-09-02

### Fixed
- The recipient is no longer decided while looping over the lines, but after the
  street line has been determined. A name that contains a number ("XYZ P20
  B.V.") used to be read as a street ("XYZ P", house number 20), after which
  no recipient line was collected at all and `getRecipient()` returned an empty
  array. The street and postal code were still resolved correctly, so only the
  addressee was lost.

### Changed
- `getRecipient()` now returns every line above the street line, so an address
  detail between the name and the street (e.g. "Torre Espacio - Verdieping 36")
  is returned as well. Consumers are expected to score the lines they get.
- Lines without a single letter (barcodes, sequence numbers) are never returned
  as a recipient line.

### Added
- `tests/AddressTest.php` now asserts `getRecipient()` as well; the expectations
  in the test table were never checked before and two of them were wrong.

## [2.0.0] - 2026-07-24

### Added
- Riskkix support for Dutch addresses: street names ending in a number (e.g.
  "Punter 34", "Plein 1940-1945") are kept intact instead of the trailing number
  being read as the house number, based on per-postalcode data.
- `bin/parse_riskkix.php` generator that builds `src/data/riskkix.json` from
  `src/riskkix/riskkix.txt`.

### Changed
- Updated development dependencies: PHPUnit 8 → 13 and pragmarx/countries 0.8 → 1.0
  (added illuminate/support, required by the countries generator).
- Migrated `phpunit.xml.dist` to the PHPUnit 10+ configuration schema.