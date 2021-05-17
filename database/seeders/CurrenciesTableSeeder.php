<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CurrenciesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
           [
               'id' => 1,
               'name' => 'Danish krone',
               'code' => 'DKK',
               'symbol' => 'kr.',
               'decimal_digits' => '2',
               'thousand_separator' => '.',
               'fraction_separator' => ',',
               'symbol_spacing' => '1',
               'is_def_based_currency' => '1',
               'exchange_rate' => '1'
           ],
            [
                'id' => 2,
                'name' => 'United States dollar',
                'code' => 'USD',
                'symbol' => '$',
                'decimal_digits' => '2',
                'thousand_separator' => ',',
                'fraction_separator' => '.',
                'symbol_spacing' => '0',
                'is_def_based_currency' => '0',
                'exchange_rate' => '0.15'
            ],
            [
                'id' => 3,
                'name' => 'Euro',
                'code' => 'EUR',
                'symbol' => '€',
                'decimal_digits' => '2',
                'thousand_separator' => ',',
                'fraction_separator' => '.',
                'symbol_spacing' => '0',
                'is_def_based_currency' => '0',
                'exchange_rate' => '0.13'
            ],
        ];
        \App\Entities\Currency::insert($data);
    }
}
