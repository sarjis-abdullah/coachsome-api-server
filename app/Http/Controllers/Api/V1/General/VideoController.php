<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Entities\Video;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class VideoController extends Controller
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

        $video = new Video();
        $video->user_id = Auth::id();
        $video->url = $request['url'];
        $video->status = 1;
        if($video->save()){
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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
        $video = Video::where('id',$id)
            ->where('user_id', Auth::id())
            ->first();
        $response = [];
        if($video && $video->delete()){
            $response['status'] = 'success';
            $response['message'] = 'Successfully removed';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'This url is not found';
        }

        return  $response;
    }

    public function getVideo(String $name)
    {
        $fileContents = Storage::disk('public')->get($name);
        $response = Response::make($fileContents, 200);
        $response->header('Content-Type', "video/mp4");
        return $response;
    }
}
