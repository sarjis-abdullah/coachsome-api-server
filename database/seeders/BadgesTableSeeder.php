<?php

namespace Database\Seeders;

use App\Entities\Badge;
use Illuminate\Database\Seeder;

class BadgesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id' => 1, 'name' => 'No ranked', 'key' => 'no_rank', 't_key' => 'badge_text_no_ranked'],
            ['id' => 2, 'name' => 'Popular', 'key' => 'popular', 't_key' => 'badge_text_popular'],
            ['id' => 3, 'name' => 'Trending', 'key' => 'trending', 't_key' => 'badge_text_trending'],
            ['id' => 4, 'name' => 'Rising', 'key' => 'rising', 't_key' => 'badge_text_rising'],
            ['id' => 5, 'name' => 'Top ranked', 'key' => 'top', 't_key' => 'badge_text_top'],
        ];

        foreach ($data as $item) {
            Badge::create($item);
        }
    }
}
