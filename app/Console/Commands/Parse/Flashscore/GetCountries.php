<?php

namespace App\Console\Commands\Parse\Flashscore;

use App\Models\Club;
use App\Models\ClubLeague;
use App\Models\Country;
use App\Models\GroupTournament;
use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\Season;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class GetCountries extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashscore:get-countries {--file=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FlashScore - получение стран';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $filePath = $this->option('file') ? $this->option('file') : "contrib/flashscore_countries_17062025.json";
        $countries = json_decode(file_get_contents(database_path($filePath)), 1);
        $count = 1;
        $countAll = count($countries);
        $this->info("Начинаем загрузку стран (всего: $countAll)");

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
            $this->info("$count/$countAll");
            $count++;
        }
        $this->info("Загрузка окончена");
    }

}
