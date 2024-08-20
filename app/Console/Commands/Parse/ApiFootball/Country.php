<?php

namespace App\Console\Commands\Parse\ApiFootball;

use App\Dto\CountryDto;
use Illuminate\Console\Command;

class Country extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'api_football:country';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсинг api_football - страны';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $parse = new \App\Parse\ApiFootball\Country();
        $arrCountries = $parse->start()->toArray();

        $this->info("В ресурсе найдено стран: " . count($arrCountries));

        $countModels = 0;
        foreach ($arrCountries as $country) {
            $this->info("Пишем в базу страну: " . $country['country_name']);
            $dataToDto = [
                'name' => $country['country_name'],
                'apiName' => $country['country_name'],
                'apiId' => $country['country_id'],
                'logo' => $country['country_logo'],
            ];
            $dto = new CountryDto($dataToDto);
            $countryModel = (new \App\Service\CountryService('apiFootball'))->updateOrCreate($dto);
            $this->info($countryModel != null ? "Ok" : "Error");
            $countModels++;
        }

        $this->info("В ресурсе найдено стран: " . count($arrCountries));
        $this->info("В базу записали стран: " . $countModels);
    }
}
