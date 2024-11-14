<?php

namespace App\Service;

use App\Dto\ViewTableBestClubSeasonDto;
use App\Models\Club;
use App\Models\Country;
use App\Models\FilterTable;
use App\Models\Player;
use App\Models\ViewTableBestClubsSeason;
use App\Models\ViewTableTopGame;
use Illuminate\Support\Facades\DB;

class ViewTableTopGameService
{
    private string $nameModel;

    public function __construct()
    {
        $this->nameModel = 'view_table_top_games';
    }

    public function get()
    {
        return ViewTableTopGame::actual()->get();
    }
}
