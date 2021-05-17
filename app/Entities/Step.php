<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Step extends Model
{
    protected $table = 'steps';

    public static  function getByKey($key){
        return static::where('key', $key)->first();
    }
}
