<?php

namespace App\Console\Commands\Parse\ApiFootball;

use App\Dto\CountryDto;
use App\Dto\LeagueDto;
use Illuminate\Console\Command;

class League extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api_football:league';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсинг api_football - лиги';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $parse = new \App\Parse\ApiFootball\League();
        $arrLeagues = $parse->start()->toArray();

        $this->info("В ресурсе найдено лиг: " . count($arrLeagues));

        $countModels = 0;
        foreach ($arrLeagues as $league) {
            $this->info("Пишем в базу лигу: " . $league['league_name']);
            $dataToDto = [
                'name' => $league['league_name'],
                'apiName' => $league['league_name'],
                'apiId' => $league['league_id'],
                'logo' => $league['league_logo'],
                'countryId' => \App\Models\Country::where('name', $league['country_name'])->first()->id,
            ];
            $dto = new LeagueDto($dataToDto);
            $countryModel = (new \App\Service\LeagueService('apiFootball'))->updateOrCreate($dto);
            $this->info($countryModel != null ? "Ok" : "Error");
            $countModels++;
        }

        $this->info("В ресурсе найдено лиг: " . count($arrLeagues));
        $this->info("В базу записали лиг: " . $countModels);
    }
}
