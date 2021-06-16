<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Constants;
use App\Entities\Location;
use App\Entities\UserSetting;
use App\Http\Resources\Category\SportCategoryResource;
use App\Services\CurrencyService;
use App\Services\Locale\LocaleService;
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
    public function index(Request $request)
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

        return response()->json($data);
    }

    public function getHourlyRatingUsers(Request $request, BaseReviewRepository $baseReviewRepository)
    {
        // Request value
        $cityName = $request->query('cityName');
        $categoryName = $request->query('categoryName');
        $categoryIdList = $request->query('categoryId') ? explode(",", $request->query('categoryId')) : [];
        $date = date('Y-m-d', strtotime($request->date)) ?? '';
        $minRange = $request->hourlyPriceMinRange;
        $maxRange = $request->hourlyPriceMaxRange;
        $requestedCurrencyCode = $request->header('Currency-Code');
        $requestedLanguageCode = $request->header('Language-Code');
        $originLocation = $request->originLocation ?? '';
        $originLat = $request->originLat ?? null;
        $originLon = $request->originLong ?? null;
        $distance = $request->distance ?? null;
        $countryCode = $request->countryCode ?? null;


        $authUser = auth('api')->user();
        $storageService = new StorageService();
        $mPackageService = new PackageService();
        $mTranslationService = new TranslationService();
        $localeService = new LocaleService();
        $reviewService = new ReviewService($baseReviewRepository);


        $min = $mPackageService->getMinRange();
        $max = $mPackageService->getMaxRange();
        if (!$minRange && !$maxRange) {
            $minRange = $mPackageService->getMinRange();
            $maxRange = $mPackageService->getMaxRange();
        }

        // Find user id by radius filtering
        $radiusFilterUserIdList = [];
        if (!empty($originLocation) && !empty($originLat) && !empty($originLon) && !empty($distance)) {

            $sql = "SELECT *, (((acos(sin((:orig_lat_1*pi()/180)) * sin((lat*pi()/180))+cos((:orig_lat_2*pi()/180))*cos((lat*pi()/180))*cos(((:orig_lon - locations.long)*pi()/180))))*180/pi())*60*1.1515*1609.344) as distance
                FROM locations
                HAVING distance < :dist;
            ";

            // Calculate distance in meeter
            $radiusFilterQueryResult = DB::select(
                $sql
                ,
                ["orig_lat_1" => $originLat, "orig_lat_2" => $originLat, "orig_lon" => $originLon, 'dist' => $distance * 1000]
            );
            $radiusFilterUserIdList = collect($radiusFilterQueryResult)->unique('user_id')->pluck('user_id')->toArray();
        }


        // Find Sport category by name
        // Set infinity because we cant find anything.
        // When search value to db its not find anything.
        // It is little tricky. Is not it?
        // There has a requirement that search by category name dont show anything if no category found.
        if ($categoryName) {
            $translations = $mTranslationService->getKeyByLanguageCode($requestedLanguageCode);
            $findCategories = SportCategory::get()->filter(function ($item) use ($translations, $categoryName) {
                $item->name = $translations[$item->t_key];
                if (stristr($item->name, $categoryName)) {
                    return true;
                } else {
                    return false;
                }
            })->pluck('id')->toArray();
            $categoryIdList = array_merge($categoryIdList, $findCategories);
            if (empty($categoryIdList)) {
                $categoryIdList = [INF];
            }
        }


        // Store search value if the user is authenticated
        if ($authUser) {
            $searchValueService = new SearchValueService();
            $searchValueService->createCategory($authUser, $categoryIdList);
        }

        if (!$countryCode) {
            $countryCode = $localeService->getUserCountryCodeFromSetting(Auth::guard('api')->user())
                ?? $localeService->currentCountryCode();
        }


        /*
         * Active Rules
         * A coach profile can only be active in the marketplace if they have:
         * Profile
         *  - Profile picture
         *  - Profile name
         *  - An “About” text with a minimum of 150 characters
         *  - Phone number
         *  - Minimum one language
         *  - Minimum one category
         *  - Minimum three tags
         * Packages
         *  - An hourly rate
         *  - At least one package
         * Geography
         *  - A least one location
         */

        // Filter initiating
        $userQuery = User::query()
            ->distinct()
            ->with([
                'profile',
                'ownPackageSetting',
                'locations',
                'reviews',
                'availabilities',
                'sportCategories',
                'roles',
                'languages',
                'sportTags',
                'sportCategories'
            ]);

        // Active Rules
        $userQuery->where('activity_status_id', Constants::ACTIVITY_STATUS_ID_ACTIVE);
        $userQuery->whereHas('profile', function ($q) {
            $q->where('image', '!=', null);
            $q->where('profile_name', '!=', null);
            $q->where('about_me', '!=', null);
            $q->where('mobile_no', '!=', null);
            $q->where('mobile_code', '!=', null);
        });
        $userQuery->has('sportTags', '>=', 3);
        $userQuery->has('languages');
        $userQuery->has('sportCategories');
        $userQuery->has('locations');
        $userQuery->whereHas('packages', function ($q) {
            $q->where('status', '=', 1);
        });


        // Only coach
        if (Constants::ROLE_ID_COACH) {
            $userQuery->whereRoleIs(Constants::ROLE_KEY_COACH);
        }

        // Sport category id
        if ($categoryIdList) {
            $userQuery->whereHas('sportCategories', function ($q) use ($categoryIdList) {
                $q->whereIn("sport_category_id", $categoryIdList);
            });
        }

        // City name
        if ($cityName) {
            $userQuery->whereHas('locations', function ($q) use ($cityName) {
                $q->where('city', 'LIKE', '%' . $cityName . '%');
            });
        }

        // Radius
        if (!empty($radiusFilterUserIdList)) {
            $userQuery->whereIn('id', $radiusFilterUserIdList);
        }

        // Hourly rate range
        $userQuery->whereHas('ownPackageSetting', function ($q) use ($minRange, $maxRange) {
            $q->where("hourly_rate", ">=", $minRange);
            $q->where("hourly_rate", "<=", $maxRange);
        });

        // Country
        if ($countryCode) {
            $userQuery->whereHas('locations', function ($q) use ($countryCode) {
                $q->where('cca2', $countryCode);
            });
        }


        $mCurrency = new Currency();
        $packageService = new PackageService();
        $currencyService = new CurrencyService();
        $mediaService = new MediaService();

        $requestedCurrency = $mCurrency->getByCode($requestedCurrencyCode);


        // Ranking wise sorting
        $userQuery->orderBy("ranking", "DESC");

        // Order by id for removing duplicate model in the collection
        $response['coaches'] = $userQuery
            ->orderBy('id', 'asc')
            ->paginate(10)
            ->map(function ($item)
            use ($mediaService, $reviewService, $mCurrency, $requestedCurrency, $packageService, $currencyService) {
                $coach = new \stdClass();
                $coach->name = $item->profile->profile_name ?? '';

                // Image
                $images = $mediaService->getImages($item);
                if ($images['square']) {
                    $coach->image = $images['square'];
                    $coach->imageMap = $images['square'];
                } else {
                    $coach->image = $images['old'];
                    $coach->imageMap = $images['old'];

                }

                // Location
                $coach->locations = $item->locations;
                if ($coach->locations->count() > 0) {
                    $coach->locations->map(function ($location) use ($coach) {
                        $location->userImage = $coach->imageMap;
                    });
                }


                // Find minimum price package
                $price = null;
                foreach ($item->packages->where('status', 1) as $package) {
                    $originalPrice = $packageService->calculateOriginalPrice($item, $package);
                    if ($price) {
                        if ($price > $originalPrice) {
                            $price = $originalPrice;
                        }
                    } else {
                        $price = $originalPrice;
                    }
                }

                // Price
                $coach->price = $currencyService->convert(
                    $price,
                    $currencyService->getDefaultBasedCurrency()->code,
                    $requestedCurrency->code
                );

                // Review
                $coach->rating = $reviewService->overallStarRating($item);
                $coach->countReview = $reviewService->totalReviewer($item);

                // User name
                $coach->userName = $item->user_name ?? '';

                // Categories
                $coach->categories = $item->sportCategories->map->only(['id', 'name', 't_key'])->toArray();

                return $coach;

            });

        $response['minRange'] = $minRange;
        $response['maxRange'] = $maxRange;
        $response['min'] = $min;
        $response['max'] = $max;

        // Searched country name
        $response['searchedCountryName'] = $localeService->countryNameByCountryCode($countryCode);

        $response['coachInCountries'] = UserSetting::whereNotNull('cca2')
            ->groupBy('cca2')
            ->select('cca2')
            ->get()
            ->toArray();


        return $response;
    }
}
