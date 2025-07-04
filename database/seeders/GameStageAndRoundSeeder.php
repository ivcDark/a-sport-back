<?php

namespace Database\Seeders;

use App\Models\League;
use App\Models\LeagueSeason;
use App\Models\Season;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GameStageAndRoundSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $league = League::where('id', '9f2961c0-5711-4c17-b558-05ceafb2bd6b')->first(); // Российская Премьер-лига
        $season = Season::where('title', '2024-2025')->first();
        $season2025 = Season::where('title', '2025')->first();
        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season->id)->first();

        $stages = [
            ['title' => 'Premier League - Relegation', 'str' => 'relegation', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Premier League',              'str' => 'regular',    'league_season_id' => $leagueSeason->id],
        ];

        $rounds = [
            ['title' => 'Round 1', 'str' => 'round_1', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 2', 'str' => 'round_2', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 3', 'str' => 'round_3', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 4', 'str' => 'round_4', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 5', 'str' => 'round_5', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 6', 'str' => 'round_6', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 7', 'str' => 'round_7', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 8', 'str' => 'round_8', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 9', 'str' => 'round_9', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 10', 'str' => 'round_10', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 11', 'str' => 'round_11', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 12', 'str' => 'round_12', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 13', 'str' => 'round_13', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 14', 'str' => 'round_14', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 15', 'str' => 'round_15', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 16', 'str' => 'round_16', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 17', 'str' => 'round_17', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 18', 'str' => 'round_18', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 19', 'str' => 'round_19', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 20', 'str' => 'round_20', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 21', 'str' => 'round_21', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 22', 'str' => 'round_22', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 23', 'str' => 'round_23', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 24', 'str' => 'round_24', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 25', 'str' => 'round_25', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 26', 'str' => 'round_26', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 27', 'str' => 'round_27', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 28', 'str' => 'round_28', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 29', 'str' => 'round_29', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 30', 'str' => 'round_30', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Final',    'str' => 'final',    'league_season_id' => $leagueSeason->id],
        ];

        $this->insertData(\App\Models\GameStage::class, $stages);
        $this->insertData(\App\Models\GameRound::class, $rounds);



        $league = League::where('id', '9f2961c0-5c70-48a4-89d9-04c3e2eef6d7')->first(); // Российская ФНЛ
        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season->id)->first();

        $stages = [
            ['title' => 'FNL', 'str' => 'fnl', 'league_season_id' => $leagueSeason->id],
        ];
        $rounds = [
            ['title' => 'Round 1', 'str' => 'round_1', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 2', 'str' => 'round_2', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 3', 'str' => 'round_3', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 4', 'str' => 'round_4', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 5', 'str' => 'round_5', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 6', 'str' => 'round_6', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 7', 'str' => 'round_7', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 8', 'str' => 'round_8', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 9', 'str' => 'round_9', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 10', 'str' => 'round_10', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 11', 'str' => 'round_11', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 12', 'str' => 'round_12', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 13', 'str' => 'round_13', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 14', 'str' => 'round_14', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 15', 'str' => 'round_15', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 16', 'str' => 'round_16', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 17', 'str' => 'round_17', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 18', 'str' => 'round_18', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 19', 'str' => 'round_19', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 20', 'str' => 'round_20', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 21', 'str' => 'round_21', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 22', 'str' => 'round_22', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 23', 'str' => 'round_23', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 24', 'str' => 'round_24', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 25', 'str' => 'round_25', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 26', 'str' => 'round_26', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 27', 'str' => 'round_27', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 28', 'str' => 'round_28', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 29', 'str' => 'round_29', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 30', 'str' => 'round_30', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 31', 'str' => 'round_31', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 32', 'str' => 'round_32', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 33', 'str' => 'round_33', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 34', 'str' => 'round_34', 'league_season_id' => $leagueSeason->id],
        ];

        $this->insertData(\App\Models\GameStage::class, $stages);
        $this->insertData(\App\Models\GameRound::class, $rounds);



        $league = League::where('id', '9f2961c0-87a0-4581-bf29-e5e89ba1eb01')->first(); // Кубок России
        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season->id)->first();

        $stages = [
            ['title' => 'Russian Cup - Super final', 'str' => 'super_funal', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Russian Cup - Regions Path - Play Offs', 'str' => 'region_path_play_off', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Russian Cup - Play Offs', 'str' => 'play_off', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Russian Cup - Group Stage', 'str' => 'group_stage', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Russian Cup - Regions Path', 'str' => 'region_path', 'league_season_id' => $leagueSeason->id],
        ];
        $rounds = [
            ['title' => '1/32 Finals', 'str' => '1_32_finals', 'league_season_id' => $leagueSeason->id],
            ['title' => '1/16 Finals', 'str' => '1_16_finals', 'league_season_id' => $leagueSeason->id],
            ['title' => '1/8 Finals', 'str' => '1_8_finals', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Quarter-Finals', 'str' => 'quarter_finals', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Semi-Finals', 'str' => 'semi_finals', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Final', 'str' => 'final', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 1', 'str' => 'round_1', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 2', 'str' => 'round_2', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 3', 'str' => 'round_3', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 4', 'str' => 'round_4', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 5', 'str' => 'round_5', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 6', 'str' => 'round_6', 'league_season_id' => $leagueSeason->id],
        ];

        $this->insertData(\App\Models\GameStage::class, $stages);
        $this->insertData(\App\Models\GameRound::class, $rounds);



        $league = League::where('id', '9f2961c0-8ff8-460c-89bc-90c33edd3839')->first(); // СуперКубок России
        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season->id)->first();

        $stages = [
            ['title' => 'Super Cup', 'str' => 'super_cup', 'league_season_id' => $leagueSeason->id],
        ];
        $rounds = [
            ['title' => 'Final', 'str' => 'final', 'league_season_id' => $leagueSeason->id],
        ];

        $this->insertData(\App\Models\GameStage::class, $stages);
        $this->insertData(\App\Models\GameRound::class, $rounds);



        $league = League::where('id', '9f47ab09-ccf8-4efd-b39c-e03216c3d728')->first(); // Клубный Чемпионат мира

        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season2025->id)->first();

        $stages = [
            ['title' => 'FIFA Club World Cup - Play Offs', 'str' => 'fifa_club_world_cup_play_offs', 'league_season_id' => $leagueSeason->id],
            ['title' => 'FIFA Club World Cup', 'str' => 'fifa_club_world_cup', 'league_season_id' => $leagueSeason->id],
            ['title' => 'FIFA Club World Cup - Play-in', 'str' => 'fifa_club_world_cup_play_in', 'league_season_id' => $leagueSeason->id],
        ];
        $rounds = [
            ['title' => 'Final', 'str' => 'final', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 1', 'str' => 'round_1', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 2', 'str' => 'round_2', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 3', 'str' => 'round_3', 'league_season_id' => $leagueSeason->id],
            ['title' => '1/8-finals', 'str' => '1_8_finals', 'league_season_id' => $leagueSeason->id],
        ];

        $this->insertData(\App\Models\GameStage::class, $stages);
        $this->insertData(\App\Models\GameRound::class, $rounds);



        $league = League::where('id', '9f47ab69-315f-49ba-80aa-6d793efc2568')->first(); // Лига Чемпионов УЕФА
        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season->id)->first();

        $stages = [
            ['title' => 'Champions League - Play Offs', 'str' => 'champions_league_play_offs', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Champions League - League phase', 'str' => 'champions_league_league_phase', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Champions League - Qualification', 'str' => 'champions_league_qualification', 'league_season_id' => $leagueSeason->id],
        ];
        $rounds = [
            ['title' => 'Final', 'str' => 'final', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Semi-finals', 'str' => 'semi_finals', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Quarter-finals', 'str' => 'quarter_finals', 'league_season_id' => $leagueSeason->id],
            ['title' => '1/8-finals', 'str' => '1_8_finals', 'league_season_id' => $leagueSeason->id],
            ['title' => '1/16-finals', 'str' => '1_16_finals', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 8', 'str' => 'round_8', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 7', 'str' => 'round_7', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 6', 'str' => 'round_6', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 5', 'str' => 'round_5', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 4', 'str' => 'round_4', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 3', 'str' => 'round_3', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 2', 'str' => 'round_2', 'league_season_id' => $leagueSeason->id],
            ['title' => 'Round 1', 'str' => 'round_1', 'league_season_id' => $leagueSeason->id],
        ];

        $this->insertData(\App\Models\GameStage::class, $stages);
        $this->insertData(\App\Models\GameRound::class, $rounds);

        $this->command->info("Все данные загрузили");
    }

    private function insertData($model, $data): void
    {
        foreach ($data as $item) {
            $model::updateOrCreate(
                [
                    'str' => $item['str'],
                    'league_season_id' => $item['league_season_id'],
                ],
                [
                    'title' => $item['title'],
                    'str' => $item['str'],
                    'league_season_id' => $item['league_season_id'],
                ]
            );
        }
    }
}
