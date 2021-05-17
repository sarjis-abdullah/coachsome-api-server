<?php

namespace App\Entities;

use App\Services\Media\MediaService;
use App\Services\StorageService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Profile extends Model
{

    /**
     * Get the user that owns the phone.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getImage()
    {
        $storageService = new  StorageService();
        return $storageService->hasImage($this->image) ? $this->image : null;
    }

    public function squareImage()
    {
        $mediaService  = new MediaService();
        $images = $mediaService->getImages($this->user);
        return $images['square'] ? $images['square'] : $images['old'];
    }

    public function avatarName()
    {
        $name = '';
        if ($this->profile_name) {
            $nameArray = explode(" ", $this->profile_name);

            if (array_key_exists(0, $nameArray)) {
                $name .= htmlentities(substr(strtoupper($nameArray[0]), 0, 1));
            }

            if (array_key_exists(1, $nameArray)) {
                $name .= htmlentities(substr(strtoupper($nameArray[1]), 0, 1));
            }
        }

        return $name;
    }
}
