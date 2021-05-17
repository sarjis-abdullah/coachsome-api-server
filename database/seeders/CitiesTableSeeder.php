<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CitiesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id' => 1, 'name' => 'Copenhagen', 'priority' => 1, 'image' => 'skateboard.png'],
            ['id' => 2, 'name' => 'Århus', 'priority' => 2, 'image' => 'powerlift.png'],
            ['id' => 3, 'name' => 'Odense', 'priority' => 3, 'image' => 'track.png'],
        ];
        \App\Entities\City::insert($data);
    }
}
