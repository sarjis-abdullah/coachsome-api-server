<?php


namespace App\Http\Controllers\Api\V1;


use App\Data\StatusCode;
use App\Entities\Review;
use App\Entities\SportCategory;
use App\Http\Controllers\Controller;
use App\Http\Resources\Category\SportCategoryResource;
use App\Services\Review\ReviewService;
use Coachsome\BaseReview\Repositories\BaseReviewRepository;

class CompanyRatingController extends Controller
{
    public function index(BaseReviewRepository $baseReviewRepository)
    {
        try {
            $ratings = 0;
            $overallRating = 0;
            $reviewService = new ReviewService($baseReviewRepository);
            $reviewInfo = $reviewService->info();
            if($reviewInfo){
                $overallRating = $reviewInfo['overallRating'];
                $totalReviewerCount = $reviewInfo['totalReviewerCount'];
            }
            return response()->json([
                'overallRating' => $overallRating,
                'totalReviewerCount' => $totalReviewerCount
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
