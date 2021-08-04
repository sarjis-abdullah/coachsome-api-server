<?php

namespace App\Http\Controllers\Api\V1\ChatServerApi;

use App\Data\StatusCode;
use App\Entities\Job;
use App\Entities\PendingNotification;
use App\Entities\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function __construct()
    {

    }

    public function doOnline(Request $request, $id)
    {
        $user = User::find($id);
        if ($user) {
            $user->is_online = 1;
            $user->save();

            // Destroy pending notification and related job
            $jobIdList = PendingNotification::where('user_id', $user->id)->get()->pluck('job_id')->toArray();
            Job::whereIn('id', $jobIdList)->delete();
            PendingNotification::where('user_id', $user->id)->delete();
        }
        return response(true, StatusCode::HTTP_OK);
    }

    public function doOffline(Request $request, $id)
    {
        $user = User::find($id);
        if ($user) {
            $user->is_online = 0;
            $user->save();
        }
        return response(true, StatusCode::HTTP_OK);
    }
}
