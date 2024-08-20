<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Club extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['full_name', 'name', 'code', 'slug', 'logo', 'country_id'];

    public function getSoccer24IdAttribute()
    {
        $modelIntegration = $this->hasMany(ModelIntegration::class, 'model_id', 'id')
            ->where('model', 'club')
            ->where('type_integration', 'soccer_24')
            ->first();

        return $modelIntegration != null ? $modelIntegration->integration_id : null;
    }
}
