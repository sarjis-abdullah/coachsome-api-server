<?php

namespace App\Http\Controllers\Api\V1\Coach;

use App\Data\StatusCode;
use App\Entities\ContactUser;
use App\Entities\User;
use App\Events\SendEmailToContactUserEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactUser\ActivateUserRequest;
use App\Http\Requests\ContactUser\DeleteRequest;
use App\Http\Requests\ContactUser\IndexRequest;
use App\Http\Requests\ContactUser\NavigateContactUserRequest;
use App\Http\Requests\ContactUser\ResendInvitationRequest;
use App\Http\Requests\ContactUser\StoreRequest;
use App\Http\Requests\ContactUser\UpdateRequest;
use App\Http\Resources\ContactUser\ContactUserResource;
use App\Http\Resources\ContactUser\ContactUserResourceCollection;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;

class ContactUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param IndexRequest $request
     * @return ContactUserResourceCollection
     */
    public function index(IndexRequest $request): ContactUserResourceCollection
    {
        $limit = !empty($request['per_page']) ? $request['per_page'] : 50; // it's needed for pagination
        $orderBy = !empty($request['order_by']) ? $request['order_by'] : 'id';
        $orderDirection = !empty($request['order_direction']) ? $request['order_direction'] : 'desc';
        $queryBuilder = ContactUser::where('receiverUserId', '=', Auth::user()->id);

        if (!empty($request['lazyQuery'])){
            $input = $request['lazyQuery'];
            $queryBuilder->where('firstName', 'LIKE', '%' . $input . '%')
                ->orWhere('email', 'LIKE', '%' . $input . '%')
                ->orWhere('categoryName', 'LIKE', '%' . $input . '%')
                ->orWhere('lastName', 'LIKE', '%' . $input . '%');
        }


        $items = $queryBuilder->orderBy($orderBy, $orderDirection)->paginate($limit);
        return new ContactUserResourceCollection($items);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreRequest $request
     * @return ContactUserResource
     */
    public function store(StoreRequest $request): ContactUserResource
    {
        $request['comment'] = "Created while coach added new contact from UI";
        $request['status'] = ContactUser::STATUS_PENDING;
        $request['token'] = time().'-'.mt_rand();
        $item = ContactUser::create($request->all());
        event(new SendEmailToContactUserEvent($item));
        return new ContactUserResource($item);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateRequest $request
     * @param ContactUser $contactUser
     * @return ContactUserResource
     */
    public function update(UpdateRequest $request, ContactUser $contactUser): ContactUserResource
    {
        $contactUser->update($request->all());
        return new ContactUserResource($contactUser);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ContactUser $contactUser
     * @param DeleteRequest $request
     * @return Response
     */
    public function destroy(ContactUser $contactUser, DeleteRequest $request): Response
    {
        $contactUser->forceDelete();
        return response([
            'message' => 'Deleted successfully!'
        ], StatusCode::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ResendInvitationRequest $request
     * @return Response
     */
    public function resendInvitation(ResendInvitationRequest $request): Response
    {
        $contactUser = ContactUser::find($request->id);
        event(new SendEmailToContactUserEvent($contactUser));
        return response([
            'message' => 'Invitation resend successfully!'
        ], StatusCode::HTTP_OK);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param NavigateContactUserRequest $request
     * @return Application|Redirector|RedirectResponse
     */
    public function navigateContactUserToLogin(NavigateContactUserRequest $request)
    {
        $contactUser = ContactUser::where('email', '=', $request->email)->where('token', '=', $request->token)->first();
        $query = "?firstName=".$contactUser->firstName."&lastName=".$contactUser->lastName."&email=".$contactUser->email."&id=".$contactUser->id;
        return redirect(env('APP_CLIENT_DOMAIN')."/register".$query);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ActivateUserRequest $request
     * @return ContactUserResource
     */
    public function activateContactUser(ActivateUserRequest $request)
    {
        $contactUser = ContactUser::find($request->id);
        $user = User::where('email', '=', $request->email)->first();
        $contactUser->update([
            'status' => ContactUser::STATUS_ACTIVE,
            'contactAbleUserId' => $user['id'],
        ]);
        return new ContactUserResource($contactUser);
    }
}
