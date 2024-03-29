<?php

namespace Coachsome\ChatServer\Http\Controllers;

use App\Data\StatusCode;
use App\Entities\Job;
use App\Entities\PendingNotification;
use App\Entities\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function doOnline(Request $request, $id)
    {
        try {

            $this->validateClient($request);
            $user = User::find($id);
            if ($user) {
                $user->is_online = 1;
                $user->save();

                // Destroy pending notification and related job
                $jobIdList = PendingNotification::where('user_id', $user->id)->get()->pluck('job_id')->toArray();
                Job::whereIn('id', $jobIdList)->delete();
                PendingNotification::where('user_id', $user->id)->delete();
            }
            return response([
                'data' => [],
                'message' => 'User id ' . $id . ' is connected'
            ], StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function doOffline(Request $request, $id)
    {
        try {
            $this->validateClient($request);
            $user = User::find($id);
            if ($user) {
                $user->is_online = 0;
                $user->save();
            }
            return response([
                'data' => [],
                'message' => 'User id ' . $id . ' is disconnected'
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function doOfflineAll(Request $request)
    {
        try {
            $this->validateClient($request);
            $users = User::all();
            foreach ($users as $user) {
                $user->is_online = 0;
                $user->save();
            }
            return response([
                'data' => [],
                'message' => 'Users are disconnected'
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
