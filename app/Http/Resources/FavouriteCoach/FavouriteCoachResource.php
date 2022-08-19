<?php

namespace App\Http\Resources\FavouriteCoach;

use App\Entities\Badge;
use App\Entities\Currency;
use App\Entities\Review;
use App\Http\Resources\Badge\BadgeResource;
use App\Http\Resources\BaseResource;
use App\Services\CurrencyService;
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
        $mediaService = new MediaService();
        $images = $mediaService->getImages($this->coach);
        if ($images['square']) {
            $image = $images['square'];
        } else if ($images['original']) {
            $image = $images['original'];
        }else if ($images['landscape']) {
            $image = $images['landscape'];
        }else if ($images['portrait']) {
            $image = $images['portrait'];
        }else {
            $image = $images['old'];
        }
        return [
            'userId' => $this->userId,
            'coachId' => $this->coachId,
            'isFavourite' => $this->isFavourite,
            'coach' => $this->when($this->needToInclude($request, 'f.c'), function () use ($image) {
                $packageService = new PackageService();
                $mCurrency = new Currency();
                $storageService = new StorageService();
                $currencyService = new CurrencyService();
                $requestedCurrencyCode = \request()->header('Currency-Code') ?? "DKK";
                $requestedCurrency = $mCurrency->getByCode($requestedCurrencyCode);
                $item = $this->coach;
                $item->categories = $this->coach->generalSportCategories;

                // Review
                $rating = $this->overallStarRating($item);
                $countReview = $this->totalReviewer($item);
                // Badge
                $badge = new BadgeResource(Badge::find($item->badge_id));

                // Find minimum price package
                $price = null;
                foreach ($item->packages->where('status', 1) as $package) {
                    $originalPrice = $packageService->calculateOriginalPrice($item, $package);
                    if ($price) {
                        if ($price > $originalPrice) {
                            $price = $originalPrice;
                        }
                    } else {
                        $price = $originalPrice;
                    }
                }

                // Price
                $price = $currencyService->convert(
                    $price,
                    $currencyService->getDefaultBasedCurrency()->code,
                    $requestedCurrency->code
                );
                return [
                    'countReview' => $countReview,
                    'categories' => $item->categories,
                    'badge' => $badge,
                    'rating' => $rating,
                    'userName' => $item->user_name,
                    'name' => $item->full_name ?? $item->first_name . " " .$item->last_name,
                    'id' => $item->id,
                    'image' => $image,
                    'price' =>  $price
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
