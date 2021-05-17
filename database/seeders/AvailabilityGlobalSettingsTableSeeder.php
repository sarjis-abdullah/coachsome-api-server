<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class AvailabilityGlobalSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $filterableIdList = json_encode([1, 2, 3, 4, 5]);
        $days = \App\Entities\Day::get(['id', 'name', 't_key',])->each(function ($item) {
            $item->time_ranges = [];
            $item->filtered_times = [];
            $item->times = \App\Entities\Time::get(['id', 'text', 'status'])->toArray();

        })->toArray();
        $data = ['id' => 1, 'filterable_id_list' => $filterableIdList, 'days' => json_encode($days), 'is_fewer_time' => 0];
        \App\Entities\AvailabilityGlobalSetting::insert($data);
    }
}
