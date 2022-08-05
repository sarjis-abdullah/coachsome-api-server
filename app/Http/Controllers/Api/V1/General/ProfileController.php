<?php

namespace App\Http\Controllers\Api\V1\General;

use App\Data\Constants;
use App\Data\StatusCode;
use App\Entities\Badge;
use App\Entities\Distance;
use App\Entities\Gallery;
use App\Entities\Language;
use App\Entities\Location;
use App\Entities\Package;
use App\Entities\Page;
use App\Entities\Profile;
use App\Entities\SocialAccount;
use App\Entities\SportCategory;
use App\Entities\SportTag;
use App\Entities\User;
use App\Entities\UserPage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tag\SportTagCollection;
use App\Services\CurrencyService;
use App\Services\Media\MediaService;
use App\Services\PackageService;
use App\Services\ProgressService;
use App\Services\Review\ReviewService;
use App\Services\StepService;
use App\Services\StorageService;
use App\Services\TransformerService;
use App\Transformers\Category\CategoriesTransformer;
use App\Transformers\Language\LanguagesTransformer;
use App\Transformers\Tag\TagsTransformer;
use App\Utils\CurrencyUtil;
use Coachsome\BaseReview\Repositories\BaseReviewRepository;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\ResourceAbstract;
use PeterColes\Countries\CountriesFacade;
use stdClass;

