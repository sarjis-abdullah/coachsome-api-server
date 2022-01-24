<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\ActivityStatus;
use App\Data\RoleData;
use App\Data\StatusCode;
use App\Entities\Location;
use App\Entities\SportCategory;
use App\Entities\SportTag;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Http\Resources\Category\SportCategoryResource;
use App\Http\Resources\Tag\SportTagCollection;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $categories = [];
            $translationService = new TranslationService();
            $translations = $translationService->getKeyByLanguageCode(App::getLocale());

            $categories = [];
            $locations = [];
            $tags = [];
            $users = [];
            $searchKey = $request->query('key') ?? "";

            if ($searchKey) {
                $users = User::join('profiles', 'profiles.user_id', '=', 'users.id')
                    ->whereHas('roles', function($q){
                        $q->where('roles.id', RoleData::ROLE_ID_COACH);
                    })
                    ->where('users.activity_status_id', ActivityStatus::ACTIVE)
                    ->where('users.activity_status_id', ActivityStatus::ACTIVE)
                    ->select('profiles.profile_name', 'users.user_name')
                    ->where(function ($q) use ($searchKey) {
                        $q->where('profiles.profile_name', 'LIKE', "%$searchKey%");
                    })->get()
                    ->map(function ($item) {
                        return [
                            'userName' => $item->user_name,
                            'profileName' => $item->profile_name
                        ];
                    });

                $categories = SportCategory::get()->map(function ($item) use ($translations) {
                    if (array_key_exists($item->t_key, $translations)) {
                        $item->name = $translations[$item->t_key];
                    }
                    return $item;
                })->filter(function ($item) use ($searchKey) {
                    return stripos($item['name'], $searchKey) !== false;
                });

                $tags = SportTag::groupBy('name')
                    ->select('name', DB::raw('count(*) as total'))
                    ->whereIn('user_id', $users->pluck('id')->toArray())
                    ->where(function ($q) use ($searchKey) {
                        $q->where('name', 'LIKE', "%$searchKey%");
                    })->get();

                $locations = Location::groupBy('city')
                    ->select('city', DB::raw('count(*) as total'))
                    ->where(function ($q) use ($searchKey) {
                        $q->where('city', 'LIKE', "%$searchKey%");
                    })->get();


            }

            return response()->json([
                'categories' => SportCategoryResource::collection($categories),
                'tags' => new SportTagCollection($tags),
                'locations' => $locations,
                'users' => $users,
                'locale' => App::getLocale()
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
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
        //
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
        //
    }
}
