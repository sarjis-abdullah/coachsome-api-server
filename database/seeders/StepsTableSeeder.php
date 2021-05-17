<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StepsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            // Profile Steps
            [ 'id'=>1, 'name'=>'Profile Picture','key' => \App\Data\Constants::STEP_KEY_PROFILE_PICTURE,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>2, 'name'=>'Profile Name','key' => \App\Data\Constants::STEP_KEY_PROFILE_NAME,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>3, 'name'=>'About You','key' => \App\Data\Constants::STEP_KEY_PROFILE_ABOUT_YOU,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>4, 'name'=>'Phone Number','key' => \App\Data\Constants::STEP_KEY_PROFILE_PHONE_NUMBER,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>5, 'name'=>'Language','key' => \App\Data\Constants::STEP_KEY_PROFILE_LANGUAGE,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>6, 'name'=>'Birthday','key' => \App\Data\Constants::STEP_KEY_PROFILE_BIRTHDAY,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>7, 'name'=>'Category','key' => \App\Data\Constants::STEP_KEY_PROFILE_CATEGORY,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>8, 'name'=>'Tag','key' => \App\Data\Constants::STEP_KEY_PROFILE_TAG,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>9, 'name'=>'Facebook Link','key' => \App\Data\Constants::STEP_KEY_PROFILE_FACEBOOK_LINK,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>10, 'name'=>'Twitter Link','key' => \App\Data\Constants::STEP_KEY_PROFILE_TWITTER_LINK,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>11, 'name'=>'Instagram Link','key' => \App\Data\Constants::STEP_KEY_PROFILE_INSTAGRAM_LINK,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>12, 'name'=>'Personalized Url','key' => \App\Data\Constants::STEP_KEY_PROFILE_PERSONALIZED_URL,'page_id'=> \App\Data\Constants::PAGE_ID_PROFILE ],
            [ 'id'=>13, 'name'=>'Package Hourly Rate','key' => \App\Data\Constants::STEP_KEY_PACKAGE_HOURLY_RATE,'page_id'=> \App\Data\Constants::PAGE_ID_PACKAGE ],
            [ 'id'=>14, 'name'=>'Package Created','key' => \App\Data\Constants::STEP_KEY_PACKAGE_CREATED,'page_id'=> \App\Data\Constants::PAGE_ID_PACKAGE ],
            [ 'id'=>15, 'name'=>'Image','key' => \App\Data\Constants::STEP_KEY_GALLERY_IMAGE,'page_id'=> \App\Data\Constants::PAGE_ID_IMAGE_VIDEO ],
            [ 'id'=>16, 'name'=>'Video','key' => \App\Data\Constants::STEP_KEY_GALLERY_VIDEO,'page_id'=> \App\Data\Constants::PAGE_ID_IMAGE_VIDEO ],
            [ 'id'=>17, 'name'=>'Geography Distance','key' => \App\Data\Constants::STEP_KEY_GEOGRAPHY_DISTANCE,'page_id'=> \App\Data\Constants::PAGE_ID_GEOGRAPHY ],
            [ 'id'=>18, 'name'=>'Geography Location','key' => \App\Data\Constants::STEP_KEY_GEOGRAPHY_LOCATION,'page_id'=> \App\Data\Constants::PAGE_ID_GEOGRAPHY ],
            [ 'id'=>19, 'name'=>'Availability','key' => \App\Data\Constants::STEP_KEY_AVAILABILITY_DEFAULT_SCHEDULE,'page_id'=> \App\Data\Constants::PAGE_ID_AVAILABILITY ],
            [ 'id'=>20, 'name'=>'Reviews','key' => \App\Data\Constants::STEP_KEY_REVIEW_IMPORT,'page_id'=> \App\Data\Constants::PAGE_ID_REVIEWS ],
        ];

        \App\Entities\Step::insert($data);
    }
}
