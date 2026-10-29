# Extract address from text
With this package you can extract the address from a unformatted text string

## Installation

Install composer packages

```bash
composer require bureaupartners/extract-address-from-text
```

## Usage

```php
use BureauPartners\ExtractAddressFromText\AddressExtractor;

$extractor = new AddressExtractor($address['text']);

$extractor->getAddress(); // Get all information

$extractor->getRecipient();
$extractor->getStreet();
$extractor->getHouseNumber();
$extractor->getHouseNumberAddition();
$extractor->getPostalCode();
$extractor->getCity();
$extractor->getCountry()['code'];
```

The second argument is the country to assume when the address has no country
line (default `NL`):

```php
$extractor = new AddressExtractor($address['text'], 'BE');
```

### Countries

Postcodes are recognized for every European country. The country comes from,
in this order:

1. the country line, in most languages and also as an ISO code on the last line;
2. a postcode of the assumed country;
3. a country written behind the city ("1010 Wien, Austria");
4. a postcode that can only belong to one country ("SW7 5DP", "00-468",
   "D-10117").

When none of these apply, `getCountry()['code']` is an empty string.

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
See LICENSE.md