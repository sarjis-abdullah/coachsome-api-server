<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SportCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $sports = [
            ['id' => 1, 'name' => 'American Football', 't_key' => 'cat_american_football', 'priority' => 0, 'image'=>''],
            ['id' => 2, 'name' => 'Athletics', 't_key' => 'cat_athletics', 'priority' => 0, 'image'=>''],
            ['id' => 3, 'name' => 'Badminton', 't_key' => 'cat_badminton', 'priority' => 3, 'image'=>'track.png'],
            ['id' => 4, 'name' => 'Baseball', 't_key' => 'cat_baseball', 'priority]' => 0, 'image'=>''],
            ['id' => 5, 'name' => 'Basketball', 't_key' => 'cat_basketball', 'priority' => 2, 'image'=>'powerlift.png'],
            ['id' => 6, 'name' => 'Biking', 't_key' => 'cat_biking', 'priority' => 0, 'image'=>''],
            ['id' => 7, 'name' => 'Cheerleading', 't_key' => 'cat_cheerleading', 'priority' => 0, 'image'=>''],
            ['id' => 8, 'name' => 'Chiropractor', 't_key' => 'cat_chiropractor', 'priority' => 0, 'image'=>''],
            ['id' => 9, 'name' => 'Crossfit', 't_key' => 'cat_crossfit', 'priority' => 0, 'image'=>''],
            ['id' => 10, 'name' => 'Dancing', 't_key' => 'cat_dancing', 'priority' => 0, 'image'=>''],
            ['id' => 11, 'name' => 'Diving', 't_key' => 'cat_diving', 'priority' => 0, 'image'=>''],
            ['id' => 12, 'name' => 'Esport', 't_key' => 'cat_esport', 'priority' => 0, 'image'=>''],
            ['id' => 13, 'name' => 'Fitness', 't_key' => 'cat_fitness', 'priority' => 0, 'image'=>''],
            ['id' => 14, 'name' => 'Floorball', 't_key' => 'cat_floorball', 'priority' => 0, 'image'=>''],
            ['id' => 15, 'name' => 'Golf', 't_key' => 'cat_golf', 'priority' => 0, 'image'=>''],
            ['id' => 16, 'name' => 'Gymnastics', 't_key' => 'cat_gymnastics', 'priority' => 0, 'image'=>''],
            ['id' => 17, 'name' => 'Handball', 't_key' => 'cat_handball', 'priority' => 0, 'image'=>''],
            ['id' => 18, 'name' => 'Ice Hockey', 't_key' => 'cat_ice_hockey', 'priority' => 0, 'image'=>''],
            ['id' => 19, 'name' => 'Kajak & Cano', 't_key' => 'cat_kajak_and_cano', 'priority' => 0, 'image'=>''],
            ['id' => 20, 'name' => 'Karate', 't_key' => 'cat_karate', 'priority' => 0, 'image'=>''],
            ['id' => 21, 'name' => 'Kickboxing', 't_key' => 'cat_kickboxing', 'priority' => 0, 'image'=>''],
            ['id' => 22, 'name' => 'Lacrosse', 't_key' => 'cat_lacrosse', 'priority' => 0, 'image'=>''],
            ['id' => 23, 'name' => 'Mental coaching', 't_key' => 'cat_mental_coaching', 'priority' => 0, 'image'=>''],
            ['id' => 24, 'name' => 'Nutrition', 't_key' => 'cat_nutrition', 'priority' => 0, 'image'=>''],
            ['id' => 25, 'name' => 'Padel', 't_key' => 'cat_padel', 'priority' => 0, 'image'=>''],
            ['id' => 26, 'name' => 'Petanque', 't_key' => 'cat_petanque', 'priority' => 0, 'image'=>''],
            ['id' => 27, 'name' => 'Physiotherapy', 't_key' => 'cat_physiotherapy', 'priority' => 0, 'image'=>''],
            ['id' => 28, 'name' => 'Riding', 't_key' => 'cat_riding', 'priority' => 0, 'image'=>''],
            ['id' => 29, 'name' => 'Rugby', 't_key' => 'cat_rugby', 'priority' => 0, 'image'=>''],
            ['id' => 30, 'name' => 'Running', 't_key' => 'cat_running', 'priority' => 0, 'image'=>''],
            ['id' => 31, 'name' => 'Scouting', 't_key' => 'cat_scouting', 'priority' => 0, 'image'=>''],
            ['id' => 32, 'name' => 'Skateboarding', 't_key' => 'cat_skateboarding', 'priority' => 1, 'image'=>'skateboard.png'],
            ['id' => 33, 'name' => 'Skiing', 't_key' => 'cat_skiing', 'priority'=>0, 'image'=>''],
            ['id' => 34, 'name' => 'Snowboarding', 't_key' => 'cat_snowboarding', 'priority' => 0, 'image'=>''],
            ['id' => 35, 'name' => 'Soccer', 't_key' => 'cat_soccer', 'priority' => 0, 'image'=>''],
            ['id' => 36, 'name' => 'Squash', 't_key' => 'cat_squash', 'priority' => 0, 'image'=>''],
            ['id' => 37, 'name' => 'Strength and conditioning', 't_key' => 'cat_strength_and_conditioning', 'priority' => 0, 'image'=>''],
            ['id' => 38, 'name' => 'Table tennis', 't_key' => 'cat_tabletenis', 'priority' => 0, 'image'=>''],
            ['id' => 39, 'name' => 'Tennis', 't_key' => 'cat_tennis', 'priority' => 0, 'image'=>''],
            ['id' => 40, 'name' => 'Triatlon', 't_key' => 'cat_triatlon', 'priority' => 0, 'image'=>''],
            ['id' => 41, 'name' => 'Ultimate', 't_key' => 'cat_ultimate', 'priority' => 0, 'image'=>''],
            ['id' => 42, 'name' => 'Volleyball', 't_key' => 'cat_volleyball', 'priority' => 0, 'image'=>''],
            ['id' => 43, 'name' => 'Wrestling', 't_key' => 'cat_wrestling', 'priority' => 0, 'image'=>''],
            ['id' => 44, 'name' => 'Yoga', 't_key' => 'cat_yoga', 'priority' => 0, 'image'=>''],
            ['id' => 45, 'name' => 'Swimming', 't_key' => 'cat_swimming', 'priority' => 0, 'image'=>''],
        ];
        \App\Entities\SportCategory::insert($sports);

    }
}
