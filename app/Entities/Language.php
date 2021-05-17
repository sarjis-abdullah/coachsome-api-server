<?php

namespace App\Entities;

use App\Data\Constants;
use Illuminate\Database\Eloquent\Model;

class Language extends Model
{
    public function getByTranslationKey($tKey)
    {
        return $this->where('t_key', $tKey)->first();
    }

    public function getDefaultLanguage()
    {
        return $this->where('t_key', Constants::LANGUAGE_KEY_DENMARK)->first();

    }
}
