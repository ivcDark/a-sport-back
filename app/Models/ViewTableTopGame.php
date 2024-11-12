<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ViewTableTopGame extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = ['game_id', 'win_home', 'win_guest', 'draw', 'actual'];

    public function game(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    public function scopeActual($query)
    {
        return $query->where('actual', true);
    }
}
