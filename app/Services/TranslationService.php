<?php


namespace App\Services;


use App\Data\Constants;
use App\Entities\Translation;

class TranslationService
{
    public function getKeyByLanguageCode($code)
    {
        $key = [];
        if(Constants::LANGUAGE_USA_CODE == $code){
            $key = Translation::pluck( 'en_value', 'gl_key')->toArray();
        } else if(Constants::LANGUAGE_DENAMARK_CODE == $code){
            $key = Translation::pluck( 'dn_value', 'gl_key')->toArray();
        } else if(Constants::LANGUAGE_SWEDISH_CODE == $code) {
            $key = Translation::pluck( 'sv_value', 'gl_key')->toArray();
        } else {
            $key = Translation::pluck( 'en_value', 'gl_key')->toArray();
        }

        return $key;
    }
}
