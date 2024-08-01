<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeagueSeason extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['league_id', 'season_id', 'start_at', 'finish_at'];

    public function getSoccer24IdAttribute()
    {
        $modelIntegration = $this->hasMany(ModelIntegration::class, 'model_id', 'id')
            ->where('model', 'league_season')
            ->where('type_integration', 'soccer_24')
            ->first();

        return $modelIntegration != null ? $modelIntegration->integration_id : null;
    }
}
