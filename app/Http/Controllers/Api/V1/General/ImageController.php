<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Entities\Image;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;

class ImageController extends Controller
{
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
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
        $rules = ['url'=>'required'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        }

        $response = [];

        $image = new Image();
        $image->user_id = Auth::id();
        $image->url = $request['url'];
        $image->status = 1;
        if($image->save()){
            $response['status'] = 'success';
            $response['message'] = 'Successfully saved your url';
            return $response;
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Something went wrong, try again';
            return $response;
        }
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
        $image = Image::where('id',$id)
            ->where('user_id', Auth::id())
            ->first();
        $response = [];
        if($image && $image->delete()){
            $response['status'] = 'success';
            $response['message'] = 'Successfully removed';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'This url is not found';
        }

        return  $response;
    }
}
