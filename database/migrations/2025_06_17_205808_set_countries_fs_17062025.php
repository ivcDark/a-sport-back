<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $countries = json_decode(file_get_contents(database_path("contrib/flashscore_countries_17062025.json")), 1);

        foreach ($countries as $country) {
            \App\Models\Country::updateOrCreate(
                [
                    'name' => $country['name'],
                    'flashscore_id' => $country['flashscore_id'],
                ],
                [
                    'name' => $country['name'],
                    'slug' => $country['slug'],
                    'flashscore_id' => $country['flashscore_id'],
                ]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
