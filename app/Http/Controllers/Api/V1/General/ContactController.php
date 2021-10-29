<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\ContactData;
use App\Data\StatusCode;
use App\Entities\Contact;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Contact\ContactCollection;
use App\Http\Resources\Contact\ContactResource;
use App\Http\Resources\Contact\ContactUserCollection;
use App\Services\Contact\ContactService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;


class ContactController extends Controller
{
    public function index(Request $request)
    {
        try {
            $statusFilter = $request->statusFilter;
            $selectedContactId = $request->selectedContactId;
            $searchFilter = $request->searchFilter;

            $authUser = Auth::user();
            $contactService = new ContactService();

            if ($selectedContactId) {
                $contact = Contact::where('user_id', $selectedContactId)->first();
                $contactService->reset($contact);
            }

            $contacts = Contact::where('user_id', $authUser->id)
                ->where(function ($q) use ($statusFilter) {
                    if ($statusFilter == ContactData::STATUS_ARCHIVED ||
                        $statusFilter == ContactData::STATUS_UNREAD ||
                        $statusFilter == ContactData::STATUS_READ) {
                        $q->where('status', $statusFilter);
                    } else {
                        $q->where('status', '!=', ContactData::STATUS_ARCHIVED);
                    }
                })
                ->orderBy('contacts.last_message_time', 'DESC')
                ->get();

            return response(['data' => new ContactCollection($contacts)], StatusCode::HTTP_OK);
        } catch (\Exception $e) {

        }
    }

    public function resetContactNewMessageInformation(Request $request)
    {

        try {
            $authUser = Auth::user();
            $connectedUser = User::find($request->query('connectedId'));

            if (!$connectedUser) {
                throw new \Exception('Selected user not found');
            }
            $contactService = new ContactService();

            $contactService->resetContactNewMessageCount($authUser, $connectedUser);

            return response(['message' => 'success'], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function unarchive(Request $request)
    {
        try {
            $userId = $request['userId'];
            $user = User::find($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }
            $contact = Contact::where('user_id', Auth::id())
                ->where('connection_user_id', $user->id)
                ->first();
            if (!$contact) {
                throw new \Exception('This user is not in your contact list');
            }
            $contact->status = ContactData::STATUS_READ;
            $contact->save();
            return response()->json(['data' => new ContactResource($contact)], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function archive(Request $request)
    {
        try {
            $userId = $request['userId'];
            $user = User::find($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }
            $contact = Contact::where('user_id', Auth::id())
                ->where('connection_user_id', $user->id)
                ->first();
            if (!$contact) {
                throw new \Exception('This user is not in your contact list');
            }
            $contact->status = ContactData::STATUS_ARCHIVED;
            $contact->save();
            return response()->json(['data' => new ContactResource($contact)], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function unread(Request $request)
    {
        try {
            $userId = $request['userId'];
            $user = User::find($userId);
            if (!$user) {
                throw new \Exception('User not found');
            }
            $contact = Contact::where('user_id', Auth::id())
                ->where('connection_user_id', $user->id)
                ->first();
            if (!$contact) {
                throw new \Exception('This user is not in your contact list');
            }
            $contact->status = ContactData::STATUS_UNREAD;
            $contact->save();
            return response()->json(['data' => new ContactResource($contact)], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
