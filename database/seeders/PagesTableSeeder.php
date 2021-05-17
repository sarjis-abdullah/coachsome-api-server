<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PagesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id'=>1, 'name'=>'Profile','key'=>\App\Data\Constants::PAGE_KEY_PROFILE],
            ['id'=>2, 'name'=>'Package','key'=>\App\Data\Constants::PAGE_KEY_PACKAGE],
            ['id'=>3, 'name'=>'Image And Video','key'=>\App\Data\Constants::PAGE_KEY_IMAGE_VIDEO],
            ['id'=>4, 'name'=>'Geography','key'=>\App\Data\Constants::PAGE_KEY_GEOGRAPHY],
            ['id'=>5, 'name'=>'Availability','key'=>\App\Data\Constants::PAGE_KEY_AVAILABILITY],
            ['id'=>6, 'name'=>'Reviews','key'=>\App\Data\Constants::PAGE_KEY_REVIEWS],
            ['id'=>7, 'name'=>'Translation','key'=>\App\Data\Constants::PAGE_KEY_TRANSLATION],
        ];

        \App\Entities\Page::insert($data);
    }
}
