<?php


namespace App\Http\Controllers\Api\V1\General;


use App\Data\StatusCode;
use App\Http\Controllers\Controller;
use App\Services\Media\MediaService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;

class GeneralProfileController extends Controller
{
    public function getImage(Request $request)
    {
        try {
            $user = Auth::user();
            $mediaService = new MediaService();
            if (!$user) {
                throw new Exception("User is not found");
            }

            return response()->json([
                'image' => $mediaService->getImages($user),
            ], StatusCode::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function uploadImage(Request $request)
    {
        try {
            $images = [];

            $onboarding = $request->has('isOnboarding') ? $request['isOnboarding'] : false;


            $user = Auth::user();
            $mediaService = new MediaService();
            if (!$user) {
                throw new Exception("User is not found");
            }
            $images['original'] = $request['original'];
            $images['square'] = $request['square'];
            $images['portrait'] = $request['portrait'];
            $images['landscape'] = $request['landscape'];
            $mediaService->storeImage($user, $images, $onboarding);

            return response()->json([
                'image' => $mediaService->getImages($user, $onboarding),
            ], StatusCode::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function destroyImage(Request $request)
    {
        try {
            $user = Auth::user();
            $mediaService = new MediaService();
            if (!$user) {
                throw new Exception("User is not found");
            }
            $mediaService->destroyAll($user);
            return response()->json([
                'image' => $mediaService->getImages($user),
            ], StatusCode::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
