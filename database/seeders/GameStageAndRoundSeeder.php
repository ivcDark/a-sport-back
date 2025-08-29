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
        $league = League::where('id', '9f4fb238-c393-4a67-b8e4-fcc0fad2e5dd')->first(); // Российская Премьер-лига

        $stages = [
            ['title' => 'Premier League - Relegation', 'str' => 'relegation',],
            ['title' => 'Premier League',              'str' => 'regular',],
            ['title' => 'Premier League - Relegation - Play Offs', 'str' => 'relegation',],
            ['title' => 'Premier League - Championship Group', 'str' => 'championship_group',],
            ['title' => 'Premier League - Relegation Group', 'str' => 'relegation_group',],
            ['title' => 'FNL', 'str' => 'fnl',],
            ['title' => 'FNL 2 - Division A Gold - Promotion - Play Offs', 'str' => 'fnl_2_div_2_play_offs',],
            ['title' => 'FNL 2 - Division A Gold - Spring Season', 'str' => 'fnl_2_div_2_spring_season',],
            ['title' => 'FNL 2 - Division A Gold - Fall Season', 'str' => 'fnl_2_div_2_gold_fall_season',],
            ['title' => 'Russian Cup - Super final', 'str' => 'rus_cup_super_final',],
            ['title' => 'Russian Cup - Regions Path - Play Offs', 'str' => 'region_path_play_off',],
            ['title' => 'Russian Cup - Play Offs', 'str' => 'play_off',],
            ['title' => 'Russian Cup - Group Stage', 'str' => 'group_stage',],
            ['title' => 'Russian Cup - Regions Path', 'str' => 'region_path',],
            ['title' => 'Russian Cup', 'str' => 'rus_cup',],
            ['title' => 'Russian Cup - Qualification', 'str' => 'rus_cup_qualification',],
        ];

        $rounds = [
            ['title' => 'Round 1', 'str' => 'round_1',],
            ['title' => 'Round 2', 'str' => 'round_2',],
            ['title' => 'Round 3', 'str' => 'round_3',],
            ['title' => 'Round 4', 'str' => 'round_4',],
            ['title' => 'Round 5', 'str' => 'round_5',],
            ['title' => 'Round 6', 'str' => 'round_6',],
            ['title' => 'Round 7', 'str' => 'round_7',],
            ['title' => 'Round 8', 'str' => 'round_8',],
            ['title' => 'Round 9', 'str' => 'round_9',],
            ['title' => 'Round 10', 'str' => 'round_10',],
            ['title' => 'Round 11', 'str' => 'round_11',],
            ['title' => 'Round 12', 'str' => 'round_12',],
            ['title' => 'Round 13', 'str' => 'round_13',],
            ['title' => 'Round 14', 'str' => 'round_14',],
            ['title' => 'Round 15', 'str' => 'round_15',],
            ['title' => 'Round 16', 'str' => 'round_16',],
            ['title' => 'Round 17', 'str' => 'round_17',],
            ['title' => 'Round 18', 'str' => 'round_18',],
            ['title' => 'Round 19', 'str' => 'round_19',],
            ['title' => 'Round 20', 'str' => 'round_20',],
            ['title' => 'Round 21', 'str' => 'round_21',],
            ['title' => 'Round 22', 'str' => 'round_22',],
            ['title' => 'Round 23', 'str' => 'round_23',],
            ['title' => 'Round 24', 'str' => 'round_24',],
            ['title' => 'Round 25', 'str' => 'round_25',],
            ['title' => 'Round 26', 'str' => 'round_26',],
            ['title' => 'Round 27', 'str' => 'round_27',],
            ['title' => 'Round 28', 'str' => 'round_28',],
            ['title' => 'Round 29', 'str' => 'round_29',],
            ['title' => 'Round 30', 'str' => 'round_30',],
            ['title' => 'Final',    'str' => 'final',   ],
            ['title' => 'Round 31', 'str' => 'round_31',],
            ['title' => 'Round 32', 'str' => 'round_32',],
            ['title' => 'Round 33', 'str' => 'round_33',],
            ['title' => 'Round 34', 'str' => 'round_34',],
            ['title' => 'Round 35', 'str' => 'round_34',],
            ['title' => 'Round 36', 'str' => 'round_34',],
            ['title' => 'Round 37', 'str' => 'round_34',],
            ['title' => 'Round 38', 'str' => 'round_34',],
            ['title' => 'Round 39', 'str' => 'round_34',],
            ['title' => 'Round 40', 'str' => 'round_34',],
            ['title' => 'Round 41', 'str' => 'round_34',],
            ['title' => 'Round 42', 'str' => 'round_34',],
            ['title' => '1/256-finals', 'str' => '1_256_finals',],
            ['title' => '1/128-finals', 'str' => '1_128_finals',],
            ['title' => '1/64-finals', 'str' => '1_64_finals',],
            ['title' => '1/32-finals', 'str' => '1_32_finals',],
            ['title' => '1/16-finals', 'str' => '1_16_finals',],
            ['title' => '1/8-finals', 'str' => '1_8_finals',],
            ['title' => 'Quarter-finals', 'str' => 'quarter_finals',],
            ['title' => 'Semi-finals', 'str' => 'semi_finals',],
            ['title' => 'Final', 'str' => 'final',],
            ['title' => 'Round 1', 'str' => 'round_1',],
            ['title' => 'Round 2', 'str' => 'round_2',],
            ['title' => 'Round 3', 'str' => 'round_3',],
            ['title' => 'Round 4', 'str' => 'round_4',],
            ['title' => 'Round 5', 'str' => 'round_5',],
            ['title' => 'Round 6', 'str' => 'round_6',],
        ];

        $this->insertData(\App\Models\GameStage::class, $stages);
        $this->insertData(\App\Models\GameRound::class, $rounds);



