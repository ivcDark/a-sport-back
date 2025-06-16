<?php

namespace App\Console\Commands\Parse\Flashscore;

use App\Models\Country;
use App\Models\League;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GetLeagues extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashscore:get-leagues {--country= : ID страны}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FlashScore - получение лиг в стране';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('country')) {
            $countryModel = Country::where('flashscore_id', $this->option('country'))->first();

            $this->info('Начинаем получать лиги');

            $result = Http::get("https://www.flashscore.com/x/req/m_1_{$countryModel->flashscore_id}");

            if ($result->status() == 200) {
                $rawLeagues = explode('~', $result->body());
                $leagues = [];

                foreach ($rawLeagues as $leagueBlock) {
                    $name = null;
                    $slug = null;
                    $leagueId = null;

                    if (preg_match('/MN÷([^¬]+)/u', $leagueBlock, $matches)) {
                        $name = $matches[1];
                    }
                    if (preg_match('/MU÷([^¬]+)/u', $leagueBlock, $matches)) {
                        $slug = $matches[1];
                    }
                    if (preg_match('/MTI÷([^¬]+)/u', $leagueBlock, $matches)) {
                        $leagueId = $matches[1];
                    }
                    if ($name && $slug && $leagueId) {
                        $leagues[] = [
                            'name' => $name,
                            'slug' => $slug,
                            'leagueId' => $leagueId,
                        ];

                        $this->info("Получили лигу: {'name' = $name, 'slug' = $slug, 'leagueId = $leagueId}");
                    }
                }

                $this->info("Лиги с сайта получили");
            } else {
                $this->error("Ошибка во время выгрузки данных из Flashscore");
                $this->error($result->body());
                return false;
            }



            $this->info("Загружаем лиги в базу");

            if (count($leagues) > 0) {
                foreach ($leagues as $league) {
                    $leagueModel = League::create(
                        [
                            'name' => $league['name'],
                            'slug' => $league['slug'],
                            'flashscore_id' => $league['leagueId'],
                            'country_id' => $countryModel->id,
                        ]
                    );

                    $this->info("Страна создана в БД: {'id' = $leagueModel->id}");
                }
            } else {
                $this->error("Массив с лигами пустой");
                return false;
            }

            $this->info('Загрузка лиг завершена');
            return 1;
        } else {
            $this->error('Для работы необходимо указать страну (ID из flashscore)');
            return false;
        }
    }

}
