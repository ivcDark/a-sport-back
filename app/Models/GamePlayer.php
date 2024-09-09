<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GamePlayer extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['game_id', 'club_id', 'player_id', 'is_start_group', 'is_reserve_group', 'is_injured_group', 'is_best', 'rating'];
}
