<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\Gallery;
use App\Entities\Image;
use App\Entities\Video;
use App\Http\Controllers\Controller;
use App\Services\ProgressService;
use App\Services\StepService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class GalleryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        return Gallery::where('user_id', $user->id)->get(['id', 'type', 'url', 'file_name'])->toArray();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $rules = ['type' => 'required'];
        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return $validator->errors();
        }

        $type = $request['type'];
        $image = $request['image'];

        $response = [];

        $user = Auth::user();
        $gallery = new Gallery();
        $gallery->user_id = $user->id;
        $gallery->url = $request['url'];
        $gallery->type = $request['type'];

        if ($type == Constants::GALLERY_ASSET_TYPE_IMAGE && $image) {
            $gallery->file_name = $this->uploadImage($image);
        }

        if ($gallery->save()) {
            $progressService= new ProgressService();
            $progress = $progressService->getUserImageAndVideoPageProgress($user);

            $response['progress'] = $progress;
            $response['status'] = 'success';
            $response['message'] = 'Successfully saved your url';
            $response['gallery'] = $gallery;
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
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $response = [];
        $gallery = Gallery::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        if ($gallery) {
            if ($gallery->file_name && Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->has($gallery->file_name)) {
                Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->delete($gallery->file_name);
            }
            if ($gallery->delete()) {
                $response['status'] = 'success';
                $response['message'] = 'Successfully removed';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Sorry, something went wrong.';
            }
        } else {
            $response['status'] = 'error';
            $response['message'] = 'This url is not found';
        }

        return $response;
    }

    public function uploadImage($file_data)
    {
        $fileName = '';
        @list($type, $file_data) = explode(';', $file_data);
        @list(, $file_data) = explode(',', $file_data);

        if ($file_data != "") {
            $extension = explode("/", $type)[1];
            $prefix = 'id_' . Auth::id() . '_';
            $fileName = $prefix . time() . '.' . $extension;
            Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->put($fileName, base64_decode($file_data));
        }

        return $fileName;
    }
}
