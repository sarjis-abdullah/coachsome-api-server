<?php


namespace App\Services\Review;


use App\Entities\Review;
use App\Entities\User;
use App\Services\Media\MediaService;
use Coachsome\BaseReview\Repositories\BaseReviewRepository;
use Illuminate\Support\Facades\Log;

class ReviewService
{
    protected $baseReviewRepository;

    public function __construct(BaseReviewRepository $baseReviewRepository)
    {
        $this->baseReviewRepository = $baseReviewRepository;
    }


    public function info()
    {
        $data = [
            "overallRating" => 0,
            "totalReviewerCount" => 0,
            "reviewers" => []
        ];

        $fbReviews = [];
        $baseReviews = null;
        $reviewPart = 0;

        $facebookTotalRating = 0;
        $facebookRatingCount = 0;
        $facebookOverallStarRating = 0;

        $baseTotalRating = 0;
        $baseOverallStarRating = 0;
        $baseRatingCount = 0;

        $mediaService = new MediaService();

        // Facebook part review
        $reviews = Review::orderBy("created_at")->get();
        if ($reviews->count()) {
            $reviewPart++;
            foreach ($reviews as $review) {
                $facebookRatingCount += $review->rating_count;
                $facebookTotalRating += $review->overall_star_rating;
                $reviewerItems = json_decode($review->reviewers, true);
                foreach ($reviewerItems as $reviewer) {
                    $fbReviews[] = [
                        'title' => $reviewer['title'],
                        'description' => array_key_exists("description", $reviewer) ? $reviewer['description'] : "",
                        'rating' => $reviewer['rating'],
                        'image' => array_key_exists("image", $reviewer) ? $reviewer['image'] : "",
                        'date' => "",
                    ];
                }
            };
            $facebookOverallStarRating = $facebookTotalRating / $reviews->count();
        }

        // Base Review part
        $allBaseReview = $this->baseReviewRepository->orderBy("created_at", "DESC")->get();
        if ($allBaseReview->count()) {
            $reviewPart++;
            $baseReviews = $allBaseReview->map(function ($item) use ($mediaService) {
                $name = "";
                $image = null;
                $reviewerUser = User::find($item->reviewer_id);

                if ($reviewerUser) {
                    $name = $reviewerUser->first_name . " " . $reviewerUser->last_name;
                    $image = $mediaService->getImages($reviewerUser)['square'];
                }
                return [
                    "title" => $name,
                    "description" => $item->text ?? "",
                    "image" => $image,
                    "rating" => $item->rating,
                    "date" => date('d/m/Y', strtotime($item->created_at)),
                ];
            })->values();
            $baseRatingCount = $this->baseReviewRepository->count();
            $baseTotalRating = $this->baseReviewRepository->all()->sum("rating");
            $baseOverallStarRating = $baseTotalRating / $baseRatingCount;
        }

        $data["overallRating"] = round(($facebookOverallStarRating + $baseOverallStarRating) / $reviewPart, 1);
        $data["totalReviewerCount"] = $facebookRatingCount + $baseRatingCount;

        return $data;
    }


    public function overallStarRating($user)
    {
        $rating = 0;
        $overallStarRating = 0;
        $reviewPart = 0;


        // Facebook part review rating count
        $review = Review::where("user_id", $user->id)->first();
        if ($review) {
            $reviewPart++;
            $overallStarRating += $review->overall_star_rating;
        }

        // Base platform part review rating count
        $baseReviews = $this->baseReviewRepository->findWhere(['user_id' => $user->id]);
        if ($baseReviews->count()) {
            $reviewPart++;
            $overallStarRating += $baseReviews->sum("rating") / $baseReviews->count();
        }

        // Avg count from two part
        $rating = $reviewPart ? $overallStarRating / $reviewPart : 0;

        return round($rating, 2);
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
        $baseReviews = $this->baseReviewRepository->findWhere(['user_id' => $user->id]);
        if ($baseReviews->count()) {
            $totalReviewer += $baseReviews->count();
        }

        return $totalReviewer;
    }

    public function reviewers($user)
    {
        $fbReviews = [];
        $baseReviews = [];

        $mediaService = new MediaService();
        $review = Review::where('user_id', $user->id)->first();
        if ($review) {
            $reviewerItems = json_decode($review->reviewers, true);
            foreach ($reviewerItems as $reviewer) {
                $image = $reviewer['image'];
                if (array_key_exists("unexpired_image", $reviewer) && $reviewer['unexpired_image']) {
                    $image = $mediaService->getFacebookImageUrl($reviewer['unexpired_image']);
                }
                $fbReviews[] = [
                    'title' => $reviewer['title'],
                    'description' => array_key_exists("description", $reviewer) ? $reviewer['description'] : "",
                    'rating' => $reviewer['rating'],
                    'image' => $image ?? "",
                    'date' => "",
                ];
            }
        }

        $baseReviews = $this->baseReviewRepository
            ->orderBy("created_at", "DESC")
            ->findWhere(['user_id' => $user->id])
            ->map(function ($item) use ($mediaService) {
                $name = "";
                $image = null;
                $reviewerUser = User::find($item->reviewer_id);

                if ($reviewerUser) {
                    $name = $reviewerUser->first_name . " " . $reviewerUser->last_name;
                    $image = $mediaService->getImages($reviewerUser)['square'];
                }
                return [
                    "title" => $name,
                    "description" => $item->text ?? "",
                    "image" => $image,
                    "rating" => $item->rating,
                    "date" => date('d/m/Y', strtotime($item->created_at)),
                ];
            })->all();

        $collection = collect($baseReviews);
        $concat = $collection->concat($fbReviews);
        return $concat->all();
    }
}
