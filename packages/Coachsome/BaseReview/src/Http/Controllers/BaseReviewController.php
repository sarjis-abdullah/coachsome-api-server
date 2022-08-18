<?php

namespace Coachsome\BaseReview\Http\Controllers;

use App\Data\StatusCode;
use App\Entities\Review;
use App\Services\Review\ReviewService;
use Coachsome\BaseReview\Mail\CoachNotification;
use Coachsome\BaseReview\Mail\Invitation;
use Coachsome\BaseReview\Models\BaseReview;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\Media\MediaService;
use Coachsome\BaseReview\Mail\InvitationPWA;
use Coachsome\BaseReview\Repositories\BaseReviewRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;


class BaseReviewController extends Controller
{
    public function getAll(Request $request, BaseReviewRepository $baseReviewRepository)
    {
        $data = [
            "overallRating" => 0,
            "totalReviewerCount" => 0,
            "reviewers" => []
        ];

        try {
            $page = $request->page;
            $fbReviews = [];
            $baseReviews = null;
            $totalRatingCount = 0;
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
                    $revieweeUser = $review->user;
                    foreach ($reviewerItems as $reviewer) {
                        $fbReviews[] = [
                            'title' => $reviewer['title'],
                            'description' => array_key_exists("description", $reviewer) ? $reviewer['description'] : "",
                            'rating' => $reviewer['rating'],
                            'image' => array_key_exists("image", $reviewer) ? $reviewer['image'] : "",
                            'reviewee' => [
                                'userName' => $revieweeUser->user_name ?? '',
                                'profileName' => $revieweeUser->profile->profile_name ?? '',
                            ],
                            'date' => "",
                        ];
                    }
                };
                $facebookOverallStarRating = $facebookTotalRating / $reviews->count();
            }

            // Base Review part
            $allBaseReview = $baseReviewRepository->orderBy("created_at", "DESC")->get();
            if ($allBaseReview->count()) {
                $reviewPart++;
                $baseReviews = $allBaseReview->map(function ($item) use ($mediaService) {
                    $name = "";
                    $image = null;
                    $reviewerUser = User::find($item->reviewer_id);
                    $revieweeUser = User::find($item->user_id);

                    if ($reviewerUser) {
                        $name = $reviewerUser->first_name . " " . $reviewerUser->last_name;
                        $image = $mediaService->getImages($reviewerUser)['square'];
                    }
                    return [
                        "title" => $name,
                        "description" => $item->text ?? "",
                        "image" => $image,
                        "rating" => $item->rating,
                        'reviewee' => [
                            'userName' => $revieweeUser->user_name ?? '',
                            'profileName' => $revieweeUser->profile->profile_name ?? '',
                        ],
                        "date" => date('d/m/Y', strtotime($item->created_at)),
                    ];
                })->values();
                $baseRatingCount = $baseReviewRepository->count();
                $baseTotalRating = $baseReviewRepository->all()->sum("rating");
                $baseOverallStarRating = $baseTotalRating / $baseRatingCount;
            }

            $data["overallRating"] = round(($facebookOverallStarRating + $baseOverallStarRating) / $reviewPart, 1);
            $data["totalReviewerCount"] = $facebookRatingCount + $baseRatingCount;

            $collection = collect($baseReviews);
            $concat = $collection->concat($fbReviews);
            $data["reviewers"] = $concat->forPage($page, 10)->values();

            return response()->json($data, StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage() . " Line: " . $e->getLine()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(BaseReviewRepository $baseReviewRepository)
    {
        $data = [
            "avgReview" => null,
            "reviewerCount" => null,
            "overallRating" => null,
            "reviewerAnalysis" => [
                "maxReviewer" => 0,
                "minReviewer" => 0,
                "reviewer" => [
                    "oneStar" => 0,
                    "twoStar" => 0,
                    "threeStar" => 0,
                    "fourStar" => 0,
                    "fiveStar" => 0,
                ]
            ]
        ];

        try {
            $authUser = Auth::user();
            $reviewService = new ReviewService($baseReviewRepository);

            $baseReviews = $baseReviewRepository->findWhere(['user_id' => $authUser->id]);
            $baseReviewCollection = collect($baseReviews);
            $data["overallRating"] = $reviewService->overallStarRating($authUser);
            $data["reviewerCount"] = $reviewService->totalReviewer($authUser);

            $data["reviewerAnalysis"]["maxReviewer"] = $data["reviewerCount"];
            foreach ($baseReviews as $baseReview) {
                if ($baseReview->rating == 1) {
                    ++$data["reviewerAnalysis"]["reviewer"]["oneStar"];
                }
                if ($baseReview->rating == 2) {
                    ++$data["reviewerAnalysis"]["reviewer"]["twoStar"];
                }
                if ($baseReview->rating == 3) {
                    ++$data["reviewerAnalysis"]["reviewer"]["threeStar"];
                }
                if ($baseReview->rating == 4) {
                    ++$data["reviewerAnalysis"]["reviewer"]["fourStar"];
                }
                if ($baseReview->rating == 5) {
                    ++$data["reviewerAnalysis"]["reviewer"]["fiveStar"];
                }

            }

            return response()->json($data, StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage() . " Line: " . $e->getLine()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProfileInformation(Request $request)
    {
        $data = [
            'profileName' => null,
            'profileImage' => null,
            'nameAvatar' => null,
        ];
        $userName = $request->query("userName");
        try {
            $user = User::where("user_name", $userName)->first();
            if (!$user) {
                throw new \Exception("User not found");
            }

            $mediaService = new MediaService();

            $profile = $user->profile;
            $images = $mediaService->getImages($user);

            if ($profile) {
                $data['profileName'] = $profile->profile_name;
                $data['profileImage'] = $images['square'];
                $data['nameAvatar'] = strtoupper(substr($user->first_name, 0, 1))
                    . strtoupper(substr($user->last_name, 0, 1));
            }

            return response()->json($data, StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $userName = $request["userName"];
        $rating = $request["rating"];
        $text = $request["text"];
        try {
            if (!$rating) {
                throw new \Exception("Please give the rating.");
            }

            $user = User::where("user_name", $userName)->first();
            $authUser = Auth::user();

            if (!$text) {
                throw new \Exception("Reviewer comments is required");
            }

            if (!$user) {
                throw new \Exception("User not found");
            }
            if ($user->id == $authUser->id) {
                throw new \Exception("You can not review yourself.");
            }

            $findBaseRview = BaseReview::where('user_id', $user->id)
                ->where('reviewer_id', $authUser->id)
                ->first();
            $baseReview = $findBaseRview ?? new BaseReview();
            $baseReview->user_id = $user->id;
            $baseReview->reviewer_id = $authUser->id;
            $baseReview->rating = $rating;
            $baseReview->text = $text;
            $baseReview->save();

            Mail::to($user)->send(new CoachNotification($baseReview));

            return response()->json($baseReview, StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function makeRequest(Request $request)
    {
        $recipients = $request->all();

        try {
            foreach ($recipients as $recipient) {
                Mail::to($recipient)->send(new Invitation());
            }
            return response()->json(['message' => 'Email was send successfully.'], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    public function makeRequestPWA(Request $request)
    {
        $recipients = $request->all();

        try {
            foreach ($recipients as $recipient) {
                Mail::to($recipient)->send(new InvitationPWA());
            }
            return response()->json(['message' => 'Email was send successfully.'], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


}
