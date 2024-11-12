<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewTableBestClubsSeason extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'club_id',
        'league_season_id',
        'league_id',
        'season_id',
        'section_game_id',
        'games_played',
        'points',
        'goals_scored',
        'goals_conceded',
        'goals_diff',
        'wins',
        'yellow_cards',
        'red_cards',
        'avg_ball_possession'
    ];

    public function club(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Club::class, 'club_id', 'id');
    }

    public function league(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function sectionGame(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FilterTable::class, 'section_game_id', 'id');
    }
}
