<?php

namespace App\Console\Commands\Parse;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class Sportbox extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'parse:sportbox';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Парсинг спортбокса';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $result = Http::get('https://news.sportbox.ru/Vidy_sporta/Futbol/Russia/premier_league/stats/turnir_20912/game_1380865282');
        $crawler = new Crawler($result->body());
        $content = $crawler->filter('#sb_c_trans_text_text ul li')->each(function (Crawler $node) {
            return $node->text();
        });

        dd($content);
    }
}
