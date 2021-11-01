<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\ContactData;
use App\Data\GroupInvitationData;
use App\Data\StatusCode;
use App\Entities\Contact;
use App\Entities\Group;
use App\Entities\GroupInvitation;
use App\Entities\GroupUser;
use App\Http\Controllers\Controller;
use App\Mail\JoinConversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class GroupInvitationController extends Controller
{

    public function verify(Request $request)
    {
        try {
            $authUser = Auth::user();
            $groupInvitation = GroupInvitation::where('token', $request['token'])->first();

            if (!$groupInvitation) {
                throw new \Exception('No token found');
            }

            $group = Group::find($groupInvitation->group_id);
            if ($group) {
                $groupUser = GroupUser::where('group_id',$group->id )->where('user_id', $authUser->id)->first();
                if($groupUser){
                   throw new \Exception('Already joined');
                }
                $groupUser = new GroupUser();
                $groupUser->grou_id = $group->id;
                $groupUser->user_Id = $authUser->id;
                $groupUser->save();

                $contact = new Contact();
                $contact->user_id = $authUser->id;
                $contact->group_id = $groupInvitation->group_id;
                $contact->contact_category_id = ContactData::CATEGORY_ID_GROUP;
                $contact->last_message = "";
                $contact->save();

                $groupInvitation->status = GroupInvitationData::STATUS_ACCEPTED;
                $groupInvitation->save();
            } else {
                throw new \Exception('Group not found');
            }

            return response([
                'data' => [
                    'groupId' => $group->id
                ]
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function invite(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'emails' => 'required',
            ]);
            $authUser = Auth::user();
            foreach ($request['emails'] as $email) {
                $token = uniqid($authUser->id, true);
                $groupInvitation = new GroupInvitation();
                $groupInvitation->group_id = $id;
                $groupInvitation->user_id = $authUser->id;
                $groupInvitation->token = $token;
                $groupInvitation->status = GroupInvitationData::STATUS_PENDING;
                $groupInvitation->save();
                Mail::to([$email])->send(new JoinConversation($authUser, $token));
            }

            $group = Group::find($groupInvitation->group_id);
            if ($group) {
                if ($group->connection_users_id) {
                    $connectionUsersId = json_decode($group->connection_users_id, true);
                    $group->connection_users_id = json_encode(array_push($connectionUsersId, $authUser->id));
                } else {
                    $group->connection_users_id = json_encode([$authUser->id]);
                }
                $group->save();
            }

            return response([
                'data' => [
                    'groupId' => $groupInvitation->group_id
                ]
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ]
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
