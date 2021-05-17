<?php

namespace App\Entities;

use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    public function details()
    {
        return $this->hasOne(PackageDetail::class, 'package_id');
    }

    public function category()
    {
        return $this->belongsTo(PackageCategory::class, 'package_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
