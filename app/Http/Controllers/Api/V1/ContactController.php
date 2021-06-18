<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Contact\ContactService;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function index()
    {
        $authUser = Auth::user();
        $contactService = new ContactService();
        $users = $contactService->getContact($authUser);
        return response()->json([
            'users' => $users
        ]);
    }
}
