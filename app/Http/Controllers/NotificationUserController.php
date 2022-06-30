<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreNotificationUserRequest;
use App\Http\Requests\UpdateNotificationUserRequest;
use App\Http\Resources\NotificationUser\NotificationUserResource;
use App\NotificationUser;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class NotificationUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return NotificationUserResource|array []
     */
    public function index()
    {
        $userId = Auth::user()->id;
        $nu = NotificationUser::where('userId', '=', $userId)->first();
        if ($nu){
            return new NotificationUserResource($nu);
        }
        return [];
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreNotificationUserRequest $request
     * @return NotificationUserResource
     */
    public function store(StoreNotificationUserRequest $request): NotificationUserResource
    {
        $userId = Auth::user()->id;
        $nu = NotificationUser::where('userId', '=', $userId)->first();

        $request['userId'] = $userId;
        if ($nu){
            $nu->update($request->all());
            return new NotificationUserResource($nu);
        }
        $item = NotificationUser::create($request->all());
        return new NotificationUserResource($item);
    }

    /**
     * Display the specified resource.
     *
     * @param NotificationUser $notificationUser
     * @return Response
     */
    public function show(NotificationUser $notificationUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateNotificationUserRequest $request
     * @param NotificationUser $notificationUser
     */
    public function update(UpdateNotificationUserRequest $request, NotificationUser $notificationUser)
    {
        $status = $notificationUser['status'];
        if ($status == NotificationUser::STATUS_ON){
            $status = NotificationUser::STATUS_OFF;
        }else {
            $status = NotificationUser::STATUS_ON;
        }
        $notificationUser->status = $status;
        $notificationUser->save();
        return new NotificationUserResource($notificationUser);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param NotificationUser $notificationUser
     * @return Response
     */
    public function destroy(NotificationUser $notificationUser)
    {
        //
    }
}
