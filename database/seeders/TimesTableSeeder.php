<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TimesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id'=>1, 'text'=> '00:00', 'status'=> 0],
            ['id'=>2, 'text'=> '01:00', 'status'=> 0],
            ['id'=>3, 'text'=> '02:00', 'status'=> 0],
            ['id'=>4, 'text'=> '03:00', 'status'=> 0],
            ['id'=>5, 'text'=> '04:00', 'status'=> 0],
            ['id'=>6, 'text'=> '05:00', 'status'=> 0],
            ['id'=>7, 'text'=> '06:00', 'status'=> 0],
            ['id'=>8, 'text'=> '07:00', 'status'=> 0],
            ['id'=>9, 'text'=> '08:00', 'status'=> 0],
            ['id'=>10, 'text'=> '09:00', 'status'=> 0],
            ['id'=>11, 'text'=> '10:00', 'status'=> 0],
            ['id'=>12, 'text'=> '11:00', 'status'=> 0],
            ['id'=>13, 'text'=> '12:00', 'status'=> 0],
            ['id'=>14, 'text'=> '13:00', 'status'=> 0],
            ['id'=>15, 'text'=> '14:00', 'status'=> 0],
            ['id'=>16, 'text'=> '15:00', 'status'=> 0],
            ['id'=>17, 'text'=> '16:00', 'status'=> 0],
            ['id'=>18, 'text'=> '17:00', 'status'=> 0],
            ['id'=>19, 'text'=> '18:00', 'status'=> 0],
            ['id'=>20, 'text'=> '19:00', 'status'=> 0],
            ['id'=>21, 'text'=> '20:00', 'status'=> 0],
            ['id'=>22, 'text'=> '21:00', 'status'=> 0],
            ['id'=>23, 'text'=> '22:00', 'status'=> 0],
            ['id'=>24, 'text'=> '23:00', 'status'=> 0],
        ];
        \App\Entities\Time::insert($data);
    }
}
