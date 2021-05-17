<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class SportCategory extends Model
{
    protected $table='sport_categories';

    /**
     * Get the name.
     *
     * @param  string  $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        return lcfirst($value);
    }
}
