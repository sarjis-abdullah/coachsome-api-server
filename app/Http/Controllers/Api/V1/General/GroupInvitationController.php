<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\ContactData;
use App\Data\GroupInvitationData;
use App\Data\StatusCode;
use App\Entities\Contact;
use App\Entities\Group;
use App\Entities\GroupGlobalSetting;
use App\Entities\GroupInvitation;
use App\Entities\GroupUser;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\User\UserInfoCollection;
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
                $groupUser = GroupUser::where('group_id', $group->id)->where('user_id', $authUser->id)->first();
                if ($groupUser) {
                    throw new \Exception('Already joined');
                }
                $groupUser = new GroupUser();
                $groupUser->group_id = $group->id;
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

    function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function invite(Request $request, $id)
    {
        try {
            $this->validate($request, [
                'emails' => 'required',
            ]);

            $pwa = false;

            if($request->has('pwa')){
                $pwa = true;
            }

            foreach ($request['emails'] as $email) {
                if (!$this->validateEmail($email)) {
                    throw new \Exception('All emails should have to valid');
                }
            }
            $group = Group::find($id);

            if (!$group) {
                throw new \Exception('No group found');
            }

            $groupGlobalSetting = GroupGlobalSetting::get()->first();
            $groupUsersCount = GroupUser::where('group_id', $group->id)->count();
            if ($groupGlobalSetting) {
                if (($groupUsersCount + sizeof($request['emails'])) > $groupGlobalSetting->max_people_of_a_group) {
                    throw new \Exception('You can not invite at once more than ' . $groupGlobalSetting->max_invitation_at_once);
                }
            }

            $authUser = Auth::user();
            foreach ($request['emails'] as $email) {
                $token = uniqid($authUser->id, true);
                $groupInvitation = new GroupInvitation();
                $groupInvitation->group_id = $group->id;
                $groupInvitation->user_id = $authUser->id;
                $groupInvitation->token = $token;
                $groupInvitation->status = GroupInvitationData::STATUS_PENDING;
                $groupInvitation->save();
                Mail::to([$email])->send(new JoinConversation($authUser, $token, $pwa));
            }

            return response([
                'data' => [
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

    public function getPrivateUser(Request $request)
    {
        try {
            $authUser = Auth::user();
            $search = $request->query('search');
            $groupId = $request->query('groupId');

            $group = Group::find($groupId);
            if (!$group) {
                throw new \Exception('Group not found.');
            }

            $groupUsersId = GroupUser::where('group_id', $group->id)->pluck('user_id');

            $connectedUsersId = Contact::where('user_id', $authUser->id)
                ->where('status', '!=', ContactData::STATUS_ARCHIVED)
                ->where('contact_category_id', ContactData::CATEGORY_ID_PRIVATE)
                ->whereNotIn('connection_user_id', $groupUsersId)
                ->orderBy('contacts.last_message_time', 'DESC')
                ->pluck('connection_user_id');

            $users = User::whereIn('id', $connectedUsersId)
                ->where(function ($q) use ($search) {
                    if ($search) {
                        foreach (['first_name', 'last_name'] as $column) {
                            $q->orWhere($column, 'LIKE', '%' . $search . '%');
                        }
                    }
                })->get();
            return response([
                'data' => new UserInfoCollection($users)
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {

        }

    }
}
