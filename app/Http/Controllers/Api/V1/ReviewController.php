<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\StatusCode;
use App\Entities\Review;
use App\Http\Controllers\Controller;
use App\Services\Media\MediaService;
use App\Services\ProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class ReviewController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = [
            'reviewers' => []
        ];

        $user = Auth::user();
        $mediaService = new MediaService();

        $review = $user->reviews()->where('provider', 'facebook')->first();
        $decodedReviewers = json_decode($review->reviewers, true);
        foreach ($decodedReviewers as $decodedReviewer) {
            if (array_key_exists("unexpired_image", $decodedReviewer) && $decodedReviewer['unexpired_image']) {
                $decodedReviewer['image'] = $mediaService->getFacebookImageUrl($decodedReviewer['unexpired_image']);
            }
            $response['reviewers'][] = $decodedReviewer;
        }

        return $response;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        try {
            $response = [];
            $user = Auth::user();
            $mediaService = new MediaService();

            $reviewers = [];
            if ($request['reviewers']) {
                foreach ($request['reviewers'] as $reviewer) {
                    $reviewer['unexpired_image'] = null;
                    if ($reviewer['image']) {
                        $contents = file_get_contents($reviewer['image']);
                        $ext = 'jpeg';

                        $name = 'id_' . $user->id . '_' . Str::uuid() . '.' . $ext;
                        $mediaService->storeFacebookImage($name, $contents);
                        $reviewer['unexpired_image'] = $name;
                    }

                    $reviewers[] = $reviewer;
                }
            }

            $review = $user->reviews()->where('provider', 'facebook')->first() ?? new Review();
            $review->provider = 'facebook';
            $review->access_token = $request['access_token'];
            $review->user_id = $user->id;
            $review->page_id = $request['page_id'];
            $review->overall_star_rating = $request['overall_star_rating'];
            $review->rating_count = $request['rating_count'];

            // Destroy pre reviewer unexpired images
            if ($review->reviewers) {
                foreach (json_decode($review->reviewers, true) as $decodedReviewer) {
                    if (array_key_exists('unexpired_image', $decodedReviewer) && $decodedReviewer['unexpired_image']) {
                        $mediaService->destroyFacebookImage($decodedReviewer['unexpired_image']);
                    }
                }
            }

            $review->reviewers = json_encode($reviewers);
            if ($review->save()) {
                $progressService = new ProgressService();
                $progress = $progressService->getUserReviewPageProgress($user);
                $response['progress'] = $progress;
                $response['status'] = 'success';
                $response['message'] = 'Successfully saved your review';
            }

            return response()->json($response, StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
