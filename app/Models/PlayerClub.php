<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlayerClub extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['player_id', 'club_id', 'in_club'];
}
