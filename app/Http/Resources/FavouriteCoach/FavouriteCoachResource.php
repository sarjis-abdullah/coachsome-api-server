<?php

namespace App\Http\Resources\FavouriteCoach;

use App\Entities\Badge;
use App\Entities\Currency;
use App\Entities\Review;
use App\Http\Resources\Badge\BadgeResource;
use App\Http\Resources\BaseResource;
use App\Services\Media\MediaService;
use App\Services\PackageService;
use App\Services\StorageService;
use Coachsome\BaseReview\Models\BaseReview;
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
                $rating = $this->overallStarRating($item);
                $countReview = $this->totalReviewer($item);
                // Badge
                $badge = new BadgeResource(Badge::find($item->badge_id));
                $mediaService = new MediaService();
                $images = $mediaService->getImages($item);
                if ($images['square']) {
                    $image = $images['square'];
                } else {
                    $image = $images['old'];
                }
                return [
                    'countReview' => $countReview,
                    'categories' => $item->categories,
                    'badge' => $badge,
                    'rating' => $rating,
                    'userName' => $item->user_name,
                    'name' => $item->full_name ?? $item->first_name . " " .$item->last_name,
                    'id' => $item->id,
                    'image' => $item->image ?? $image,
                    'price' =>  $item->ownPackageSetting
                        ? $packageService->calculateAmountByUserBasedCurrency($item->ownPackageSetting->hourly_rate, $mCurrency->getUserBasedCurrency($item), $requestedCurrency)
                        : 0.00,
                ];
            }),
        ];
    }

    public function totalReviewer($user)
    {
        $totalReviewer = 0;

        // Facebook  review rating count
        $review = Review::where("user_id", $user->id)->first();
        if ($review) {
            $totalReviewer += $review->rating_count;
        }

        // Base platform review rating count
        $baseReviews = BaseReview::where('user_id', '=', $user->id)->get();
        if ($baseReviews->count()) {
            $totalReviewer += $baseReviews->count();
        }

        return $totalReviewer;
    }

    public function overallStarRating($user)
    {
        $rating = 0;
        $overallStarRating = 0;
        $reviewPart = 0;


        // Facebook part review rating count
        $review = Review::where("user_id", $user->id)->first();
        if ($review) {
            $overallStarRating += $review->overall_star_rating;
            if($review->overall_star_rating){
                $reviewPart++;
            }
        }


        // Base platform part review rating count
        $baseReviews = BaseReview::where('user_id', '=', $user->id)->get();
        if ($baseReviews->count()) {
            $reviewPart++;
            $overallStarRating += $baseReviews->sum("rating") / $baseReviews->count();
        }

        // Avg count from two part
        $rating = $reviewPart ? $overallStarRating / $reviewPart : 0;

        return round($rating, 2);
    }
}
