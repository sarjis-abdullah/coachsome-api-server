<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Data\GalleryData;
use App\Data\StatusCode;
use App\Entities\Gallery;
use App\Entities\Image;
use App\Entities\Video;
use App\Http\Controllers\Controller;
use App\Services\Media\MediaService;
use App\Services\ProgressService;
use App\Services\StepService;
use Illuminate\Database\Eloquent\Model;
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
        try {
            $data = [];
            $user = Auth::user();
            if (!$user) {
                throw new \Exception('User not found');
            }
            $mediaService = new MediaService();

            $data['items'] = Gallery::where('user_id', $user->id)->get()->map(function ($item) use ($mediaService) {
                $url = $item->url ?? '';
                if ($item->type == 'image') {
                    $url = $mediaService->getGalleryImageUrl($item->file_name);
                }
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'url' => $url,
                ];
            });

            return response($data, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        try {
            $data = [];

            $type = $request['type'];
            $url = $request['url'];
            $image = $request['image'];

            $validator = Validator::make($request->all(), ['type' => 'required']);
            if ($validator->fails()) {
                throw new \Exception("Validation errors");
            }

            $user = Auth::user();
            $mediaService = new MediaService();

            $gallery = new Gallery();
            $gallery->user_id = $user->id;
            $gallery->type = $type;
            if ($type == GalleryData::ASSET_TYPE_IMAGE && $image) {
                $gallery->file_name = $this->uploadImage($image);
                $gallery->url = null;
                $url = $mediaService->getGalleryImageUrl($gallery->file_name);
            }
            if ($type == GalleryData::ASSET_TYPE_VIDEO) {
                $gallery->url = $url;
            }
            $gallery->save();

            $data['item'] = [
                'id'=> $gallery->id,
                'type'=> $gallery->type,
                'url'=> $url,
            ];

            $progressService = new ProgressService();
            $progress = $progressService->getUserImageAndVideoPageProgress($user);
            $data['progress'] = $progress;

            $data['message'] = 'Successfully saved your url';

            return response($data, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
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
