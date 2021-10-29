<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\ContactData;
use App\Data\GroupInvitationData;
use App\Data\StatusCode;
use App\Entities\Contact;
use App\Entities\Group;
use App\Entities\GroupInvitation;
use App\Http\Controllers\Controller;
use App\Mail\JoinConversation;
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
                'message' => 'required'
            ]);

            $authUser = Auth::user();
            $group = new Group();
            $group->created_user_id = $authUser->id;
            $group->emails = json_encode($request['emails']);
            $group->name = $request['name'];
            $group->save();

            $contact = new Contact();
            $contact->user_id = $authUser->id;
            $contact->group_id = $group->id;
            $contact->contact_category_id = ContactData::CATEGORY_ID_GROUP;
            $contact->last_message = $request['message'];
            $contact->save();

            foreach ($request['emails'] as $email) {
                $token = uniqid($authUser->id,true);
                $groupInvitation =new GroupInvitation();
                $groupInvitation->group_id = $group->id;
                $groupInvitation->user_id = $authUser->id;
                $groupInvitation->token = $token;
                $groupInvitation->status = GroupInvitationData::STATUS_PENDING;
                $groupInvitation->save();
                Mail::to([$email])->send(new JoinConversation($authUser,$token));
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
