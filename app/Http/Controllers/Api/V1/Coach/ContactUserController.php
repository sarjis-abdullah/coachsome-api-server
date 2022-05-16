<?php

namespace App\Http\Controllers\Api\V1\Coach;

use App\Data\StatusCode;
use App\Entities\ContactUser;
use App\Events\CreateNewContactUserEvent;
use App\Events\SendEmailToContactUserEvent;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactUser\DeleteRequest;
use App\Http\Requests\ContactUser\IndexRequest;
use App\Http\Requests\ContactUser\ResendInvitationRequest;
use App\Http\Requests\ContactUser\StoreRequest;
use App\Http\Requests\ContactUser\UpdateRequest;
use App\Http\Resources\ContactUser\ContactUserResource;
use App\Http\Resources\ContactUser\ContactUserResourceCollection;
use Illuminate\Http\Response;
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

}
