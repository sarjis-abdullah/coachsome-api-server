<?php

namespace App\Http\Controllers\Api\V1\Coach;

use App\Entities\ContactUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\ContactUser\IndexRequest;
use App\Http\Requests\ContactUser\StoreRequest;
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
        $items = ContactUser::where('contactToUserId', '=', Auth::user()->id)->get();
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
        $item = ContactUser::create($request->all());
        return new ContactUserResource($item);
    }

    /**
     * Display the specified resource.
     *
     * @param ContactUser $contactUser
     * @return Response
     */
    public function show(ContactUser $contactUser)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ContactUser $contactUser
     * @return string
     */
    public function update(UpdateContactUserRequest $request, ContactUser $contactUser): string
    {
        return "";
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param ContactUser $contactUser
     * @return Response
     */
    public function destroy(ContactUser $contactUser)
    {
        //
    }
}
