<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Country extends Model
{
    use HasFactory, SoftDeletes, HasUuids;

    protected $fillable = ['full_name', 'name', 'code', 'logo'];

    public function leagues(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(League::class);
    }
}
