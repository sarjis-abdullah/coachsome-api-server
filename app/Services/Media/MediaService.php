<?php


namespace App\Services\Media;


use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class MediaService
{

    /*
     * All image extension should have to same type
     * All image should have base64 encoding
     *
     */
    public function storeImage($user, $images)
    {
        $profile = $user->profile;
        if ($profile) {
            $this->destroyImages($profile->image);
        }

        $imageTitle = 'id_' . $user->id . '_' . time();

        // Store original image
        if (array_key_exists('original', $images) && $images['original']) {
            $image_64 = $images['original'];
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageName = $imageTitle . '.' . $extension;
            Storage::disk('publicImage')->put("original/" . $imageName, base64_decode($image));
        }

        // Store square image
        if (array_key_exists('square', $images) && $images['square']) {
            $image_64 = $images['square'];
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageName = $imageTitle . '.' . $extension;
            Storage::disk('publicImage')->put("square/" . $imageName, base64_decode($image));
        }

        // Store medium image
        if (array_key_exists('portrait', $images) && $images['portrait']) {
            $image_64 = $images['portrait'];
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageName = $imageTitle . '.' . $extension;
            Storage::disk('publicImage')->put("portrait/" . $imageName, base64_decode($image));
        }

        // Store landscape image
        if (array_key_exists('landscape', $images) && $images['landscape']) {
            $image_64 = $images['landscape'];
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageName = $imageTitle . '.' . $extension;
            Storage::disk('publicImage')->put("landscape/" . $imageName, base64_decode($image));
        }

        // Store tiny image
        if (array_key_exists('tiny', $images) && $images['tiny']) {
            $image_64 = $images['tiny'];
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageName = $imageTitle . '.' . $extension;
            Storage::disk('publicImage')->put("tiny/" . $imageName, base64_decode($image));
        }

        // Save image name
        $profile->image = $imageName;
        $profile->save();
    }

    public function storeFacebookImage($name, $content)
    {
        return Storage::disk('publicImage')->put("facebook/" . $name, $content);
    }

    public function destroyFacebookImage($name)
    {
        return Storage::disk('publicImage')->delete('facebook/' . $name);
    }

    public function getFacebookImageUrl($name)
    {
        return env('APP_SERVER_DOMAIN_STORAGE_PATH') . '/images/facebook/' . $name;
    }

    public function getGalleryImageUrl($name)
    {
        return env('APP_SERVER_DOMAIN_STORAGE_PATH') . '/images/' . $name;
    }

    private function destroyImages($imageName)
    {
        if ($imageName) {
            if (Storage::disk('publicImage')->has('original/' . $imageName)) {
                Storage::disk('publicImage')->delete('original/' . $imageName);
            }

            if (Storage::disk('publicImage')->has('square/' . $imageName)) {
                Storage::disk('publicImage')->delete('square/' . $imageName);
            }

            if (Storage::disk('publicImage')->has('portrait/' . $imageName)) {
                Storage::disk('publicImage')->delete('portrait/' . $imageName);
            }

            if (Storage::disk('publicImage')->has('landscape/' . $imageName)) {
                Storage::disk('publicImage')->delete('landscape/' . $imageName);
            }

            if (Storage::disk('publicImage')->has($imageName)) {
                Storage::disk('publicImage')->delete($imageName);
            }
        }
    }

    public function destroyAll($user)
    {
        $profile = $user->profile;
        if ($profile) {
            $this->destroyImages($profile->image);
        }
    }

    public function getImages($user)
    {
        $images = [
            'old' => '',
            'original' => '',
            'square' => '',
            'portrait' => '',
            'landscape' => '',
        ];
        $profile = $user->profile;
        if ($profile) {
            if ($profile->image) {
                if (Storage::disk('publicImage')->has('original/' . $profile->image)) {
                    $images['original'] = route('images', ["size" => 'original', "filename" => $profile->image]);
                }

                if (Storage::disk('publicImage')->has('square/' . $profile->image)) {
                    $images['square'] = env('APP_SERVER_DOMAIN_STORAGE_PATH') . '/images/square/' . $profile->image;
                }

                if (Storage::disk('publicImage')->has('portrait/' . $profile->image)) {
                    $images['portrait'] = env('APP_SERVER_DOMAIN_STORAGE_PATH') . '/images/portrait/' . $profile->image;
                }

                if (Storage::disk('publicImage')->has('landscape/' . $profile->image)) {
                    $images['landscape'] = env('APP_SERVER_DOMAIN_STORAGE_PATH') . '/images/landscape/' . $profile->image;
                }

                if (Storage::disk('publicImage')->has($profile->image)) {
                    $images['old'] = env('APP_SERVER_DOMAIN_STORAGE_PATH') . '/images/' . $profile->image;
                }
            }

        }
        return $images;
    }

}
