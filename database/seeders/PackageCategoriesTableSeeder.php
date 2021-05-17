<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class PackageCategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            ['id'=>1,'name'=>'Default Package'],
            ['id'=>2,'name'=>'Camp Package'],
        ];
        \App\Entities\PackageCategory::insert($data);
    }
}
