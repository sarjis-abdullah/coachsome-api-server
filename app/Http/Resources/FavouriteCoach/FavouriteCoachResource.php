<?php

namespace App\Http\Resources\FavouriteCoach;

use App\Entities\Badge;
use App\Entities\Currency;
use App\Http\Resources\Badge\BadgeResource;
use App\Http\Resources\BaseResource;
use App\Services\PackageService;
use App\Services\Review\ReviewService;
use App\Services\StorageService;
use Coachsome\BaseReview\Repositories\BaseReviewRepository;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Foundation\Application;
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
                $item->categories = $this->coach->generalSportCategories;

                $profile = $item->profile;
                $item->image = null;
                if ($profile) {
                    $item->image = $storageService->hasImage($profile->image) ? $profile->image : '';
                }

                // Review
                $faceBookReview = $item->reviews->where('provider','=', 'facebook')->first();
                $rating = $faceBookReview
                    ? $faceBookReview->overall_star_rating
                    : 0;
                $countReview = $faceBookReview ? $faceBookReview->rating_count : 0;
                // Badge
                $badge = new BadgeResource(Badge::find($item->badge_id));
                return [
                    'countReview' => $countReview,
                    'categories' => $item->categories,
                    'reviews' => $this->coach->reviews,
                    'badge' => $badge,
                    'rating' => $rating,
                    'userName' => $item->user_name,
                    'name' => $item->full_name ?? $item->first_name . " " .$item->last_name,
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
