<?php


namespace App\Services;


use App\Data\Constants;
use App\Entities\Page;
use App\Entities\Step;
use App\Entities\UserDefWeekAvailability;
use App\Entities\UserPage;
use Illuminate\Support\Facades\Log;

class StepService
{
    public function manageUserPageStep($user, $page)
    {
        if ($user && $page) {
            // Profile
            if ($page->key == Constants::PAGE_KEY_PROFILE) {
                $profile = $user->profile ?? null;
                $stepCount = 0;
                $stepIdList = [];
                if ($profile) {
                    // Profile Image
                    if ($profile->image) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_PICTURE);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // Profile name
                    if ($profile->profile_name) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_NAME);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // About You
                    if (!empty($profile->about_me)) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_ABOUT_YOU);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // Mobile No
                    if ($profile->mobile_no) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_PHONE_NUMBER);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // Birthday
                    if ($profile->birth_day) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_BIRTHDAY);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // Language
                    if ($user->languages->first()) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_LANGUAGE);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // Category
                    if ($user->sportCategories->first()) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_CATEGORY);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // Tag
                    if ($user->sportTags->first()) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_TAG);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // Facebook Link
                    if ($profile->social_acc_fb_link) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_FACEBOOK_LINK);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // Twitter Link
                    if ($profile->social_acc_twitter_link) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_TWITTER_LINK);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // Instagram Link
                    if ($profile->social_acc_instagram_link) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_INSTAGRAM_LINK);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }

                    // Personalized Url is depend on user name
                    // So no need to check personalized field
                    if ($user->user_name) {
                        $step = Step::getByKey(Constants::STEP_KEY_PROFILE_PERSONALIZED_URL);
                        if ($step) {
                            $stepCount++;
                            $stepIdList[] = $step->id;
                        }
                    }
                }
            }

            // Packages
            if ($page->key == Constants::PAGE_KEY_PACKAGE) {
                $packages = $user->packages ?? null;
                $stepCount = 0;
                $stepIdList = [];

                if ($user->ownPackageSetting) {
                    $step = Step::getByKey(Constants::STEP_KEY_PACKAGE_HOURLY_RATE);
                    if ($step) {
                        $stepCount++;
                        $stepIdList[] = $step->id;
                    }
                }

                if ($packages->count() > 0) {
                    $step = Step::getByKey(Constants::STEP_KEY_PACKAGE_CREATED);
                    if ($step) {
                        $stepCount++;
                        $stepIdList[] = $step->id;
                    }
                }


            }


            // Image And Video
            if ($page->key == Constants::PAGE_KEY_IMAGE_VIDEO) {
                $galleries = $user->galleries ?? null;
                $stepCount = 0;
                $stepIdList = [];

                if ($galleries->count() > 0) {
                    $galleryImage = $galleries->where('type', Constants::GALLERY_ASSET_TYPE_IMAGE)->first();
                    $galleryVideo = $galleries->where('type', Constants::GALLERY_ASSET_TYPE_VIDEO)->first();

                    $step = Step::getByKey(Constants::STEP_KEY_GALLERY_IMAGE);
                    if ($step && $galleryImage) {
                        $stepCount++;
                        $stepIdList[] = $step->id;
                    }

                    $step = Step::getByKey(Constants::STEP_KEY_GALLERY_VIDEO);
                    if ($step && $galleryVideo) {
                        $stepCount++;
                        $stepIdList[] = $step->id;
                    }
                }

                Log::info($galleryVideo);
            }

            // Geography
            if ($page->key == Constants::PAGE_KEY_GEOGRAPHY) {
                $stepCount = 0;
                $stepIdList = [];

                if ($user->locations->count() > 0) {
                    $step = Step::getByKey(Constants::STEP_KEY_GEOGRAPHY_LOCATION);
                    if ($step) {
                        $stepCount++;
                        $stepIdList[] = $step->id;
                    }
                }

                if ($user->distance) {
                    $step = Step::getByKey(Constants::STEP_KEY_GEOGRAPHY_DISTANCE);
                    if ($step) {
                        $stepCount++;
                        $stepIdList[] = $step->id;
                    }
                }
            }

            // Availability
            if ($page->key == Constants::PAGE_KEY_AVAILABILITY) {
                $stepCount = 0;
                $stepIdList = [];
                $userDefaultAvailability = $user->defaultAvailability;
                if ($userDefaultAvailability) {
                    $step = Step::getByKey(Constants::STEP_KEY_AVAILABILITY_DEFAULT_SCHEDULE);
                    if ($step) {
                        $stepCount++;
                        $stepIdList[] = $step->id;
                    }
                }
            }

            // Reviews
            if ($page->key == Constants::PAGE_KEY_REVIEWS) {
                $stepCount = 0;
                $stepIdList = [];

                if ($user->reviews()->count() > 0) {
                    $step = Step::getByKey(Constants::STEP_KEY_REVIEW_IMPORT);
                    if ($step) {
                        $stepCount++;
                        $stepIdList[] = $step->id;
                    }
                }

            }

            // User Steps information store
            $mUserPage = new UserPage();
            $userProfilePageStep = $mUserPage->getUserPage($user, $page) ?? new UserPage();
            $userProfilePageStep->user_id = $user->id;
            $userProfilePageStep->page_id = $page->id;
            $userProfilePageStep->step_count = $stepCount;
            $userProfilePageStep->step_id_list = json_encode($stepIdList);
            $userProfilePageStep->save();
        }


    }

    public function getPageStepCountInPercent($user, $page)
    {
        $stepInPercent = 0;

        $mUserPage = new UserPage();
        $userPageStep = $mUserPage->getUserPage($user, $page);

        $numberOfTotalPageStep = $page->steps->count();
        $userStepCount = $userPageStep ? $userPageStep->step_count : 0;
        $stepInPercent = $userStepCount > 0 ? round($userStepCount / $numberOfTotalPageStep * 100) : 0;
        return $stepInPercent;

    }

    public function manage($user, $pageKey)
    {
        $stepService = new StepService();
        $page = Page::getByKey($pageKey);
        $stepService->manageUserPageStep($user, $page);
    }

    public function stepInPercent($user, $pageKey)
    {
        $stepInPercent = 0;

        $mUserPage = new UserPage();
        $page = Page::getByKey($pageKey);
        $userPage = $mUserPage->getUserPage($user, $page);

        $numberOfTotalPageStep = $page->steps->count();
        $userStepCount = $userPage ? $userPage->step_count : 0;
        $stepInPercent = $userStepCount > 0 ? round($userStepCount / $numberOfTotalPageStep * 100) : 0;

        return $stepInPercent;
    }

}
