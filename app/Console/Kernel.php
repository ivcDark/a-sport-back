<?php

namespace App\Console;

use App\Console\Commands\Parse\Flashscore\GetClubs;
use App\Console\Commands\Parse\Flashscore\GetCountries;
use App\Console\Commands\Parse\Flashscore\GetEventGame;
use App\Console\Commands\Parse\Flashscore\GetFixturesGame;
use App\Console\Commands\Parse\Flashscore\GetLeagues;
use App\Console\Commands\Parse\Flashscore\GetLeagueSeasons;
use App\Console\Commands\Parse\Flashscore\GetLineups;
use App\Console\Commands\Parse\Flashscore\GetPlayers;
use App\Console\Commands\Parse\Flashscore\GetResultGame;
use App\Console\Commands\Parse\Flashscore\GetStatisticGame;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        /* Загрузку стран делаем ежемесячно в первый день месяца в 06:00 */
        $schedule->command(GetCountries::class)->monthly()->at('06:00');

        /* Загрузку лиг делаем еженедельно по понедельникам в 06:30 */
        $schedule->command(GetLeagues::class)->weekly()->mondays()->at('06:30');

        /* Загрузку сезонов у лиг делаем еженедельно по понедельникам в 06:40 */
        $schedule->command(GetLeagueSeasons::class)->weekly()->mondays()->at('06:40');

        /* Загрузку клубов у лиг в стране делаем ежедневно в 07:00 */
        $schedule->command(GetClubs::class)->daily()->at('07:00');

        /* Загрузку список матчей в расписании делаем ежедневно в 07:20 */
        $schedule->command(GetFixturesGame::class)->daily()->at('07:20');

        /* Загрузку результатов матчей делаем каждые 5 минут */
        $schedule->command(GetResultGame::class)->everyFiveMinutes();

        /* Загрузку статистики матчей делаем каждые 10 минут */
        $schedule->command(GetStatisticGame::class)->everyTenMinutes();

        /* Загрузку состава на матчи делаем каждые 10 минут */
        $schedule->command(GetLineups::class)->everyTenMinutes();

        /* Загрузку событий в матчах делаем каждые 15 минут */
        $schedule->command(GetEventGame::class)->everyFifteenMinutes();

        /* Загрузку игроков клуба делаем каждые 6 часов */
        $schedule->command(GetPlayers::class)->everySixHours();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
