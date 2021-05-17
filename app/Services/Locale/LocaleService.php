<?php


namespace App\Services\Locale;


use App\Entities\UserSetting;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use PeterColes\Countries\CountriesFacade;
use PragmaRX\Countries\Package\Countries;

class LocaleService
{
    public function currentLocale()
    {
        return App::currentLocale();
    }

    public function currentCountryCode()
    {
        return config('app.supported_languages')[$this->currentLocale()]['country_code'];
    }

    public function currentTimezone($countryCode = null)
    {
        $countries = new Countries();
        return $countries->where('cca2', $countryCode ?? $this->currentCountryCode())->first()->hydrate('timezones')->timezones->first()->zone_name;
    }

    public function getUserCountryCodeFromSetting($user)
    {
        $cca2 = null;
        if($user){
            $setting = UserSetting::where('user_id',$user->id)->first();
            if($setting){
                $cca2= $setting->cca2;
            }
        }
        return $cca2;
    }

    public function countryList($locale)
    {
        return json_decode(CountriesFacade::lookup($locale),true);
    }

    public function countryNameByCountryCode($countryCode, $locale = null)
    {
        $searchedCountryName = '';
        $countryList = $this->countryList($locale ?? $this->currentLocale());
        foreach ($countryList as $key=>$item) {
            if($countryCode == $key){
                $searchedCountryName = $item;
                break;
            }
        }
        return $searchedCountryName;
    }

}
