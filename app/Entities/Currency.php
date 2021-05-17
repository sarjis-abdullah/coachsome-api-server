<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    public function getByCode($code){
        return $this->where('code', $code)->first();
    }

    public function getUserBasedCurrency($user)
    {
        // if settings found any currency then return
        // if not found then default based currency is user based currency
        return $this->getDefaultBasedCurrency();
    }

    public function getDefaultBasedCurrency()
    {
        return $this->where('is_def_based_currency', 1)->first();
    }
}
