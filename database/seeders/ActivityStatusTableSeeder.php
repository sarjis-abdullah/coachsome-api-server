<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class ActivityStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id'=>1, 'display_text'=>'Active','t_key'=>''],
            ['id'=>2, 'display_text'=>'De-active','t_key'=>''],
            ['id'=>3, 'display_text'=>'Archived','t_key'=>''],
        ];
        \App\Entities\ActivityStatus::insert($data);
    }
}
