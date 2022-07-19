<?php

namespace App\Entities;

use App\Scopes\RoleScope;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Event;

class Contact extends Model
{
    protected static function booted()
    {
        // static::addGlobalScope(new RoleScope);
    }
}
