<?php

namespace App\Http\Resources\FavouriteCoach;

use App\Entities\Currency;
use App\Http\Resources\BaseResource;
use App\Services\PackageService;
use App\Services\StorageService;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Http\Request;

class FavouriteCoachResource extends BaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     */
    public function toArray($request): array
    {

        return [
            'userId' => $this->userId,
            'coachId' => $this->coachId,
            'isFavourite' => $this->isFavourite,
            'coach' => $this->when($this->needToInclude($request, 'f.c'), function () {
                $packageService = new PackageService();
                $mCurrency = new Currency();
                $storageService = new StorageService();
                $requestedCurrencyCode = \request()->header('Currency-Code') ?? "DKK";
                $requestedCurrency = $mCurrency->getByCode($requestedCurrencyCode);
                $item = $this->coach;

                $profile = $item->profile;
                $item->image = null;
                if ($profile) {
                    $item->image = $storageService->hasImage($profile->image) ? $profile->image : '';
                }

                $faceBookReview = $item->reviews->where('provider', 'facebook')->first();
                $rating = $faceBookReview
                    ? $faceBookReview->overall_star_rating
                    : 0;
                $countReview = $faceBookReview ? $faceBookReview->rating_count : 0;
                return [
                    'countReview' => $countReview,
                    'rating' => $rating,
                    'userName' => $item->user_name,
                    'name' => $item->full_name ?? $item->first_name . " " .$item->lastst_name,
                    'id' => $item->id,
                    'image' => $item->image,
                    'price' =>  $item->ownPackageSetting
                        ? $packageService->calculateAmountByUserBasedCurrency($item->ownPackageSetting->hourly_rate, $mCurrency->getUserBasedCurrency($item), $requestedCurrency)
                        : 0.00,
                ];
            }),
        ];
    }
}
