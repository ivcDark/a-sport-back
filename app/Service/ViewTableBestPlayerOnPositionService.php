<?php

namespace App\Service;

use App\Dto\ViewTableBestClubSeasonDto;
use App\Models\Club;
use App\Models\Country;
use App\Models\FilterTable;
use App\Models\Player;
use App\Models\ViewTableBestClubsSeason;
use App\Models\ViewTableBestPlayerOnPosition;
use App\Models\ViewTableTopGame;
use Illuminate\Support\Facades\DB;

class ViewTableBestPlayerOnPositionService
{

    public function __construct()
    {
        $this->nameModel = 'view_table_best_player_on_positions';
    }

    public function get()
    {
        return ViewTableBestPlayerOnPosition::latestPlayers()->get();
    }
}
