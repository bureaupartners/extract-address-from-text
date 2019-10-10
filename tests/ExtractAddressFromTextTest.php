<?php
declare (strict_types = 1);
namespace BureauPartners\ExtractAddressFromText\Tests;

require_once __DIR__ . '/../vendor/autoload.php';

use BureauPartners\ExtractAddressFromText;
use PHPUnit\Framework\TestCase;

final class ExtractAddressFromTextTest extends TestCase
{
    public function testExtractsStreetFromAddress(): void
    {
        $address   = 'Test';
        $extractor = new ExtractAddressFromText($address);
        $this->assertEquals(
            'Street',
            $extractor->getStreet()
        );
    }
}
