<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';

    public static function getByKey($key)
    {
        return static::where('key', $key)->first();
    }

    public function steps()
    {
        return $this->hasMany(Step::class, 'page_id');
    }
}
