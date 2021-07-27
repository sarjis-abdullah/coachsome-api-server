<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\StatusCode;
use App\Entities\Language;
use App\Http\Controllers\Controller;
use App\Services\TranslationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LanguageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $locale = $request->query('locale');

        try {
            $languages = [];

            if ($locale) {
                $translationService = new TranslationService();
                $translations = $translationService->getKeyByLanguageCode($locale);
                $languages = Language::get()->map(function($item) use($translations){
                    return [
                        'id'=>$item->id,
                        'name'=>$translations[$item->t_key],
                        't_key'=>$item->t_key,
                    ];
                });

            } else {
                $languages = Language::get(['id', 'name', 't_key']);
            }

            return response()->json([
                'languages' => $languages
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

}
