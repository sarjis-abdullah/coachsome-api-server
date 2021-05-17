<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayoutInformation extends Model
{
    use HasFactory;
    protected $table = "payout_information";
}
