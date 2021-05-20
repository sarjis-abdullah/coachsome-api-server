<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Translation extends Model
{
    protected $table = "translations";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'status',
        'locale',
        'group',
        'page_name',
        'gl_key',
        'en_value',
        'dn_value',
        'sv_value'
    ];


}
