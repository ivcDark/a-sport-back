<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClubLeague extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['club_id', 'league_season_id', 'group_tournament_id'];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
