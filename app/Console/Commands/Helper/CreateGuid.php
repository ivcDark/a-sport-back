<?php

namespace App\Console\Commands\Helper;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class CreateGuid extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'helper:create-guid {--count= : Количество GUID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Генерация GUID';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('count')) {
            for ($i = 0; $i <= $this->option('count'); $i++) {
                $this->info(Str::uuid());
            }
        } else {
            $this->info(Str::uuid());
        }
    }
}
