<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\SportCategory;
use App\Entities\Translation;
use App\Http\Controllers\Controller;
use App\Services\CurrencyService;
use App\Services\TransformerService;
use App\Services\TranslationService;
use App\Services\UserService;
use App\Transformers\Category\CategoriesTransformer;
use App\Transformers\Package\PackageTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;

class BootingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $response = [];

        $languageCode = $request->header('Language-Code');

        $translationService = new TranslationService();

        // Translation
        $translation = new \stdClass();
        $translation->en = (object)$translationService->getKeyByLanguageCode(Constants::LANGUAGE_USA_CODE);
        $translation->da = (object)$translationService->getKeyByLanguageCode(Constants::LANGUAGE_DENAMARK_CODE);
        $translation->sv = (object)$translationService->getKeyByLanguageCode(Constants::LANGUAGE_SWEDISH_CODE);

        // Category
        $categories = SportCategory::get();
        $transformerService = new TransformerService();
        $transformedCategories = $transformerService->getTransformedData(new Collection($categories, new CategoriesTransformer($languageCode)));

        $response['translation'] = $translation;
        $response['categories'] = collect($transformedCategories)->sortBy('name')->values();

        return $response;
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
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
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
