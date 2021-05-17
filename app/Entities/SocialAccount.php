<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class SocialAccount extends Model
{
    protected $table = 'social_accounts';
    protected $fillable = ['user_id', 'provider_name', 'provider_id' ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
