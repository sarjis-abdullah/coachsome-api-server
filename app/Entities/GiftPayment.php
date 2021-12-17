<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GiftPayment extends Model
{
    use HasFactory;
    protected $table = "gift_payments";
}
