<?php

namespace Coachsome\ChatServer\Http\Controllers;

use App\Data\StatusCode;
use App\Entities\Group;
use App\Entities\GroupUser;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function getConnectedUser(Request $request, $id)
    {
        try {
            $this->validateClient($request);
            $group = Group::find($id);
            if (!$group) {
                throw new \Exception('Group is not found');
            }

            $userIdList = GroupUser::where('group_id', $group->id)->pluck('user_id');

            return response([
                'data' => $userIdList
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
