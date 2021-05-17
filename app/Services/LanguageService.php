<?php


namespace App\Services;


use App\Entities\Currency;
use App\Entities\Language;

class LanguageService
{
    public function getUserLanguageByRequestHeader($request)
    {
        $language = null;
        $mLanguage = new Language();
        $requestedLanguageCode = $request->header('Language-Code');
        $requestedLanguage = $mLanguage->getByTranslationKey($requestedLanguageCode);
        if ($requestedLanguage) {
            $language = $requestedLanguage;
        } else {
            $language = $mLanguage->getDefaultBasedCurrency();
        }
        return $language;
    }
}
