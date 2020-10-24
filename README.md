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

## Contributing
Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

Please make sure to update tests as appropriate.

## License
See LICENSE.md