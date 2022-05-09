<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\Constants;
use App\Data\ExerciseData;
use App\Data\GalleryData;
use App\Data\StatusCode;
use App\Entities\Exercise;
use App\Entities\ExerciseAsset;
use App\Entities\Gallery;
use App\Entities\Image;
use App\Entities\Video;
use App\Http\Controllers\Controller;
use App\Http\Resources\Exercise\ExerciseResource;
use App\Services\Media\MediaService;
use App\Services\ProgressService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ExerciseController extends Controller
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
            $storageService = new StorageService();

            $data['items'] = Gallery::where('user_id', $user->id)->get()->filter(function($item) use($storageService){
                if ($item->type == 'image') {
                   return $storageService->hasImage($item->file_name);
                } else {
                    return true;
                }
            })->map(function ($item) use ($mediaService) {
                $url = $item->url ?? '';
                if ($item->type == 'image') {
                    $url = $mediaService->getGalleryImageUrl($item->file_name);
                }
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'url' => $url,
                ];
            })->values();

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
    public function storeAssets(Request $request)
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

            $exerciseAsset = new ExerciseAsset();
            $exerciseAsset->user_id = $user->id;
            $exerciseAsset->type = $type;
            if ($type == ExerciseData::ASSET_TYPE_IMAGE && $image) {
                $exerciseAsset->file_name = $this->uploadImage($image);
                $exerciseAsset->url = null;
                $url = $mediaService->getGalleryImageUrl($exerciseAsset->file_name);
            }
            if ($type == ExerciseData::ASSET_TYPE_VIDEO) {
                $exerciseAsset->url = $url;
            }
            $exerciseAsset->save();

            $data['item'] = [
                'id'=> $exerciseAsset->id,
                'type'=> $exerciseAsset->type,
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
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        // dd($request->all());

        try {
            $data = [];

            $request->validate([
                'name' => 'required',
                'instructions' => 'required',
                'category' => 'required',
                'sport' => 'required',
                'lavel' => 'required',
                'tags' => 'required',
            ]);


            $asset_ids = implode(',', array_column($request->assets, 'id'));
            $category_id = $request->category['id'];
            $sport_id = $request->sport['id'];
            $lavel_id = $request->lavel['id'];
            $exercise = new Exercise();
            $exercise->user_id = Auth::user()->id;
            $exercise->exercise_asset_ids = $asset_ids;
            $exercise->name = $request->name;
            $exercise->instructions = $request->instructions;
            $exercise->category_id = $category_id;
            $exercise->sport_id = $sport_id;
            $exercise->lavel_id = $lavel_id;
            $exercise->tags = implode(',', $request->tags);
            $exercise->type = $request->type;

            $exercise->save();

            $data['exercise'] = new ExerciseResource($exercise);
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
    public function destroyAssets($id)
    {
        $response = [];
        $exerciseAsset = ExerciseAsset::where('id', $id)
            ->where('user_id', Auth::id())
            ->first();
        if ($exerciseAsset) {
            if ($exerciseAsset->file_name && Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->has($exerciseAsset->file_name)) {
                Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->delete($exerciseAsset->file_name);
            }
            if ($exerciseAsset->delete()) {
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
            $fileName = "exercise/".$prefix . time() . '.' . $extension;
            Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->put($fileName, base64_decode($file_data));
        }

        return $fileName;
    }

    public function getCategory(Request $request)
    {
        try {
            $categories = [];

            $categories = Config::get('exercise.exercise_categories');

            return response()->json([
                'categories' => $categories
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function getLavels(Request $request)
    {
        try {
            $lavels = [];

            $lavels = Config::get('exercise.exercise_lavels');

            return response()->json([
                'lavels' => $lavels
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    
}
