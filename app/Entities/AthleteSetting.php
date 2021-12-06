<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AthleteSetting extends Model
{
    use HasFactory;

    protected $table = "athlete_settings";

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 
        'inbox_message', 
        'order_message', 
        'order_update', 
        'booking_request', 
        'booking_change', 
        'account', 
        'marketting'
    ];
}
