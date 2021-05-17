<?php


namespace App\Services;


use App\Data\Constants;
use Illuminate\Support\Facades\Storage;

class StorageService
{
    private const IMAGE_DISK = Constants::DISK_NAME_PUBLIC_IMAGE;

    public function hasImage($image)
    {
        return Storage::disk(self::IMAGE_DISK)->has($image);
    }

    public static function exist($image)
    {
        return Storage::disk(self::IMAGE_DISK)->has($image);
    }

    public function destroyImage($image)
    {
        return Storage::disk(self::IMAGE_DISK)->delete($image);;
    }

    public function putImage($fileName, $fileData)
    {
        return Storage::disk(self::IMAGE_DISK)->put($fileName, base64_decode($fileData));
    }
}
