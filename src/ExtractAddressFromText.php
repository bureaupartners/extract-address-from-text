<?php

namespace BureauPartners;

class ExtractAddressFromText
{
    private $recipient             = [];
    private $street                = null;
    private $house_number          = null;
    private $house_number_addition = null;
    private $city                  = null;
    private $country               = null;

    public function __construct($text)
    {

    }

    public function getStreet()
    {
        return 'Street';
    }

    public function getCity()
    {

    }

    public function getCountry()
    {

    }

}
