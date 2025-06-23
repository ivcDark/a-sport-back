<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClubCoach extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'club_id',
        'coach_id',
        'season_id',
        'is_active',
        'start_date',
        'end_date',
    ];
}
