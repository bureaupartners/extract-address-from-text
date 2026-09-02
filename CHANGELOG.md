# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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