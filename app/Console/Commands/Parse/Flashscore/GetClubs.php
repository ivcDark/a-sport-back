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

class GetClubs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashscore:get-clubs {--league= : ID лиги}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'FlashScore - получение клубов в лиге';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('league')) {
            $leagueModel = League::where('flashscore_id', $this->option('league'))->first();
            $seasonModel = Season::where('title', '2024-2025')->first();
            $leagueSeasonModel = LeagueSeason::where('league_id', $leagueModel->id)
                ->where('season_id', $seasonModel->id)
                ->first();

            $this->info('Начинаем выгружать html с параметрами');

            $result = Http::get("https://www.flashscore.com/football/{$leagueModel->country->slug}/{$leagueModel->slug}-{$seasonModel->title}/standings");

            if ($result->status() == 200) {
                preg_match('/tournamentId:\s*"([^"]+)"/', $result->body(), $tournamentIdMatch);
                preg_match('/tournamentStageId:\s*"([^"]+)"/', $result->body(), $tournamentStageIdMatch);

                $tournamentId = $tournamentIdMatch[1] ?? null;
                $tournamentStageId = $tournamentStageIdMatch[1] ?? null;

                $this->info('Начинаем получать клубы');
                $result = Http::withHeaders([
                        'x-fsign' => 'SW9D1eZo',
                    ])
                    ->get("https://2.flashscore.ninja/2/x/feed/to_{$tournamentId}_{$tournamentStageId}_1");
                $this->info("URL = https://2.flashscore.ninja/2/x/feed/to_{$tournamentId}_{$tournamentStageId}_1");

                if ($result->status() == 200) {

                    preg_match_all('/TE÷(.*?)¬(.*?)((?=TE÷)|$)/s', $result->body(), $groupsMatches, PREG_SET_ORDER);

                    if (count($groupsMatches) == 0) {
                        preg_match_all('/IPU÷(.*?)¬/', $result->body(), $images);
                        preg_match_all('/TN÷(.*?)¬/', $result->body(), $teamNames);
                        preg_match_all('/TI÷(.*?)¬/', $result->body(), $teamIds);
                        preg_match_all('/TIU÷\/team\/(.*?)\/.*?¬/', $result->body(), $teamSlugs);

                        $clubs = [];
                        $count = min(count($images[1]), count($teamNames[1]), count($teamIds[1]), count($teamSlugs[1]));

                        for ($i = 0; $i < $count; $i++) {
                            $clubs[] = [
                                'image' => $images[1][$i],
                                'name' => $teamNames[1][$i],
                                'slug' => $teamSlugs[1][$i],
                                'id' => $teamIds[1][$i+1],
                                'group_id' => null,
                            ];

                            $this->info("Получили команду: {'name' = {$teamNames[1][$i]}, 'image' = {$images[1][$i]}, 'id = ".$teamIds[1][$i+1]."}");
                        }
                    } else {
                        $numberGroup = 1;
                        $clubs = [];
                        foreach ($groupsMatches as $groupMatch) {
                            $text = $groupMatch[2];
                            $groupModel = GroupTournament::updateOrCreate(
                                [
                                    'league_season_id' => $leagueSeasonModel->id,
                                    'name' => $groupMatch[1]
                                ],
                                [
                                    'league_season_id' => $leagueSeasonModel->id,
                                    'name' => $groupMatch[1],
                                    'sort_order' => $numberGroup,
                                ]
                            );
                            $numberGroup++;

//                            preg_match_all('/IPU÷(.*?)¬/', $text, $images);
                            preg_match_all('/TN÷(.*?)¬/', $text, $teamNames);
                            preg_match_all('/TI÷(.*?)¬/', $text, $teamIds);
                            preg_match_all('/TIU÷\/team\/(.*?)\/.*?¬/', $text, $teamSlugs);

                            $count = min(count($teamNames[1]), count($teamIds[1]), count($teamSlugs[1]));
                            dump($teamIds[1]);
                            for ($i = 0; $i < $count; $i++) {
                                $teamId = count($teamIds[1]) == 4 ? $teamIds[1][$i] : $teamIds[1][$i+1];
                                $clubs[] = [
                                    'image' => null,
                                    'name' => $teamNames[1][$i],
                                    'slug' => $teamSlugs[1][$i],
                                    'id' => $teamId,
                                    'group_id' => $groupModel->id,
                                ];

                                $this->info("Получили команду: {'name' = {$teamNames[1][$i]}, 'image' = null, 'id = ".$teamId."}");
                            }
                        }
                    }


                } else {
                    $this->error("Ошибка во время выгрузки клубов из Flashscore");
                    $this->error($result->body());
                    return false;
                }

                $this->info("Клубы с сайта получили");
            } else {
                $this->error("Ошибка во время выгрузки html с параметрами из Flashscore");
                $this->error($result->body());
                return false;
            }

            $this->info("Загружаем клубы в базу");

            if (count($clubs) > 0) {
                foreach ($clubs as $club) {
                    $clubModel = Club::updateOrCreate(
                        [
                            'slug' => $club['slug'],
                            'flashscore_id' => $club['id'],
                            'country_id' => $leagueModel->country->id,
                        ],
                        [
                            'name' => $club['name'],
                            'full_name' => $club['name'],
                            'slug' => $club['slug'],
                            'flashscore_id' => $club['id'],
                            'logo' => $club['image'],
                            'country_id' => $leagueModel->country->id,
                        ]
                    );

                    $clubLeagueModel = ClubLeague::updateOrCreate(
                        [
                            'club_id' => $clubModel->id,
                            'league_season_id' => $leagueSeasonModel->id,
                        ],
                        [
                            'club_id' => $clubModel->id,
                            'league_season_id' => $leagueSeasonModel->id,
                        ]
                    );

                    $this->info("Клуб создан в БД: {'id' = $clubModel->id}");
                }
            } else {
                $this->error("Массив с клубами пустой");
                return false;
            }

            $this->info('Загрузка клубов завершена');
            return 1;
        } else {
            $this->error('Для работы необходимо указать лигу (ID из flashscore)');
            return false;
        }
    }

}
