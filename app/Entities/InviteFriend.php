<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InviteFriend extends Model
{
    use HasFactory, SoftDeletes;

    const STATUS_TYPE_REQUESTED = 'INVITATION_REQUESTED';
    const STATUS_TYPE_ACCEPTED = 'INVITATION_ACCEPTED';

    protected $fillable = [
        'token',
        'email',
        'status',
        'invitedByUserId',
    ];

    /**
     * get constants of a model by prefix
     *
     * @param $prefix
     * @return array
     */
    public static function getConstantsByPrefix($prefix): array
    {
        $reflectionClass = new \ReflectionClass(self::class);

        $constants = array_filter($reflectionClass->getConstants(), function ($constant) use ($prefix) {
            return strpos($constant, $prefix) === 0;
        }, ARRAY_FILTER_USE_KEY);

        return array_values($constants);
    }
}
