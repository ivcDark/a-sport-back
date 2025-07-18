<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GameStage extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = [
        'title',
        'str',
        'league_season_id',
    ];
}
