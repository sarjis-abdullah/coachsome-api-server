<?php

namespace App\Http\Controllers\Api\V1\Admin\User;

use App\Data\Constants;
use App\Entities\ActivityStatus;
use App\Entities\Badge;
use App\Entities\Profile;
use App\Entities\StarStatus;
use App\Entities\User;
use App\Entities\UserLog;
use App\Http\Controllers\Controller;
use App\Http\Resources\Badge\BadgeResource;
use App\Services\TransformerService;
use App\Transformers\Admin\User\UserListTransformer;
use App\Transformers\Admin\User\UserTransformer;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;


class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $response = [];

        $users = User::has('roles')->get();
        $activityStatusList = ActivityStatus::all()->toArray();
        $starStatusList = StarStatus::all()->toArray();

        $transformerService = new TransformerService();
        $transformedUserList = $transformerService->getTransformedData(new Collection($users, new UserListTransformer()));

        $response['users'] = $transformedUserList;
        $response['activityStatusList'] = $activityStatusList;
        $response['starStatusList'] = $starStatusList;
        $response['badges'] = BadgeResource::collection(Badge::all());

        return $response;
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
        $response = [];

        try {

            $request->validate([
                'id' => 'required|numeric',
                'email' => [
                    'required',
                    Rule::unique('users')->ignore($id),
                ],
                'activityStatusId' => 'nullable|numeric',
                'starStatusId' => 'nullable|numeric'
            ]);


            $userId = $request->id;
            $email = $request->email;
            $mobileCode = $request->phoneCode;
            $mobileNumber = $request->phoneNumber;
            $badgeId = $request->badgeId;
            $ranking = $request->ranking;
            $starStatusId = $request->starStatusId;
            $skillLevelValue = $request->skillLevelValue;
            $activityStatusId = $request->activityStatusId;
            $activityStatusReason = $request->activityStatusReason;
            $user = User::where('id', $userId)->firstOrFail();


            // Profile
            $profile = $user->profile;
            if (!$profile) {
                $profile = new Profile();
                $profile->user_id = $user->id;
                $profile->save();
            }

            if ($mobileCode) {
                $profile->mobile_code = $mobileCode;
            }

            if ($mobileNumber) {
                $profile->mobile_no = $mobileNumber;
            }

            $profile->save();

            // User
            $user->activity_status_id = $activityStatusId;
            $user->activity_status_reason = $activityStatusReason;
            $user->ranking = $ranking;
            $user->badge_id = $badgeId;
            if($email){
                $user->email = $email;
            }
            $user->save();
            $transformerService = new TransformerService();
            $transformedUser = $transformerService->getTransformedData(new Item($user, new UserTransformer()));

            // User log
            UserLog::createByUser($user);

            $response['user'] = $transformedUser;
            $response['status'] = 'success';
            $response['message'] = 'Successfully updated.';
            return response()->json($response, Constants::HTTP_OK);

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                $response['status'] = 'error';
                $response['message'] = $e->validator->errors()->first();
                return response()->json($response, Constants::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($e instanceof ModelNotFoundException) {
                $response['status'] = 'error';
                $response['message'] = 'User not found';
                return response()->json($response, Constants::HTTP_UNPROCESSABLE_ENTITY);
            }

            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            return response()->json($response, Constants::HTTP_UNPROCESSABLE_ENTITY);
        }
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
