<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewTableBestPlayerSeason extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'club_id',
        'league_id',
        'season_id',
        'league_season_id',
        'section_game_id',
        'type_game_id',
        'player_id',
        'games_played',
        'goals',
        'assist',
        'yellow_cards',
        'red_cards',
        'rating',
    ];

    public function club(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Club::class);
    }

    public function league(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    public function sectionGame(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(FilterTable::class, 'type_game_id', 'id');
    }

    public function player(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
