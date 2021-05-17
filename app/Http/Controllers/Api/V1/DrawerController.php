<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\ActivityStatus;
use App\Http\Controllers\Controller;
use App\Services\ProgressService;
use App\Services\UserService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class DrawerController extends Controller
{
    public function getBackendDrawerInitialData()
    {
        $response = [];
        $progress = [];

        $user = Auth::user();
        $progressService= new ProgressService();

        if($user->activity_status_id ==  Constants::ACTIVITY_STATUS_ID_ACTIVE){
            $isActive = true;
        } else {
            $isActive = false;
        }

        $progress['profile'] = $progressService->getUserProfilePageProgress($user);
        $progress['package'] = $progressService->getUserPackagePageProgress($user);
        $progress['imageAndVideo'] = $progressService->getUserImageAndVideoPageProgress($user);
        $progress['geography'] = $progressService->getUserGeographyPageProgress($user);
        $progress['availability'] = $progressService->getUserAvailabilityPageProgress($user);
        $progress['review'] = $progressService->getUserReviewPageProgress($user);

        $response['isActive'] = $isActive;
        $response['progress'] = $progress;
        return $response;
    }

    public function changeActiveStatus(Request $request)
    {
        $response = [];
        $user = Auth::user();
        $userService = new UserService();
        try {
            if(!$userService->hasPermissionToChangeActiveStatus($user)){
                throw new \Exception('No permission to change active status');
            }
            $activeStatus = ActivityStatus::where('id', Constants::ACTIVITY_STATUS_ID_ACTIVE)->first();
            $inActiveStatus = ActivityStatus::where('id', Constants::ACTIVITY_STATUS_ID_INACTIVE)->first();

            $userCurrentStatusId = $user->activity_status_id;

            if($userCurrentStatusId == Constants::ACTIVITY_STATUS_ID_ACTIVE){
                $user->activity_status_id =  Constants::ACTIVITY_STATUS_ID_INACTIVE;
            }

            if($userCurrentStatusId == Constants::ACTIVITY_STATUS_ID_INACTIVE){
                $user->activity_status_id =  Constants::ACTIVITY_STATUS_ID_ACTIVE;
            }

            $user->save();

            $response['isActive'] =  $user->activity_status_id ==  Constants::ACTIVITY_STATUS_ID_ACTIVE
                ? true
                : false;
            $response['message'] = "Successfully changed your status";

            return response()->json($response, Constants::HTTP_OK);


        } catch (\Exception $e) {
            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            return response()->json($response,Constants::HTTP_UNPROCESSABLE_ENTITY);
        }

    }
}