//        $league = League::where('id', '9f4fb238-c898-4bbc-94b9-ec9391bb3013')->first(); // Российская ФНЛ
//        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season->id)->first();
//
//        $stages = [
//            ['title' => 'FNL', 'str' => 'fnl', 'league_season_id' => $leagueSeason->id],
//        ];
//        $rounds = [
//            ['title' => 'Round 1', 'str' => 'round_1', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 2', 'str' => 'round_2', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 3', 'str' => 'round_3', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 4', 'str' => 'round_4', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 5', 'str' => 'round_5', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 6', 'str' => 'round_6', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 7', 'str' => 'round_7', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 8', 'str' => 'round_8', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 9', 'str' => 'round_9', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 10', 'str' => 'round_10', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 11', 'str' => 'round_11', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 12', 'str' => 'round_12', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 13', 'str' => 'round_13', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 14', 'str' => 'round_14', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 15', 'str' => 'round_15', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 16', 'str' => 'round_16', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 17', 'str' => 'round_17', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 18', 'str' => 'round_18', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 19', 'str' => 'round_19', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 20', 'str' => 'round_20', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 21', 'str' => 'round_21', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 22', 'str' => 'round_22', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 23', 'str' => 'round_23', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 24', 'str' => 'round_24', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 25', 'str' => 'round_25', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 26', 'str' => 'round_26', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 27', 'str' => 'round_27', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 28', 'str' => 'round_28', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 29', 'str' => 'round_29', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 30', 'str' => 'round_30', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 31', 'str' => 'round_31', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 32', 'str' => 'round_32', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 33', 'str' => 'round_33', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 34', 'str' => 'round_34', 'league_season_id' => $leagueSeason->id],
//        ];
//
//        $this->insertData(\App\Models\GameStage::class, $stages);
//        $this->insertData(\App\Models\GameRound::class, $rounds);
//
//
//
//        $league = League::where('id', '9f4fb238-e235-4e84-bd4a-8e8054aecc08')->first(); // Кубок России
//        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season->id)->first();
//
//        $stages = [
//            ['title' => 'Russian Cup - Super final', 'str' => 'super_funal', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Russian Cup - Regions Path - Play Offs', 'str' => 'region_path_play_off', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Russian Cup - Play Offs', 'str' => 'play_off', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Russian Cup - Group Stage', 'str' => 'group_stage', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Russian Cup - Regions Path', 'str' => 'region_path', 'league_season_id' => $leagueSeason->id],
//        ];
//        $rounds = [
//            ['title' => '1/32 Finals', 'str' => '1_32_finals', 'league_season_id' => $leagueSeason->id],
//            ['title' => '1/16 Finals', 'str' => '1_16_finals', 'league_season_id' => $leagueSeason->id],
//            ['title' => '1/8 Finals', 'str' => '1_8_finals', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Quarter-Finals', 'str' => 'quarter_finals', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Semi-Finals', 'str' => 'semi_finals', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Final', 'str' => 'final', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 1', 'str' => 'round_1', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 2', 'str' => 'round_2', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 3', 'str' => 'round_3', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 4', 'str' => 'round_4', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 5', 'str' => 'round_5', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 6', 'str' => 'round_6', 'league_season_id' => $leagueSeason->id],
//        ];
//
//        $this->insertData(\App\Models\GameStage::class, $stages);
//        $this->insertData(\App\Models\GameRound::class, $rounds);
//
//
//
//        $league = League::where('id', '9f2961c0-8ff8-460c-89bc-90c33edd3839')->first(); // СуперКубок России
//        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season->id)->first();
//
//        $stages = [
//            ['title' => 'Super Cup', 'str' => 'super_cup', 'league_season_id' => $leagueSeason->id],
//        ];
//        $rounds = [
//            ['title' => 'Final', 'str' => 'final', 'league_season_id' => $leagueSeason->id],
//        ];
//
//        $this->insertData(\App\Models\GameStage::class, $stages);
//        $this->insertData(\App\Models\GameRound::class, $rounds);
//
//
//
//        $league = League::where('id', '9f47ab09-ccf8-4efd-b39c-e03216c3d728')->first(); // Клубный Чемпионат мира
//
//        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season2025->id)->first();
//
//        $stages = [
//            ['title' => 'FIFA Club World Cup - Play Offs', 'str' => 'fifa_club_world_cup_play_offs', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'FIFA Club World Cup', 'str' => 'fifa_club_world_cup', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'FIFA Club World Cup - Play-in', 'str' => 'fifa_club_world_cup_play_in', 'league_season_id' => $leagueSeason->id],
//        ];
//        $rounds = [
//            ['title' => 'Final', 'str' => 'final', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 1', 'str' => 'round_1', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 2', 'str' => 'round_2', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 3', 'str' => 'round_3', 'league_season_id' => $leagueSeason->id],
//            ['title' => '1/8-finals', 'str' => '1_8_finals', 'league_season_id' => $leagueSeason->id],
//        ];
//
//        $this->insertData(\App\Models\GameStage::class, $stages);
//        $this->insertData(\App\Models\GameRound::class, $rounds);
//
//
//
//        $league = League::where('id', '9f47ab69-315f-49ba-80aa-6d793efc2568')->first(); // Лига Чемпионов УЕФА
//        $leagueSeason = LeagueSeason::where('league_id', $league->id)->where('season_id', $season->id)->first();
//
//        $stages = [
//            ['title' => 'Champions League - Play Offs', 'str' => 'champions_league_play_offs', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Champions League - League phase', 'str' => 'champions_league_league_phase', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Champions League - Qualification', 'str' => 'champions_league_qualification', 'league_season_id' => $leagueSeason->id],
//        ];
//        $rounds = [
//            ['title' => 'Final', 'str' => 'final', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Semi-finals', 'str' => 'semi_finals', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Quarter-finals', 'str' => 'quarter_finals', 'league_season_id' => $leagueSeason->id],
//            ['title' => '1/8-finals', 'str' => '1_8_finals', 'league_season_id' => $leagueSeason->id],
//            ['title' => '1/16-finals', 'str' => '1_16_finals', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 8', 'str' => 'round_8', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 7', 'str' => 'round_7', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 6', 'str' => 'round_6', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 5', 'str' => 'round_5', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 4', 'str' => 'round_4', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 3', 'str' => 'round_3', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 2', 'str' => 'round_2', 'league_season_id' => $leagueSeason->id],
//            ['title' => 'Round 1', 'str' => 'round_1', 'league_season_id' => $leagueSeason->id],
//        ];
//
//        $this->insertData(\App\Models\GameStage::class, $stages);
//        $this->insertData(\App\Models\GameRound::class, $rounds);

        $this->command->info("Все данные загрузили");
    }

    private function insertData($model, $data): void
    {
        foreach ($data as $item) {
            $model::updateOrCreate(
                [
                    'title' => $item['title'],
                ],
                [
                    'title' => $item['title'],
                    'str' => $item['str'],
                ]
            );
        }
    }
}
