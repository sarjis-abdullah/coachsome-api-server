<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\StatusCode;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Services\Contact\ContactService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;


class ContactController extends Controller
{
    public function index(Request $request)
    {
        $data = [];

        $authUser = Auth::user();
        $contactService = new ContactService();

        $resetUser = User::find($request->query('resetUserId'));
        if ($resetUser) {
            $contactService->resetContactNewMessageCount($authUser, $resetUser);
        }
        $data['users'] = $contactService->getContact($authUser);

        return response($data, StatusCode::HTTP_OK);
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
}
