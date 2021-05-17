<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class PendingBooking extends Model
{
    protected $table = "pending_bookings";

    public function packageOwnerUser()
    {
        return $this->belongsTo(User::class, 'package_owner_user_id');
    }

    public function customerUser()
    {
        return $this->belongsTo(User::class, 'customer_user_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }
}
