<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(LaratrustSeeder::class);
        $this->call(TranslationsTableSeeder::class);
        $this->call(LanguagesTableSeeder::class);
        $this->call(SportCategoriesTableSeeder::class);
        $this->call(SportTagsTableSeeder::class);
        $this->call(UserTableSeeder::class);
        $this->call(ProfileTableSeeder::class);
        $this->call(OAuthTableSeeder::class);
        $this->call(PackageCategoriesTableSeeder::class);
        $this->call(TimesTableSeeder::class);
        $this->call(DaysTableSeeder::class);
        $this->call(AvailabilityGlobalSettingsTableSeeder::class);
        $this->call(NotificationsTableSeeder::class);
        $this->call(CurrenciesTableSeeder::class);
        $this->call(CitiesTableSeeder::class);
        $this->call(StepsTableSeeder::class);
        $this->call(PagesTableSeeder::class);


    }
}
