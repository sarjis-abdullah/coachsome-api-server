<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class PayoutRequest extends Model
{
    protected $table='payout_requests';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
