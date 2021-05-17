<?php


namespace App\Http\Controllers\Api\V1\Athlete;


use App\Data\Constants;
use App\Entities\Currency;
use App\Entities\SportCategory;
use App\Entities\User;
use App\Helpers\Util;
use App\Services\PackageService;
use App\Services\SearchValueService;
use App\Services\StorageService;
use App\Services\TranslationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class PackageController
{
    public function explore(Request $request)
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


        $authUser = auth('api')->user();
        $storageService = new StorageService();
        $mPackageService = new PackageService();
        $mTranslationService = new TranslationService();

        $min = $mPackageService->getMinRange();
        $max = $mPackageService->getMaxRange();
        if (!$minRange && !$maxRange) {
            $minRange = $min;
            $maxRange = $max;
        }


        // Find user id by radius filtering
        $radiusFilterUserIdList = [];
        if (!empty($originLocation) && !empty($originLat) && !empty($originLon) && !empty($distance)) {
            $originLat = 55.05697191;
            $originLon = 10.57665367;
            $distance = 2000;

            $sql = "SELECT *, (((acos(sin((:orig_lat_1*pi()/180)) * sin((lat*pi()/180))+cos((:orig_lat_2*pi()/180))*cos((lat*pi()/180))*cos(((:orig_lon - locations.long)*pi()/180))))*180/pi())*60*1.1515*1609.344) as distance
                FROM locations
                HAVING distance < :dist;
            ";

            $radiusFilterQueryResult = DB::select(
                $sql
                ,
                ["orig_lat_1" => $originLat, "orig_lat_2" => $originLat, "orig_lon" => $originLon, 'dist' => $distance]
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
            $categoryIdList = array_merge($categoryIdList,$findCategories);
            if(empty($categoryIdList)){
                $categoryIdList= [INF];
            }
        }


        // Store search value if the user is authenticated
        if($authUser){
            $searchValueService = new SearchValueService();
            $searchValueService->createCategory($authUser, $categoryIdList);
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
        $userQuery = User::query()->with([
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
        $userQuery->whereHas('packages', function($q){
            $q->where('status','=', 1);
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

        // Location
        if ($originLocation) {
            $userQuery->whereHas('locations', function ($q) use ($originLocation) {
                $q->where('address', 'LIKE', '%' . $originLocation . '%');
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


        $mCurrency = new Currency();
        $requestedCurrency = $mCurrency->getByCode($requestedCurrencyCode);

        $response['coaches'] = $userQuery->paginate(50)->map(function ($item) use ($storageService, $mCurrency, $requestedCurrency) {
            $coach = new \stdClass();
            $coach->name = $item->profile->profile_name ?? '';

            // Image
            $profile = $item->profile;
            $coach->image = null;
            if ($profile) {
                $coach->image = $storageService->hasImage($profile->image) ? $profile->image : '';
            }

            // Location
            $coach->locations = $item->locations;
            if ($coach->locations->count() > 0) {
                $coach->locations->map(function ($location) use ($coach) {
                    $location->userImage = $coach->image;
                });
            }

            // Price
            $coach->price = $item->ownPackageSetting
                ? Util::calculateAmountByUserBasedCurrency($item->ownPackageSetting->hourly_rate, $mCurrency->getUserBasedCurrency($item), $requestedCurrency)
                : 0.00;

            // Review
            $faceBookReview = $item->reviews->where('provider', 'facebook')->first();
            $coach->rating = $faceBookReview
                ? $faceBookReview->overall_star_rating
                : 0;
            $coach->countReview = $faceBookReview ? $faceBookReview->rating_count : 0;

            // User name
            $coach->userName = $item->user_name ?? '';

            // Categories
            $coach->categories = $item->sportCategories->map->only(['id', 'name', 't_key'])->toArray();

            return $coach;

        })->take(3);

        $response['minRange'] = $minRange;
        $response['maxRange'] = $maxRange;

        $response['min'] = $min;
        $response['max'] = $max;

        return $response;
    }
}
