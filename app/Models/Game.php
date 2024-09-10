<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Game extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['club_home_id', 'club_guest_id', 'league_season_id', 'tour', 'time_start'];

    public function getSoccer24IdAttribute()
    {
        $modelIntegration = $this->hasMany(ModelIntegration::class, 'model_id', 'id')
            ->where('model', 'game')
            ->where('type_integration', 'soccer_24')
            ->first();

        return $modelIntegration != null ? $modelIntegration->integration_id : null;
    }

    public function clubHome()
    {
        return $this->belongsTo(Club::class, 'club_home_id', 'id');
    }

    public function clubGuest()
    {
        return $this->belongsTo(Club::class, 'club_guest_id', 'id');
    }

    public function leagueSeason()
    {
        return $this->belongsTo(LeagueSeason::class, 'league_season_id', 'id');
    }

    public function result()
    {
        return $this->hasOne(GameResult::class, 'game_id', 'id');
    }
}
