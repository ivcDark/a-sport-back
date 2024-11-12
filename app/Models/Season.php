<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Season extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['title', 'code'];

    public function leagueSeasons(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(LeagueSeason::class);
    }

    public function scopeCurrent($query)
    {
        return $query->orderBy('title', 'desc')->first();
    }
}
