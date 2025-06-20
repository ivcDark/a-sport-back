<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GameStatistic extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['game_id', 'club_id', 'section_game', 'type_indicator', 'value', 'period', 'stat_type_id'];
}
