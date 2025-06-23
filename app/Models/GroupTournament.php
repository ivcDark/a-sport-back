<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GroupTournament extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'league_season_id',
        'name',
        'sort_order',
    ];
}
