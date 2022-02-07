<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OTP extends Model
{
    use HasFactory;
    protected $table = "otp_verifications";

    protected $fillable = [
        'email',
        'otp',
        'type'
    ];
}
