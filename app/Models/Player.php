<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Player extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['club_id', 'fio', 'number', 'soccer_24_id', 'fieldName', 'listName', 'country_id', 'slug', 'in_club', 'position', 'image', 'birthday', 'flashscore_id'];

    public function clubs()
    {
        return $this->belongsToMany(Club::class, 'player_clubs');
    }
}
