<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayoffRound extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['league_season_id', 'title', 'round_order'];
}
