<?php

namespace App\Http\Controllers\Api\V1\Athlete;

use App\Data\Constants;
use App\Entities\Distance;
use App\Entities\Gallery;
use App\Entities\Language;
use App\Entities\Location;
use App\Entities\Page;
use App\Entities\Profile;
use App\Entities\SportCategory;
use App\Entities\SportTag;
use App\Entities\User;
use App\Entities\UserPage;
use App\Http\Controllers\Controller;
use App\Http\Resources\Tag\SportTagCollection;
use App\Services\Media\MediaService;
use App\Services\PackageService;
use App\Services\ProgressService;
use App\Services\StepService;
use App\Services\StorageService;
use App\Services\TransformerService;
use App\Transformers\Category\CategoriesTransformer;
use App\Transformers\Language\LanguagesTransformer;
use App\Transformers\Tag\TagsTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use League\Fractal\Resource\Collection;


class ProfileController extends Controller
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

        $storageService = new StorageService();
        $transformerService = new TransformerService();
        $mediaService = new MediaService();

        $user = Auth::user()->load(['profile', 'sportCategories']);
        $images = $mediaService->getImages($user);

        $profile = $user->profile;
        if(!$profile){
            $profile = new Profile();
            $profile->user_id = $user->id;
            $profile->user_role = $user->roles[0]->name;
            $profile->profile_name = $user->first_name." ".$user->last_name;
            $profile->save();
        }
        $response['image'] = $images['square'] ? $images['square'] : $images['old'];
        $response['profile_name'] = $profile->profile_name ?? '';
        $response['about_me'] = $profile->about_me ?? '';
        $response['mobile_no'] = $profile->mobile_no ?? '';
        $response['mobile_code'] = $profile->mobile_code ?? '';
        $response['birth_day'] = $profile->birth_day ?? '';
        $response['social_acc_fb_link'] = $profile->social_acc_fb_link ?? '';
        $response['social_acc_twitter_link'] = $profile->social_acc_twitter_link ?? '';
        $response['social_acc_instagram_link'] = $profile->social_acc_instagram_link ?? '';
        $response['initial_image_content'] = strtoupper(substr($user->first_name, 0, 1)) . strtoupper(substr($user->last_name, 0, 1));

        // User Info
        $response['user'] = $user->info();
        $response['dd'] = $user->info();

        // Language
        $languages= Language::get();
        $transformedLanguages = $transformerService->getTransformedData(new Collection($languages, new LanguagesTransformer($languageCode)));
        $transformedSelectedLanguages = $transformerService->getTransformedData(new Collection($user->languages, new LanguagesTransformer($languageCode)));
        $response['languages'] = collect($transformedLanguages)->sortBy('name')->values();
        $response['selectedLanguages'] = collect($transformedSelectedLanguages)->sortBy('name')->values();

        // Tag
        $response['selectedSportTags'] = new SportTagCollection($user->sportTags);

        // Category
        $categories = SportCategory::get();
        $transformedCategories = $transformerService->getTransformedData(new Collection($categories, new CategoriesTransformer($languageCode)));
        $transformedSelectedCategories = $transformerService->getTransformedData(new Collection($user->sportCategories, new CategoriesTransformer($languageCode)));
        $response['selectedCategories'] = collect($transformedSelectedCategories)->sortBy('name')->values();
        $response['sport_category'] = collect($transformedCategories)->sortBy('name')->values();

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
            $user->sportTags()->delete();
            foreach ($tagNameList as $name) {
                $tag = new SportTag();
                $tag->user_id = $user->id;
                $tag->name = $name;
                $tag->save();
            }

            // Category
            $user->sportCategories()->sync($categoryIdList);

            // Language
            $user->languages()->sync($languageIdList);

            $profile = $user->profile;
            $profile->user_id = $user->id;
            $profile->profile_name = $request->profile_name ?? '';
            $profile->about_me = $request->about_me ?? '';
            $profile->mobile_code = $request->mobile_code ?? '';
            $profile->mobile_no = $request->mobile_no ?? '';
            $profile->birth_day = !empty($request->birth_day) ? date('Y-m-d', strtotime($request->birth_day)) : null;
            $profile->social_acc_fb_link = $request->social_acc_fb_link ?? '';
            $profile->social_acc_twitter_link = $request->social_acc_twitter_link ?? '';
            $profile->social_acc_instagram_link = $request->social_acc_instagram_link ?? '';
            if ($profile->save()) {
                $progressService= new ProgressService();

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
            return  response($response, Constants::HTTP_OK);
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Something Wrong';
            return  response($response, Constants::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function getByUserName($userName, Request $request)
    {
        $response = [];

        $storageService = new StorageService();
        $packageService = new PackageService();

        $user = User::where('user_name', $userName)->with(['sportTags'])->first();

        $profile = $user->profile ?? null;

        $review = $user->reviews->where('provider', 'facebook')->first();

        // Profile
        if ($profile) {
            $profileInfo = new \stdClass();
            $profileInfo->profile_name = $profile->profile_name;
            $image = $profile->image && $storageService->hasImage($profile->image)
                ? $profile->image
                : "";
            $profileInfo->image = $image;
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
            $profileInfo->categories = $user->sportCategories()->orderBy('name')->get();
            $profileInfo->languages = $user->languages;
            $response['profile_info'] = $profileInfo;
        }

        // Review
        if ($review) {
            $ratingInfo = new \stdClass();
            $ratingInfo->rating = $review->overall_star_rating;
            $ratingInfo->rating_count = $review->rating_count;
            $ratingInfo->reviewers = json_decode($review->reviewers, true);
            $response['rating_info'] = $ratingInfo;
        }

        // Packages
        $packages = $user->packages()->where('status', 1)->get()->map(function ($item) use ($packageService) {
            $modifiedItem = new \stdClass();
            $modifiedItem->id = $item->id;
            $modifiedItem->details = $item->details;
            $modifiedItem->originalPrice = $item->details->price ?? 0.00;
            $modifiedItem->salePrice = $item->details ? $packageService->calculatePackageSalePrice($item->details->price, $item->details->discount) : 0.00;
            $modifiedItem->category = $item->category;
            return $modifiedItem;
        });

        $response['packages'] = $packages;

        $response['links'] = Gallery::where('user_id', $user->id)->get(['id', 'type', 'url', 'file_name'])->toArray();

        $response['locations'] = Location::where('user_id', $user->id)->get(['id', 'lat', 'long', 'city', 'address', 'zip']);

        $response['distance'] = Distance::where('user_id', $user->id)->get(['far_away', 'unit'])->first();

        return $response;
    }
}
