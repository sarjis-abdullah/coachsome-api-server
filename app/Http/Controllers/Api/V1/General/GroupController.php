<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\ContactData;
use App\Data\GroupInvitationData;
use App\Data\MessageData;
use App\Data\StatusCode;
use App\Entities\Contact;
use App\Entities\Group;
use App\Entities\GroupGlobalSetting;
use App\Entities\GroupInvitation;
use App\Entities\GroupMessage;
use App\Entities\GroupUser;
use App\Http\Controllers\Controller;
use App\Http\Resources\Group\GroupResource;
use App\Mail\JoinConversation;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    function validateEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'name' => 'required',
                'emails' => 'required',
                'message' => 'required',
                'description' => 'required'
            ]);

            foreach ($request['emails'] as $email) {
                if (!$this->validateEmail($email)) {
                    throw new \Exception('All emails should have to valid');
                }
            }

            $groupGlobalSetting = GroupGlobalSetting::get()->first();
            if ($groupGlobalSetting) {
                if (sizeof($request['emails']) > $groupGlobalSetting->max_invitation_at_once) {
                    throw new \Exception('You can not invite at once more than ' . $groupGlobalSetting->max_invitation_at_once);
                }
            }


            $authUser = Auth::user();

            $group = new Group();
            $group->created_user_id = $authUser->id;
            $group->name = $request['name'];
            $group->description = $request['description'];
            $group->save();

            $groupUser = new GroupUser();
            $groupUser->user_id = $authUser->id;
            $groupUser->group_id = $group->id;
            $groupUser->save();

            $contact = new Contact();
            $contact->user_id = $authUser->id;
            $contact->group_id = $group->id;
            $contact->contact_category_id = ContactData::CATEGORY_ID_GROUP;
            $contact->last_message = $request['message'];
            $contact->save();

            $groupMessage = new GroupMessage();
            $groupMessage->type = MessageData::TYPE_TEXT;
            $groupMessage->message_category_id = MessageData::CATEGORY_ID_TEXT;
            $groupMessage->group_id = $group->id;
            $groupMessage->sender_user_id = $authUser->id;
            $groupMessage->content = json_encode($request['message']);
            $groupMessage->date_time = Carbon::now();
            $groupMessage->date_time_iso = Carbon::now()->toISOString();
            $groupMessage->save();

            foreach ($request['emails'] as $email) {
                $token = uniqid($authUser->id, true);
                $groupInvitation = new GroupInvitation();
                $groupInvitation->group_id = $group->id;
                $groupInvitation->user_id = $authUser->id;
                $groupInvitation->token = $token;
                $groupInvitation->status = GroupInvitationData::STATUS_PENDING;
                $groupInvitation->save();
                Mail::to([$email])->send(new JoinConversation($authUser, $token));
            }

            return response(['data' => []], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => StatusCode::HTTP_UNPROCESSABLE_ENTITY
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function changeTopic(Request $request, $id)
    {
        try {
            $topic = $request['topic'];
            if(!$topic){
                throw new \Exception("Topics should not be empty");
            }
            $group = Group::find($id);
            if (!$group) {
                throw new \Exception('This group is not found');
            }
            $group->description = $topic;
            $group->save();
            return response()->json(['data' => new GroupResource($group)], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'error' => [
                    'message' => $e->getMessage()
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