class ProfileController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request, $isonboarding=false)
    {
        try {
            $response = [];

            $languageCode = $request->header('Language-Code');

            $mediaService = new MediaService();

            $transformerService = new TransformerService();

            $user = Auth::user();

            if($isonboarding){
                $profile = Profile::where('user_id', $user->id)->where('is_onboarding', 1)->first();
            }else{
                $profile = $user->profile;
            }
            
            if (!$profile) {
                $profile = new Profile();
                $profile->user_id = $user->id;
                $profile->user_role = $isonboarding ? '' : $user->roles[0]->name;
                $profile->profile_name = $isonboarding ? '' : $user->first_name . " " . $user->last_name;
                $profile->is_onboarding = $isonboarding ? 1 : 0;
                $profile->save();
            }

            $response['countryList'] = [];

            $response['image'] = $mediaService->getImages($user,$isonboarding);
            $response['profile_name'] = $profile->profile_name ?? '';
            $response['about_me'] = $profile->about_me ?? '';
            $response['mobile_no'] = $profile->mobile_no ?? '';
            $response['mobile_code'] = $profile->mobile_code ?? '';
            $response['birth_day'] = $profile->birth_day ?? '';
            $response['social_acc_fb_link'] = $profile->social_acc_fb_link ?? '';
            $response['social_acc_twitter_link'] = $profile->social_acc_twitter_link ?? '';
            $response['social_acc_instagram_link'] = $profile->social_acc_instagram_link ?? '';
            $response['initial_image_content'] = strtoupper(mb_substr($user->first_name, 0, 1)) . strtoupper(mb_substr($user->last_name, 0, 1));

            // User Info
            $response['user'] = $user->info();

            // Language
            $languages = Language::get();
            $userLanguages =  $isonboarding ? $user->onBoardingLanguages : $user->languages ;
            $transformedLanguages = $transformerService->getTransformedData(new Collection($languages, new LanguagesTransformer($languageCode)));
            $transformedSelectedLanguages = $transformerService->getTransformedData(new Collection($userLanguages, new LanguagesTransformer($languageCode)));
            $response['languages'] = collect($transformedLanguages)->sortBy('name')->values();
            $response['selectedLanguages'] = collect($transformedSelectedLanguages)->values();

            // sportTags
            $userTags =  $isonboarding ? $user->onBoardingSportTags : $user->sportTags ;
            $response['selectedSportTags'] = new SportTagCollection($userTags);

            // Category
            $categories = SportCategory::get();
            $userCategories =  $isonboarding ? $user->onBoardingSportCategories : $user->sportCategories ;
            $transformedCategories = $transformerService->getTransformedData(new Collection($categories, new CategoriesTransformer($languageCode)));
            $transformedSelectedCategories = $transformerService->getTransformedData(new Collection($userCategories, new CategoriesTransformer($languageCode)));
            $response['selectedCategories'] = collect($transformedSelectedCategories)->values();
            $response['sport_category'] = collect($transformedCategories)->sortBy('name')->values();

            return $response;
        } catch (Exception $e) {
            return response(['message' => $e->getMessage()], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $response = [];
        $user = Auth::user();

        // Validation
        if (!empty($request->social_acc_fb_link)) {
            if (!preg_match('/(https:\/\/)?(www\.)?facebook\.com(.*?)/', $request->social_acc_fb_link)) {
                $response['status'] = 'error';
                $response['message'] = 'Facebook url is not correct.';
                $response['t_key'] = 'profile_validation_error_fb_is_not_correct';
                return response($response, Constants::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        if (!empty($request->social_acc_twitter_link)) {
            if (!preg_match('/(https:\/\/)?(www\.)?twitter\.com(.*?)/', $request->social_acc_twitter_link)) {
                $response['status'] = 'error';
                $response['message'] = 'Twitter url is not correct.';
                $response['t_key'] = 'profile_validation_error_twitter_is_not_correct';
                return response($response, Constants::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        if (!empty($request->social_acc_instagram_link)) {
            if (!preg_match('/(https:\/\/)?(www\.)?instagram\.com(.*?)/', $request->social_acc_instagram_link)) {
                $response['status'] = 'error';
                $response['message'] = 'Instagram url is not correct.';
                $response['t_key'] = 'profile_validation_error_instagram_is_not_correct';

                return response($response, Constants::HTTP_UNPROCESSABLE_ENTITY);
            }
        }

        if ($user) {

            $tagNameList = $request->sport_tag_list_name ?? [];
            $categoryIdList = $request->category_tag_list_id ?? [];
            $languageIdList = $request->language_tag_list_id ?? [];

            // Tag
            $tagsData = SportTag::where('user_id', $user->id);
            if($request->has('is_onboarding')){
                $tagsData->where('is_onboarding', 1);
            }else{
                $tagsData->where('user_role', $user->roles[0]->name);
            }
            $tagsData->delete();

            foreach ($tagNameList as $name) {
                $tag = new SportTag();
                $tag->user_id = $user->id;
                $tag->name = $name;
                $tag->user_role = $request->has('is_onboarding') ? '' : $user->roles[0]->name;
                $tag->is_onboarding = $request->has('is_onboarding') ? 1 : 0;
                $tag->save();
            }

            // Category
            $categoriesData = DB::table('sport_category_user')->where('user_id', $user->id);
            if($request->has('is_onboarding')){
                $categoriesData->where('is_onboarding', 1);
            }else{
                $categoriesData->where('user_role', $user->roles[0]->name);
            }
            $categoriesData->delete();

            foreach ($categoryIdList as $id) {
                DB::table('sport_category_user')->insert(
                    [
                        'user_id' => $user->id,
                        'user_role' => $request->has('is_onboarding') ? '' : $user->roles[0]->name,
                        'sport_category_id' => $id,
                        'is_onboarding'  => $request->has('is_onboarding') ? 1 : 0
                    ]
                );
            }

            // Language
            $languagesData = DB::table('language_user')->where('user_id', $user->id);
            if($request->has('is_onboarding')){
                $languagesData->where('is_onboarding', 1);
            }else{
                $languagesData->where('user_role', $user->roles[0]->name);
            }
            $languagesData->delete();

            foreach ($languageIdList as $id) {
                DB::table('language_user')->insert(
                    [
                        'user_id' => $user->id,
                        'user_role' => $request->has('is_onboarding') ? '' : $user->roles[0]->name,
                        'language_id' => $id,
                        'is_onboarding'  => $request->has('is_onboarding') ? 1 : 0
                    ]
                );
            }


            $profile = Profile::updateOrInsert(
                [
                    'user_id' => $user->id,
                    'user_role' => $request->has('is_onboarding') ? '' : $user->roles[0]->name,
                    'is_onboarding' => $request->has('is_onboarding') ? $request->is_onboarding : 0,
                ],
                [

                    'profile_name' => $request->profile_name ?? '',
                    'about_me' => $request->about_me ?? '',
                    'mobile_code' => $request->mobile_code ?? '',
                    'mobile_no' => $request->mobile_no ?? '',
                    'birth_day' => !empty($request->birth_day) ? date('Y-m-d', strtotime($request->birth_day)) : null,
                    'social_acc_fb_link' => $request->social_acc_fb_link ?? '',
                    'social_acc_twitter_link' => $request->social_acc_twitter_link ?? '',
                    'social_acc_instagram_link' => $request->social_acc_instagram_link ?? ''

                ]
            );

            if ($profile) {
                $progressService = new ProgressService();

                $response['status'] = 'success';
                $response['t_key'] = 'profile_save_success_message';
                $response['message'] = 'Updated successfully';
                $response['progress'] = $progressService->getUserProfilePageProgress($user);
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Something wrong';
            }
        }

        return $response;
    }


    public function uploadImages(Request $request)
    {
        try {
            $images = [];
            $user = Auth::user();
            $mediaService = new MediaService();
            if (!$user) {
                throw new Exception("User is not found");
            }
            $images['original'] = $request['original'];
            $images['square'] = $request['square'];
            $images['portrait'] = $request['portrait'];
            $images['landscape'] = $request['landscape'];
            $mediaService->storeImage($user, $images);

            return response()->json([
                'image' => $mediaService->getImages($user),
            ], StatusCode::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function destroyImages(Request $request)
    {
        try {
            $user = Auth::user();
            $mediaService = new MediaService();
            if (!$user) {
                throw new Exception("User is not found");
            }
            $mediaService->destroyAll($user);
            return response()->json([
                'image' => $mediaService->getImages($user),
            ], StatusCode::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function uploadImage(Request $request)
    {
        $response = [];
        // Validation
        if (!$request->has('profile_image')) {
            $response['status'] = 'error';
            $response['message'] = 'You need to select an image before you upload.';
            return response($response, Constants::HTTP_BAD_REQUEST);
        }

        $storageService = new StorageService();
        $file_data = $request['profile_image'];
        @list($type, $file_data) = explode(';', $file_data);
        @list(, $file_data) = explode(',', $file_data);

        $extension = explode("/", $type)[1];
        $prefix = 'id_' . Auth::id() . '_';
        $fileName = $prefix . time() . '.' . $extension;
        if ($file_data != "") {
            $user = Auth::user()->load('profile');
            if ($storageService->hasImage($user->profile->image)) {
                $storageService->destroyImage($user->profile->image);
            }
            $storageService->putImage($fileName, $file_data);
            $user->profile->image = $fileName;
            $user->profile->save();

            $response['status'] = 'success';
            $response['message'] = 'Image uploaded successfully';
            $response['image'] = $fileName;
            return response($response, Constants::HTTP_OK);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Something Wrong';
            return response($response, Constants::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function getByUserName($userName, Request $request, BaseReviewRepository $baseReviewRepository)
    {

        try {
            $requestedCurrencyCode = $request->header('Currency-Code');

            $ratingInfo = [];
            $packages = [];
            $links = [];
            $locations = [];
            $distance = null;
            $ratingInfo = null;
            $profileInfo = null;
            $userInfo = null;
            $verification = [
                'google' => false,
                'facebook' => false,
                'gmail' => false,
            ];

            $packageService = new PackageService();
            $currencyService = new CurrencyService();
            $mediaService = new MediaService();
            $reviewService = new ReviewService($baseReviewRepository);

            // User name formatting
            if ($userName) {
                $userName = str_replace("-", ".", $userName);
            }

            $user = User::where('user_name', $userName)->first();
            if (!$user) {
                throw new Exception('Sorry, user not found');
            }

            $profile = $user->profile ?? null;

            $fromCurrencyCode = $currencyService->getUserCurrency($user)->code;
            $toCurrencyCode = $requestedCurrencyCode ?? $currencyService->getDefaultBasedCurrency()->code;

            // User
            if ($user) {
                $userInfo = new stdClass;
                $userInfo->id = $user->id;
                $userInfo->userName = $user->user_name;
                $userInfo->firstName = $user->first_name;
                $userInfo->lastName = $user->last_name;
                $userInfo->email = $user->email;
                $userInfo->role = $user->roles[0]->name;
                $userInfo->badgeKey = Badge::find($user->badge_id)->key ?? "";

                $socialAccount = SocialAccount::where('user_id', $user->id)->first();
                if ($socialAccount) {
                    if ($socialAccount->provider_name == 'google') {
                        $verification['google'] = true;
                    }

                    if ($socialAccount->provider_name == 'facebook') {
                        $verification['facebook'] = true;
                    }
                } else {
                    $verification['gmail'] = $user->verified ? true : false;
                }
            }

            // Profile
            if ($profile) {
                $profileInfo = new \stdClass();
                $profileInfo->profile_name = $profile->profile_name;
                $images = $mediaService->getImages($user);
                if ($images['square']) {
                    $image = $images['square'];
                } else {
                    $image = $images['old'];
                }

                $profileInfo->image = $image;
                $profileInfo->metaImage = $images['landscape'] ?? '';
                $profileInfo->fb_link = $profile->social_acc_fb_link;
                $profileInfo->twitter_link = $profile->social_acc_twitter_link;
                $profileInfo->instagram_link = $profile->social_acc_instagram_link;
                $profileInfo->more_about = $profile->about_me;
                $profileInfo->tags = $user->sportTags->map(function ($item) {
                    $newItem = new \stdClass();
                    $newItem->id = $item->id;
                    $newItem->name = $item->name;
                    return $newItem;
                });
                $profileInfo->categories = $user->sportCategories()->get();
                $profileInfo->languages = $user->languages;
            }

            // Review
            $ratingInfo = new \stdClass();
            $ratingInfo->rating = $reviewService->overallStarRating($user);
            $ratingInfo->rating_count = $reviewService->totalReviewer($user);
            $ratingInfo->reviewers = $reviewService->reviewers($user);

            // Packages
            $packages = $user->packages()
                ->orderBy("order")
                ->where('status', 1)
                ->get()
                ->map(function ($item) use ($toCurrencyCode, $fromCurrencyCode, $packageService, $user) {
                    $discount = $item->details->discount ?? 0.00;
                    $originalPrice = $packageService->calculateOriginalPrice($user, $item);
                    $salePrice = $packageService->calculatePackageSalePrice($originalPrice, $discount);
                    $modifiedItem = new \stdClass();
                    $modifiedItem->id = $item->id;
                    $modifiedItem->details = $item->details;
                    $modifiedItem->originalPrice = CurrencyUtil::convert($originalPrice, $fromCurrencyCode, $toCurrencyCode);
                    $modifiedItem->salePrice = CurrencyUtil::convert($salePrice, $fromCurrencyCode, $toCurrencyCode);
                    $modifiedItem->category = $item->category;
                    return $modifiedItem;
                });


            $links = Gallery::where('user_id', $user->id)->get()->map(function ($item) use ($mediaService) {
                $url = $item->url;
                if ($item->type == 'image') {
                    $url = $mediaService->getGalleryImageUrl($item->file_name);
                }
                return [
                    'id' => $item->id,
                    'type' => $item->type,
                    'src' => $url,
                ];
            });

            $locations = Location::where('user_id', $user->id)->get(['id', 'lat', 'long', 'city', 'address', 'zip']);
            $distance = Distance::where('user_id', $user->id)->get(['far_away', 'unit', "is_offer_only_online"])->first();


            return response()->json([
                'packages' => $packages,
                'links' => $links,
                'locations' => $locations,
                'distance' => $distance,
                'rating_info' => $ratingInfo,
                'profile_info' => $profileInfo,
                'user_info' => $userInfo,
                'verification' => $verification
            ], StatusCode::HTTP_OK);
        } catch (Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    public function onboardCoach(Request $request){
        try {

            $user = User::find($request->user_id);


            $packages = Package::where('user_id', $user->id)->get();

            foreach($packages as $package){
                Package::where('id', $package->id)->update([ 'status' => 1]);
            }

            $locations = Location::where('user_id', $user->id)->where('is_onboarding', 1)->get();

            foreach($locations as $location){
                Location::where('id', $location->id)->update([ 'is_onboarding' => 0]);
            }

            $sport_categories =  DB::table('sport_category_user')->where('user_id', $user->id)->where('is_onboarding', 1)->get();
            
            foreach($sport_categories as $sport_category){
                DB::table('sport_category_user')->where('id', $sport_category->id)->update([ 'is_onboarding' => 0, 'user_role' => 'coach' ]);
            }

            $sport_tags = SportTag::where('user_id', $user->id)->where('is_onboarding', 1)->get();
            
            foreach($sport_tags as $sport_tag){
                SportTag::where('id', $sport_tag->id)->update([ 'is_onboarding' => 0, 'user_role' => 'coach' ]);
            }

            $languages =  DB::table('language_user')->where('user_id', $user->id)->where('is_onboarding', 1)->get();
            
            foreach($languages as $language){
                DB::table('language_user')->where('id', $language->id)->update([ 'is_onboarding' => 0, 'user_role' => 'coach' ]);
            }

            Profile::where('user_id', $user->id)->where('is_onboarding', 1)->update([ 'is_onboarding' => 0, 'user_role' => 'coach'  ]);
            return response()->json([
                'message'=>'Successfully switched into coach.'
            ], StatusCode::HTTP_OK);
        } catch (\Exception $e) {
            return response()->json([
                'message'=>$e->getMessage()
            ], StatusCode::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
