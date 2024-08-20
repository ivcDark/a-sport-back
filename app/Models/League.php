<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class League extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['full_name', 'name', 'code', 'country_id', 'logo'];

    public function getApiFootballIdAttribute()
    {
        $modelIntegration = $this->hasMany(ModelIntegration::class, 'model_id', 'id')
            ->where('model', 'league')
            ->where('type_integration', 'apiFootball')
            ->first();

        return $modelIntegration != null ? $modelIntegration->integration_id : null;
    }
}
