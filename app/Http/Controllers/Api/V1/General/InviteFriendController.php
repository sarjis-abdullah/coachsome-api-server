<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Entities\InviteFriend;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteFriend\IndexRequest;
use App\Http\Requests\InviteFriend\InviteFriendsRequest;
use App\Http\Resources\InviteFriend\InviteFriendResource;
use App\Http\Resources\InviteFriend\InviteFriendResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class InviteFriendController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return InviteFriendResourceCollection
     */
    public function index(IndexRequest $request)
    {
        $items = InviteFriend::where('status', '=', $request->status)->get();
        return new InviteFriendResourceCollection($items);
    }

    /**
     * Display a listing of the resource.
     *
     * @param InviteFriendsRequest $request
     * @return InviteFriendResource
     */
    public function inviteFriends(InviteFriendsRequest $request): InviteFriendResource
    {
        $request['status'] = InviteFriend::STATUS_TYPE_REQUESTED;
        $request['invitedByUserId'] = Auth::user()->id;
        $request['token'] = time().'-'.mt_rand();
        $result = InviteFriend::create($request->all());
        return new InviteFriendResource($result);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Entities\InviteFriend  $inviteFriend
     * @return Response
     */
    public function show(InviteFriend $inviteFriend)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Entities\InviteFriend  $inviteFriend
     * @return Response
     */
    public function destroy(InviteFriend $inviteFriend)
    {
        //
    }
}
