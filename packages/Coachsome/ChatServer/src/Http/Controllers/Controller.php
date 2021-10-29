<?php

namespace Coachsome\ChatServer\Http\Controllers;

use Coachsome\ChatServer\Models\Client;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function validateClient($request)
    {
        $secret = $request->header('Client-Secret');
        $client = Client::where('secret', $secret)->first();
        if(!$client){
            throw new \Exception('Client-Secret is not valid.');
        }
    }
}
