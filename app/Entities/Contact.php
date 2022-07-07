<?php

namespace App\Entities;

use App\Scopes\RoleScope;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new RoleScope);
    }
}
