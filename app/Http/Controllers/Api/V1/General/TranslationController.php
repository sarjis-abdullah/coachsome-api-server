<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\StatusCode;
use App\Data\TranslationData;
use App\Entities\Translation;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TranslationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $data = [];

        $locale = $request->query('locale');

        if ($locale == config('app.supported_languages.en.lang_code')) {
            $data['translations'] = Translation::where('type', TranslationData::TYPE_GENERAL)->pluck('en_value', "gl_key");
        } else if ($locale == config('app.supported_languages.da.lang_code')) {
            $data['translations'] = Translation::where('type', TranslationData::TYPE_GENERAL)->pluck('dn_value', "gl_key");
        } else if ($locale == config('app.supported_languages.sv.lang_code')) {
            $data['translations'] = Translation::where('type', TranslationData::TYPE_GENERAL)->pluck('sv_value', "gl_key");
        }

        return response($data, StatusCode::HTTP_OK);
    }


}
