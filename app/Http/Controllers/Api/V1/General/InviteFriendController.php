<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreInviteFriendRequest;
use App\Http\Requests\UpdateInviteFriendRequest;
use App\InviteFriend;
use Illuminate\Http\Response;

class InviteFriendController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function inviteFriends()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreInviteFriendRequest  $request
     * @return Response
     */
    public function store(StoreInviteFriendRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\InviteFriend  $inviteFriend
     * @return Response
     */
    public function show(InviteFriend $inviteFriend)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\InviteFriend  $inviteFriend
     * @return Response
     */
    public function edit(InviteFriend $inviteFriend)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateInviteFriendRequest  $request
     * @param  \App\InviteFriend  $inviteFriend
     * @return Response
     */
    public function update(UpdateInviteFriendRequest $request, InviteFriend $inviteFriend)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\InviteFriend  $inviteFriend
     * @return Response
     */
    public function destroy(InviteFriend $inviteFriend)
    {
        //
    }
}
