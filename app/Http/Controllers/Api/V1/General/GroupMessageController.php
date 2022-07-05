<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\ContactData;
use App\Data\MessageData;
use App\Data\StatusCode;
use App\Entities\Contact;
use App\Entities\Group;
use App\Entities\GroupMessage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Group\GroupMessageCollection;
use App\Http\Resources\Group\GroupMessageResource;
use App\ValueObjects\Message\Attachment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GroupMessageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $groupId = $request->groupId;

            $groupMessages = GroupMessage::where(function($q) use($groupId) {
                if ($groupId) {
                    $q->where('group_id', $groupId);
                }
            })->get();

            return response([
                'data' => new GroupMessageCollection($groupMessages),
            ], StatusCode::HTTP_OK);
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
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'required',
                'content' => 'required',
                'groupId' => 'required',
                'createdAt' => 'required',
            ]);

            $group = Group::find($request['groupId']);
            if(!$group){
                throw new \Exception('Group is not found');
            }
            $groupMessage = new GroupMessage();
            $groupMessage->type = $request['type'];
            $groupMessage->message_category_id = $request['type'] == 'text' ? MessageData::CATEGORY_ID_TEXT : null;
            $groupMessage->group_id = $group->id;
            $groupMessage->sender_user_id = Auth::id();
            $groupMessage->content = json_encode($request['content']);
            $groupMessage->date_time = Carbon::now();
            $groupMessage->date_time_iso = $request['createdAt'];
            $groupMessage->save();

            $contacts = Contact::where('group_id', $group->id)->get();
            foreach ($contacts as $contact) {
                $contact->last_message_time = Carbon::now();
                $contact->last_message = $groupMessage->toJson();
                $contact->save();
            }
            return response([
                'data' => new GroupMessageResource($groupMessage),
                'message' => 'Successfully send a message'
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response([
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => StatusCode::HTTP_UNPROCESSABLE_ENTITY
                ]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function storeAttachment(Request $request)
    {
        try {
            $this->validate($request, [
                'type' => 'required',
                'file' => 'required|max:20000',
                'groupId' => 'required',
                'createdAt' => 'required',
            ]);

            $name = $request->file('file')->store(
                '', 'minio'
            );

            $attachment = $request->fileType && $request->fileType == 'video'?  env('MINIO_ENDPOINT')."/".env('MINIO_BUCKET')."/".$name : $name;

            $messageContent = new Attachment([
                'key' => $request->fileType && $request->fileType == 'video'? 'video' : 'attachment',
                'url' =>  $attachment
            ]);

            $group = Group::find($request['groupId']);
            if(!$group){
                throw new \Exception('Group is not found');
            }
            $groupMessage = new GroupMessage();
            $groupMessage->type = $request['type'];
            $groupMessage->message_category_id = $request['type'] == 'structure' ? MessageData::CATEGORY_ID_ATTACHMENT : null;
            $groupMessage->group_id = $group->id;
            $groupMessage->sender_user_id = Auth::id();
            $groupMessage->content = $messageContent->toJson();
            $groupMessage->date_time = Carbon::now();
            $groupMessage->date_time_iso = $request['createdAt'];
            $groupMessage->save();

            $contacts = Contact::where('group_id', $group->id)->get();
            foreach ($contacts as $contact) {
                $contact->last_message_time = Carbon::now();
                $contact->last_message = $groupMessage->toJson();
                $contact->save();
            }

            // $messageFormatService = new MessageFormatterService();
            // $messageData = $messageFormatService->doFormat($message);

            return response([
                'data' => new GroupMessageResource($groupMessage),
                'message' => 'Successfully send a message'
            ], StatusCode::HTTP_OK);
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
