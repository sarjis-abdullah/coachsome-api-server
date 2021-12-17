<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\StatusCode;
use App\Entities\UserVerification;
use App\Http\Controllers\Controller;
use App\Http\Resources\Setting\UserVerificationResource;
use Illuminate\Support\Facades\Auth;

class SecuritySettingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $authUser = Auth::user();

            $userVerification = UserVerification::where('user_id', $authUser->id)->first();
            if (!$userVerification) {
                $userVerification = new  UserVerification();
                $userVerification->user_id = $authUser->id;
                if ($authUser->verified) {
                    $userVerification->email_verified_at = $authUser->created_at;
                }
                $userVerification->save();
            }

            return response([
                'data' => new UserVerificationResource($userVerification)
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
        }
    }
}
