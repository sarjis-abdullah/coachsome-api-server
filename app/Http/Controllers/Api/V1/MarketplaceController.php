<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\Location;
use App\Entities\UserSetting;
use App\Http\Resources\Category\SportCategoryResource;
use App\Services\CurrencyService;
use App\Services\Locale\LocaleService;
use App\Services\Marketplace\MarketplaceService;
use App\Services\Media\MediaService;
use App\Services\PackageService;
use App\Entities\Currency;
use App\Entities\Package;
use App\Entities\SportCategory;
use App\Entities\User;
use App\Http\Controllers\Controller;
use App\Helpers\Util;
use App\Services\Review\ReviewService;
use App\Services\SearchValueService;
use App\Services\StorageService;
use App\Services\TranslationService;
use Coachsome\BaseReview\Repositories\BaseReviewRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use PeterColes\Countries\CountriesFacade;

class MarketplaceController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, BaseReviewRepository $baseReviewRepository)
    {
        if ($request->all()) {
            $marketplaceService = new MarketplaceService();
            return response($marketplaceService->filter($request, $baseReviewRepository));
        } else {
            return response($this->initial($request));
        }

    }

    private function initial($request)
    {
        $data = [
            'categories' => [],
            'min' => 0,
            'max' => 0,
            'minRange' => 0,
            'maxRange' => 0,
            'countries' => [],
        ];

        $localeService = new LocaleService();
        $mPackageService = new PackageService();
        $data['countryCode'] = $localeService->getUserCountryCodeFromSetting(\Auth::guard('api')->user()) ?? $localeService->currentCountryCode();
        $data['categories'] = SportCategoryResource::collection(SportCategory::get());
        $data['min'] = $mPackageService->getMinRange();
        $data['max'] = $mPackageService->getMaxRange();
        $data['minRange'] = $mPackageService->getMinRange();
        $data['maxRange'] = $mPackageService->getMaxRange();
        $data['coachInCountries'] = UserSetting::whereNotNull('cca2')
            ->groupBy('cca2')
            ->select('cca2')
            ->get()->toArray();

        $countryList = json_decode(CountriesFacade::lookup($localeService->currentLocale()), true);
        foreach ($countryList as $key => $item) {
            $newCountry = new \stdClass();
            $newCountry->code = $key;
            $newCountry->displayName = $item;
            $data['countries'][] = $newCountry;
        }

        return $data;

    }
}
