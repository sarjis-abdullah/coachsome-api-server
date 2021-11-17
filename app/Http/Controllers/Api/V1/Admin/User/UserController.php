<?php

namespace App\Http\Controllers\Api\V1\Admin\User;

use App\Data\StatusCode;
use App\Entities\ActivityStatus;
use App\Entities\Badge;
use App\Entities\Profile;
use App\Entities\Role;
use App\Entities\StarStatus;
use App\Entities\User;
use App\Entities\UserLog;
use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\User\UserCollection;
use App\Http\Resources\Admin\User\UserResource;
use App\Http\Resources\Badge\BadgeResource;
use App\Http\Resources\Role\RoleCollection;
use App\Services\UserService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use App\Data\ActivityStatus as ActivityStatusData;

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

        $users = User::has('roles')->orderBy('id', 'DESC')->get();
        $activityStatusList = ActivityStatus::all()->toArray();
        $starStatusList = StarStatus::all()->toArray();

        $response['users'] = new UserCollection($users);
        $response['roles'] = new RoleCollection(Role::all());
        $response['activityStatusList'] = $activityStatusList;
        $response['starStatusList'] = $starStatusList;
        $response['badges'] = BadgeResource::collection(Badge::all());

        return $response;
    }

    /**
     * Store the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $data = [];

        try {

            $request->validate([
                'firstName'=>'string|required',
                'lastName'=>'string|required',
                'email' => [
                    'required',
                    Rule::unique('users'),
                ],
                'password' => 'required',
                'role' => 'required|numeric'
            ]);
            $userService = new UserService();
            $role = Role::find($request['role']);

            if(!$role){
                throw new \Exception('Role not found');
            }

            $user = new User;
            $user->first_name = $request['firstName'];
            $user->last_name = $request['lastName'];
            $user->activity_status_id =  ActivityStatusData::ACTIVE;
            $user->badge_id =  ActivityStatusData::ACTIVE;
            $user->email = $request['email'];
            $user->user_name = $userService->generateUserName($request['firstName'], $request['lastName']);
            $user->password = $userService->generateUserHashPassword($request['password']);
            $user->save();

            $user->save();
            $user->attachRole($role);

            $data['user'] = new UserResource($user);
            return response($data, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                $data['message'] = $e->validator->errors()->first();
                return response()->json($data, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
            }

            $data['message'] = $e->getMessage();
            return response()->json($data, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
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
            $roleId = $request->roleId;
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
            if($badgeId){
                $user->badge_id = $badgeId;
            }
            if($email){
                $user->email = $email;
            }
            $user->save();

            // Update role
            if($roleId){
                $role = Role::find($roleId);
                if($role) {
                    $user->syncRoles([$role->id]);
                }
            }

            // User log
            UserLog::createByUser($user);

            $response['user'] = new UserResource($user);
            $response['status'] = 'success';
            $response['message'] = 'Successfully updated.';
            return response()->json($response, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                $response['status'] = 'error';
                $response['message'] = $e->validator->errors()->first();
                return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($e instanceof ModelNotFoundException) {
                $response['status'] = 'error';
                $response['message'] = 'User not found';
                return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
            }

            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
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
