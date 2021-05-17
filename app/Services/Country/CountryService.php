<?php


namespace App\Services\Country;


use PeterColes\Countries\CountriesFacade;

class CountryService
{
    public function getCountryList($locale)
    {
        return json_decode(CountriesFacade::lookup($locale), true);
    }
}
