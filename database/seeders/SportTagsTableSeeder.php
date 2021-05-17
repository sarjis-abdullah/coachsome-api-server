<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SportTagsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $sport_tags = [
            ['id' => 1, 'name' => 'Nutrition'],
            ['id' => 2, 'name' => 'Speed & Agility'],
            ['id' => 3, 'name' => 'Shooting'],
            ['id' => 4, 'name' => 'Tactical coaching'],
        ];
        \App\Entities\SportTag::insert($sport_tags);
    }
}
