<?php

namespace Database\Seeders;

use App\Models\Country;
use Illuminate\Database\Seeder;

class CountriesInFlashscore extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $countries = [
            ['name' => 'Australia', 'flashscore_id' => 24],
            ['name' => 'Austria', 'flashscore_id' => 25],
            ['name' => 'Azerbaijan', 'flashscore_id' => 26],
            ['name' => 'Albania', 'flashscore_id' => 17],
            ['name' => 'Panama', 'flashscore_id' => 149],
            ['name' => 'Paraguay', 'flashscore_id' => 151],
            ['name' => 'Peru', 'flashscore_id' => 152],
            ['name' => 'Poland', 'flashscore_id' => 154],
            ['name' => 'Portugal', 'flashscore_id' => 155],
            ['name' => 'Reunion', 'flashscore_id' => 237],
            ['name' => 'Russia', 'flashscore_id' => 158],
        ];

        foreach ($countries as $country) {
            Country::create(
                [
                    'name' => $country['name'],
                    'flashscore_id' => $country['flashscore_id'],
                ]
            );
        }
    }
}
