<?php

namespace App;

use App\Entities\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FavouriteCoach extends Model
{
    use HasFactory;

    protected $fillable = [
        'coachId',
        'userId',
        'isFavourite',
    ];

    function coach(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'userId');
    }
}
