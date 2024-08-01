<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameResult extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['game_id', 'home_goals', 'guest_goals'];
}
