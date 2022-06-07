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
use App\Http\Resources\Exercise\ExerciseCollection;
use App\Http\Resources\Exercise\ExerciseResource;
use App\Services\Media\MediaService;
use App\Services\ProgressService;
use App\Services\StorageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;

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

            $response = [];

            $exercises = Exercise::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->get();

            $empty_assets = ExerciseAsset::where('exercise_id', null)->get();

            if(!empty($empty_assets)){
                foreach($empty_assets as $empty_asset){
                    $this->destroyAssets($empty_asset->id);
                }
            }

            $response['exercises'] = new ExerciseCollection($exercises);

            return response($response, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function filter(Request $request){

        try {

            $response = [];

            $query = Exercise::where('user_id', Auth::user()->id)->orderBy('id', 'asc');

            if($request->withVideo){

                $exercise_assets_with_video_ids = Exercise::leftJoin('exercise_assets', 'exercises.id', 'exercise_assets.exercise_id')
                ->where('exercise_assets.type', 'video')
                ->where('exercises.user_id', Auth::user()->id)
                ->orWhere('exercise_assets.type', 'custom-video')
                ->select('exercises.id')->groupBy('exercises.id')->get()->pluck('id')->toArray();

                $query->whereIn('id', $exercise_assets_with_video_ids);

            }

            if($request->typeSytem == 1 && $request->typeCustom == 1){

                $query->where('type', 1);

            }else if($request->typeCustom == 1 && $request->typeSytem != 1){

                $query->where('type', 2);

            }else if($request->typeCustom == 1 && $request->typeSytem == 1){
                $query->where('type', 1)->orWhere('type', 2);
            }



            if($request->categoriesSelected != null){

                $category_ids    = array_column($request->categoriesSelected, 'id');

                foreach($category_ids as $category_id){

                    $query->whereRaw("find_in_set('".$category_id."',category_id)");
                   
                }

            }

            if($request->lavelsSelected != null){
                

                $lavel_ids   = array_column($request->lavelsSelected, 'id');

                foreach($lavel_ids as $lavel_id){

                    $query->whereRaw("find_in_set('".$lavel_id."',lavel_id)");
                }


            }

            if($request->sportsSelected != null){

                $sport_ids   = array_column($request->sportsSelected, 'id');

                foreach($sport_ids as $sport_id){

                    $query->whereRaw("find_in_set('".$sport_id."',sport_id)");
                }
            }
            
            $exercises = $query->get();

            $response['exercises'] = new ExerciseCollection($exercises);

            return response($response, StatusCode::HTTP_OK);

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
                $exerciseAsset->sort = 2;
                $url = $mediaService->getGalleryImageUrl($exerciseAsset->file_name);
            }
            if ($type == ExerciseData::ASSET_TYPE_VIDEO) {
                $exerciseAsset->url = $url;
                $exerciseAsset->sort = 1;
            }

            if($type == 'custom-video'){
                
                if($request->hasFile('file')){
                    $file = $request->file('file');

                    $prefix = 'id_' . Auth::id() . '_';
                    $fileName = "exercise/".$prefix . time() . '.' .$request->file->getClientOriginalExtension();
    
                    Storage::putFileAs('public/images/', $file, $fileName);
    
                    $exerciseAsset->file_name = $fileName;
                    $exerciseAsset->sort = 1;
                    $url = $mediaService->getExerciseVideoUrl($exerciseAsset->file_name);
                }
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
        try {
            $data = [];

            $validator = Validator::make($request->all(), [
                'name'          => 'required',
                'instructions'  => 'required',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->getMessageBag()->first());
            }

            $asset_ids      = implode(',', array_column($request->assets, 'id'));
            $category_id    = implode(',', array_column($request->category, 'id'));
            $sport_id    = implode(',', array_column($request->sport, 'id'));
            $lavel_id    = implode(',', array_column($request->lavel, 'id'));

            $exercise                       = new Exercise();
            $exercise->user_id              = Auth::user()->id;
            $exercise->exercise_asset_ids   = $asset_ids;
            $exercise->name                 = $request->name;
            $exercise->instructions         = $request->instructions;
            $exercise->category_id          = $category_id;
            $exercise->sport_id             = $sport_id;
            $exercise->lavel_id             = $lavel_id;
            $exercise->tags                 = implode(',', $request->tags);
            $exercise->type                 = $request->type;

            if($exercise->save()){

                $ex_assets = array_column($request->assets, 'id');

                foreach($ex_assets as $asset){
                    $newExAsset =ExerciseAsset::where('id', $asset)->update([
                        'exercise_id' => $exercise->id
                    ]);
                }
            }

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
        
        try {

            $response = [];

            $exercise = Exercise::where('user_id', Auth::user()->id)->where('id', $id)->first();

            $response['exercise'] = new ExerciseResource($exercise);

            return response($response, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function edit($id)
    {
        
        try {

            $response = [];



            $exercise = Exercise::where('id', $id)->first();

            $exercise->setAttribute('show_default_image', false);

            $response['exercise'] = new ExerciseResource($exercise);

            return response($response, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    public function duplicate($id)
    {
        try {

            $response = [];

            $exercise = Exercise::where('user_id', Auth::user()->id)->where('id', $id)->first();


            if($exercise->exercise_asset_ids != ""){


                $assets = ExerciseAsset::whereIn('id', explode(',', $exercise->exercise_asset_ids))->get();

                foreach($assets as $asset){

                    if($asset->type == 'image' || $asset->type == 'custom-video'){
                        $extension = \File::extension($asset->file_name);
                        $prefix = 'id_' . Auth::id() . '_';
                        $fileName = "exercise/".$prefix .$asset->id. time() . '.' . $extension;
                        Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->copy($asset->file_name, $fileName);
                        $asset->file_name = $fileName;
                    }

                    $newAsset = $asset->replicate();
                    $newAsset->push();

                    $newAssetsData[] = array(
                        'id' => $newAsset->id,
                        'type' => $newAsset->type,
                        'url' => env('APP_SERVER_DOMAIN_STORAGE_PATH') .  $newAsset->file_name,
                        'url_type' => 'stored'
                    );
                    
                }

                $exercise->exercise_asset_ids = implode(',', array_column($newAssetsData, 'id'));
  
                
            }else{
                $exercise->setAttribute('show_default_image', false);
            }

            $response['exercise'] = new ExerciseResource($exercise);

            return response($response, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
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

        $data = [];

        try {


            $validator = Validator::make($request->all(), [
                'name'          => 'required',
                'instructions'  => 'required',
            ]);

            if ($validator->fails()) {
                throw new \Exception($validator->getMessageBag()->first());
            }


            $asset_ids      = implode(',', array_column($request->assets, 'id'));
            $category_id    = implode(',', array_column($request->category, 'id'));
            $sport_id    = implode(',', array_column($request->sport, 'id'));
            $lavel_id    = implode(',', array_column($request->lavel, 'id'));

            $exercise                       = Exercise::where('user_id', Auth::user()->id)->where('id', $request->id)->firstOrFail();
            $exercise->user_id              = Auth::user()->id;
            $exercise->exercise_asset_ids   = $asset_ids;
            $exercise->name                 = $request->name;
            $exercise->instructions         = $request->instructions;
            $exercise->category_id          = $category_id;
            $exercise->sport_id             = $sport_id;
            $exercise->lavel_id             = $lavel_id;
            $exercise->tags                 = implode(',', $request->tags);
            $exercise->type                 = $request->type;

            if($exercise->save()){

                $ex_assets = array_column($request->assets, 'id');

                foreach($ex_assets as $asset){
                    $newExAsset =ExerciseAsset::where('id', $asset)->update([
                        'exercise_id' => $exercise->id
                    ]);
                }
            }

            $data['exercise'] = new ExerciseResource($exercise);
            return response($data, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            if ($e instanceof ValidationException) {
                $response['status'] = 'error';
                $response['message'] = $e->validator->errors()->first();
                return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($e instanceof ModelNotFoundException) {
                $response['status'] = 'error';
                $response['message'] = 'Exercise not found';
                return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
            }

            $response['status'] = 'error';
            $response['message'] = $e->getMessage();
            return response()->json($response, StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */

    public function destroy($id)
    {

        try {
            $exercise = Exercise::where('user_id', Auth::user()->id)->where('id',$id)->first();
            if (!$exercise) {
                throw new \Exception('Exercise not found');
            }

            if($exercise->exercise_asset_ids != null){

                $exerciseAssets = ExerciseAsset::where('user_id', Auth::user()->id)->whereIn('id', explode(',',$exercise->exercise_asset_ids))->orderBy('sort', 'asc')->get();

                foreach($exerciseAssets as $exerciseAsset){

                    if ($exerciseAsset) {
                        if ($exerciseAsset->file_name && Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->has($exerciseAsset->file_name)) {
                            Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->delete($exerciseAsset->file_name);
                        }
                        $exerciseAsset->delete();
                    } 
                }

            }

            $exercise->delete();
            $response['exercise'] = $exercise;
            return response($response, StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
        
    }


    public function destroyAssets($id)
    {
        $response = [];
        $exerciseAsset = ExerciseAsset::where('user_id', Auth::user()->id)->where('id', $id)->first();

        if ($exerciseAsset) {

            $exercise = Exercise::leftJoin('exercise_assets', 'exercises.id', 'exercise_assets.exercise_id')
                ->where('exercise_assets.id', $id)
                ->select('exercises.id', 'exercises.exercise_asset_ids')
                ->first();

            

            

            if ($exerciseAsset->file_name && Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->has($exerciseAsset->file_name)) {
                Storage::disk(Constants::DISK_NAME_PUBLIC_IMAGE)->delete($exerciseAsset->file_name);
            }
            if ($exerciseAsset->delete()) {
                if($exercise){
                    $asset_ids = explode(',', $exercise->exercise_asset_ids);
                    if (($key = array_search($id, $asset_ids)) !== false) {
                        unset($asset_ids[$key]);
                    }
    
                    $newExercise = Exercise::where('user_id', Auth::user()->id)->where('id',$exercise->id)->first();
                    $newExercise->exercise_asset_ids = implode(',', $asset_ids);
                    $newExercise->save();

                    $response['exercise'] = new ExerciseResource($newExercise);
                }

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
