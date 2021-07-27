<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Http\Controllers\Controller;
use App\Services\Contact\ContactService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class ChatController extends Controller
{
    public function index(Request $request)
    {
        $authUser = Auth::user();
        $contactService = new ContactService();
        $users = $contactService->getContact($authUser);
        return response()->json([
            'users' => $users
        ]);
    }
}
