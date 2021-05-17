<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class BookingTime extends Model
{
    public function location()
    {
        return $this->hasOne(BookingLocation::class, 'booking_time_id');
    }

    public function requesterUser()
    {
        return $this->belongsTo(User::class, 'requester_user_id');
    }

    public function requesterToUser()
    {
        return $this->belongsTo(User::class, 'requester_to_user_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
