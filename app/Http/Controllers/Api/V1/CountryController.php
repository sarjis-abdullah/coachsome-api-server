<?php


namespace App\Http\Controllers\Api\V1;

use App\Data\StatusCode;
use App\Http\Controllers\Controller;
use App\Services\Country\CountryService;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use PeterColes\Countries\CountriesFacade;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $languageCode = App::currentLocale();

            $authUser = Auth::user();

            if (!$authUser) {
                throw new \Exception('Sorry! user not found.');
            }

            $allCountryList = [];
            $countryService = new CountryService();

            $countryList =$countryService->getCountryList($languageCode);
            foreach ($countryList as $key => $item) {
                $newCountry = new \stdClass();
                $newCountry->code = $key;
                $newCountry->displayName = $item;
                $allCountryList[] = $newCountry;
            }

            return response()->json([
                'data' => $allCountryList,
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'errors' => [$e->getMessage()]
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
