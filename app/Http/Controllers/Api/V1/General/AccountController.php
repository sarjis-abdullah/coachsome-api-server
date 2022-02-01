<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\StatusCode;
use App\Entities\SocialAccount;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function delete(Request $request)
    {
        try {
            $password = $request->query('password');

            $authUser = Auth::user();

            $socialAcc = SocialAccount::where('user_id', $authUser->id)->first();

            if($socialAcc){
                $authUser->delete();
            } else {
                if(!$password){
                    throw new Exception('Password is required');
                }
                if(!Hash::check($password, $authUser->password)){
                    throw new Exception("Password is not correct");
                }
                $authUser->delete();
            }
            return response([
                'data' => []
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response(
                [
                    'error' => [
                        'message' => $e->getMessage()
                    ]
                ],
                StatusCode::HTTP_UNPROCESSABLE_ENTITY
            );
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
