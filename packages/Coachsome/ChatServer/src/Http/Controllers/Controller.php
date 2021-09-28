<?php

namespace Coachsome\ChatServer\Http\Controllers;

use Coachsome\ChatServer\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function validateClient($request)
    {
        $request->validate([
            'clientId' => ['required'],
            'clientSecret' => ['required'],
        ]);

        $client = Client::where('id', $request['clientId'])
            ->where('secret', $request['clientSecret'])
            ->first();

        if(!$client){
            throw new \Exception('This client is not exist');
        }
    }
}
