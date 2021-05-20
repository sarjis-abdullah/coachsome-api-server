<?php

namespace App\Http\Controllers\Api\V1\Admin\Translation;

use App\Data\Constants;
use App\Data\StatusCode;
use App\Entities\Translation;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class TranslationController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Translation::orderBy('id', 'desc')->get();
        return response([
            'data' => $data,
            'message' => 'Success'
        ], 200);
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
            if(!$request->page_name || !$request->group || !$request->gl_key){
                throw new \Exception('Some information is missing.');
            }

            $findTranslation = Translation::where('gl_key', $request->gl_key)->first();
            if($findTranslation){
                throw new \Exception("This translation key has already existed.");
            }

            $translation = new Translation($request->all());
            $translation->save();

            return response([
                'data' => $translation,
                'message' => 'The item is created successfully.'
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param \App\Models\Translation $translation
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        try {
            $translation = Translation::find($request->id);
            $translation->gl_key = $request->gl_key;
            $translation->status = $request->status;
            $translation->en_value = $request->en_value;
            $translation->dn_value = $request->dn_value;
            $translation->sv_value = $request->sv_value;
            $translation->page_name = $request->page_name;
            $translation->group = $request->group;
            $translation->save();
            return response([
                'data' => $translation
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function getTranslationByGroupName(Request $request)
    {
        $data = Translation::orderBy('id', 'desc')
            ->where('group', $request->group_name)
            ->get();
        return response([
            'data' => $data,
            'message' => 'Success'
        ], 200);
    }


    public function getTranslation()
    {
        $response = [];
        $translationService = new TranslationService();
        $translation = new \stdClass();
        $translation->en = (object)$translationService->getKeyByLanguageCode(Constants::LANGUAGE_USA_CODE);
        $translation->da = (object)$translationService->getKeyByLanguageCode(Constants::LANGUAGE_DENAMARK_CODE);
        $response['translation'] = $translation;
        return $response;
    }
}
