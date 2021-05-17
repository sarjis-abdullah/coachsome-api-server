<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DaysTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id'=>1,'name'=>'Monday','t_key'=>'week_full_monday'],
            ['id'=>2,'name'=>'Tuesday','t_key'=>'week_full_tuesday'],
            ['id'=>3,'name'=>'Wednesday', 't_key'=>'week_full_wednesday'],
            ['id'=>4,'name'=>'Thursday','t_key'=>'week_full_thursday'],
            ['id'=>5,'name'=>'Friday','t_key'=>'week_full_friday'],
            ['id'=>6,'name'=>'Saturday','t_key'=>'week_full_saturday'],
            ['id'=>7,'name'=>'Sunday','t_key'=>'week_full_sunday'],
        ];
        \App\Entities\Day::insert($data);
    }
}
