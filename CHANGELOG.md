# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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