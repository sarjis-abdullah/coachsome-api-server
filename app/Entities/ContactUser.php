<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContactUser extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_ACTIVE = "active";
    const STATUS_PENDING = "pending";

    protected $fillable = [
        "categoryName",
        "firstName",
        "lastName",
        "email",
        "status",
        "comment",
        "receiverUserId",
        "contactAbleUserId",
        "token",
        "lastActiveAt",
    ];
}
