<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\Translation;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use App\Http\Requests\StoreTranslation;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use function MongoDB\BSON\toJSON;


class TranslationController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $all_transtationData = Translation::orderBy('id', 'desc')->get();
        // $user_id = Auth::user()->id;
        // $staffs = DB::table('staffs')
        //     ->select('user_role')
        //     ->where('user_id', $user_id)
        //     ->first()
        // ;
        // $staff_role = '';
        // if($staffs) {
        //     $staff_role = $staffs->user_role;
        // }else{
        //     $staff_role = '';
        // }
        return response([
            'data' => $all_transtationData,
            //'user_role' => $staff_role,
            'message' => 'Success'
        ], 200);
    }

    public function getTranslationByGroupName(Request $request)
    {
        $all_transtationData = Translation::orderBy('id', 'desc')
            ->where('group', $request->group_name)
            ->get();
        return response([
            'data' => $all_transtationData,
            'message' => 'Success'
        ], 200);
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
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreTranslation $request)
    {
        //return $request->all();
        try {
            $translation_data = new Translation($request->all());
            $translation_data->save();
            return response([
                'data' => $translation_data,
                'message' => 'Created Successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param \App\Models\Translation $translation
     * @return \Illuminate\Http\Response
     */
    public function show(Translation $translation)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param \App\Models\Translation $translation
     * @return \Illuminate\Http\Response
     */
    public function edit(Translation $translation)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Translation $translation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Translation $translation)
    {
        //return $translation->id;
        try {
            $translation_data = Translation::find($translation->id);
            $translation_data->gl_key = $request->gl_key;
            $translation_data->status = $request->status;
            $translation_data->en_value = $request->en_value;
            $translation_data->dn_value = $request->dn_value;
            $translation_data->sv_value = $request->sv_value;
            $translation_data->page_name = $request->page_name;
            $translation_data->group = $request->group;
            $translation_data->save();
            return response([
                'data' => $translation_data
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param \App\Models\Translation $translation
     * @return \Illuminate\Http\Response
     */
    public function destroy(Translation $translation)
    {
        //
    }

    public function getTranslation()
    {
        $response = [];

        // Translation
        $translationService = new TranslationService();
        $translation = new \stdClass();
        $translation->en = (object)$translationService->getKeyByLanguageCode(Constants::LANGUAGE_USA_CODE);
        $translation->da = (object)$translationService->getKeyByLanguageCode(Constants::LANGUAGE_DENAMARK_CODE);
        $response['translation'] = $translation;

        return $response;

    }
}
