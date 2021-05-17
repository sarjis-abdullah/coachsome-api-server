<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class StarStatusTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id'=>1, 'display_text'=>'A - Star','t_key'=>''],
            ['id'=>2, 'display_text'=>'B - Star','t_key'=>''],
            ['id'=>3, 'display_text'=>'C - Star','t_key'=>''],
        ];
        \App\Entities\StarStatus::insert($data);
    }
}
