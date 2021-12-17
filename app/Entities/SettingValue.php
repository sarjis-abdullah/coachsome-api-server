<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SettingValue extends Model
{
    use HasFactory;

    protected $table = "setting_values";
}
