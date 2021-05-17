<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\City;
use App\Entities\Review;
use App\Entities\SportCategory;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Category\SportCategoryResource;
use App\Http\Resources\User\UserCollection;
use App\Transformers\Categories\CategoryTransformer;
use Illuminate\Http\Request;
use App\Services\Review\ReviewService;
use Coachsome\BaseReview\Repositories\BaseReviewRepository;


class FrontHomeController extends Controller
{
    public function index(Request $request, BaseReviewRepository $baseReviewRepository)
    {
        $data = [];

        // Query request
        $typeQuery = $request->query('type');
        $actionQuery = $request->query('action');

        if ($actionQuery == 'filter') {

            // User query initiating ...
            $userQuery = User::query();

            // Only coaches are allowed
            $userQuery->whereHas('roles', function ($q) {
                return $q->where('name', Constants::ROLE_KEY_COACH);
            });

            // Active Rules
            $userQuery->where('activity_status_id', Constants::ACTIVITY_STATUS_ID_ACTIVE);
            $userQuery->whereHas('profile', function ($q) {
                $q->where('image', '!=', null);
                $q->where('profile_name', '!=', null);
                $q->where('about_me', '!=', null);
                $q->where('mobile_no', '!=', null);
                $q->where('mobile_code', '!=', null);
            });
            $userQuery->has('sportTags', '>=', 3);
            $userQuery->has('languages');
            $userQuery->has('sportCategories');
            $userQuery->has('locations');
            $userQuery->whereHas('packages', function ($q) {
                $q->where('status', '=', 1);
            });

            // Ranking wise sorting
            $userQuery->orderBy("ranking", "DESC");


            // At least one package needed
            $userQuery->has('packages');

            // Filter by sport category
            if ($typeQuery == 'fitness' || $typeQuery == 'basketball' || $typeQuery == 'mental_coaching' || $typeQuery == 'soccer' || $typeQuery == 'handball') {
                $tKey = '';
                if ($typeQuery == 'basketball') {
                    $tkey = 'cat_basketball';
                } elseif ($typeQuery == 'mental_coaching') {
                    $tkey = 'cat_mental_coaching';
                } elseif ($typeQuery == 'soccer') {
                    $tkey = 'cat_soccer';
                } elseif ($typeQuery == 'fitness') {
                    $tkey = 'cat_fitness';
                } elseif ($typeQuery == 'handball') {
                    $tkey = 'cat_handball';
                }
                $sportCategory = SportCategory::where('t_key', $tkey)->first();
                if ($sportCategory) {
                    $userQuery->whereHas('sportCategories', function ($q) use ($sportCategory) {
                        return $q->where('sport_categories.id', $sportCategory->id);
                    });
                }
            }

            $paginateUsers = $userQuery->paginate(8);

            $data['coaches'] = new UserCollection($paginateUsers);
        }

        if ($actionQuery != 'filter') {
            $ratings = 0;
            $overallRating = 0;
            $reviewService = new ReviewService($baseReviewRepository);
            $reviewInfo = $reviewService->info();
            if ($reviewInfo) {
                $overallRating = $reviewInfo['overallRating'];
                $totalReviewerCount = $reviewInfo['totalReviewerCount'];
            }
            $data['overallRating'] = $overallRating;
            $data['totalReviewerCount'] = $totalReviewerCount;


            $popularCategories = SportCategory::where('priority', '!=', 999)
                ->orderBy('priority', 'ASC')
                ->take(3)
                ->get();

            $categoryList = SportCategory::orderBy('priority', 'ASC')->get();

            $categories = SportCategory::orderBy('priority', 'ASC')
                ->take(6)
                ->get();

            $cities = City::where('priority', '!=', 0)
                ->orderBy('priority', 'ASC')
                ->get();


            $data['cities'] = $cities;
            $data['categoryList'] = $categoryList;
            
            $data['popularCategories'] =  SportCategoryResource::collection($popularCategories);
            $data['categories'] =  SportCategoryResource::collection($categories);
        }


        return response()->json($data, Constants::HTTP_OK);
    }
}
