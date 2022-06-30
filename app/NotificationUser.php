<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationUser extends Model
{
    use HasFactory;

    const STATUS_ON = 'on';
    const STATUS_OFF = 'off';

    protected $fillable = [
        'userId',
        'status',
        'token',
    ];
}
