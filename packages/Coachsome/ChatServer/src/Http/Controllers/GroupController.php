<?php

namespace Coachsome\ChatServer\Http\Controllers;

use App\Data\StatusCode;
use App\Entities\Group;
use Illuminate\Http\Request;

class GroupController extends Controller
{
    public function getConnectedUser(Request $request, $groupId)
    {
        try {
            $this->validateClient($request);
            $group = Group::find($request['id']);
            if (!$group) {
                throw new \Exception('Group is not found');
            }

            $userIdList = $group->connection_users_id ? json_decode($group->connection_users_id, true) : [];

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
