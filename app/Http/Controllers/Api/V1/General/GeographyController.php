<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\StatusCode;
use App\Entities\Distance;
use App\Entities\Location;
use App\Http\Controllers\Controller;
use App\Services\Country\CountryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;

class GeographyController extends Controller
{
    public function index()
    {
        $data = [];

        try {
            $locale = App::currentLocale();
            $countryService = new CountryService();
            $countryList = $countryService->getCountryList($locale);

            $data['status'] = 'success';
            $data['distance'] = Distance::where('user_id', Auth::id())->first();
            $data['locations'] = Location::where('user_id', Auth::id())
                ->get(['id', 'lat', 'long', 'city', 'address', 'zip', 'cca2'])
                ->each(function ($item) use ($countryList) {
                    if ($item->cca2) {
                        $item->cca2 = $countryList[$item->cca2];
                    }
                });

            return response()->json($data, StatusCode::HTTP_OK);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }


    }
}
