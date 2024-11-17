<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ViewTableBestPlayerOnPosition extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['player_id', 'position', 'rating'];

    public function scopeLatestPlayers($query)
    {
        return $query->orderByDesc('updated_at')->limit(11);
    }

    public function player()
    {
        return $this->belongsTo(Player::class);
    }
}
