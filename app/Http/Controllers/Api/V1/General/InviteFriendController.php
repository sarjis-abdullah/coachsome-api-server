<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Entities\InviteFriend;
use App\Events\InviteFriendEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\InviteFriend\AcceptFriendInvitationRequest;
use App\Http\Requests\InviteFriend\IndexRequest;
use App\Http\Requests\InviteFriend\InviteFriendsRequest;
use App\Http\Resources\InviteFriend\InviteFriendResource;
use App\Http\Resources\InviteFriend\InviteFriendResourceCollection;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
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
     * @return InviteFriendResourceCollection
     */
    public function inviteFriends(InviteFriendsRequest $request): InviteFriendResourceCollection
    {
        $emails = [];
        foreach ($request['emails'] as $key => $item){
            $request['email'] = $item;
            $request['status'] = InviteFriend::STATUS_TYPE_REQUESTED;
            $request['invitedByUserId'] = Auth::user()->id;
            $request['token'] = time().'-'.mt_rand();
            $result = InviteFriend::create($request->all());
            event(new InviteFriendEvent($result));
            $emails[$key] = $result;
        }
        return new InviteFriendResourceCollection($emails);
    }

    /**
     * Display the specified resource.
     *
     * @param AcceptFriendInvitationRequest $request
     * @return Application|Redirector|RedirectResponse
     */
    public function acceptFriendInvitation(AcceptFriendInvitationRequest $request)
    {
        $status = InviteFriend::STATUS_TYPE_ACCEPTED;
        $result = InviteFriend::where('token', '=', $request['token'])->first();
        $result->update(["status" => $status]);
        return redirect(env('APP_CLIENT_DOMAIN')."/login");
    }
}
