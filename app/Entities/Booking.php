<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $table = 'bookings';

    public function order()
    {
        return $this->hasOne(Order::class, 'booking_id');
    }

    public function bookingTimes()
    {
        return $this->hasMany(BookingTime::class, 'booking_id');
    }

    public function packageOwnerUser()
    {
        return $this->belongsTo(User::class, 'package_owner_user_id');
    }

    public function packageBuyerUser()
    {
        return $this->belongsTo(User::class, 'package_buyer_user_id');
    }
}
