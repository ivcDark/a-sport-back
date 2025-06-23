<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Referee extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'full_name',
        'country_id',
        'birthday',
        'photo',
        'slug',
    ];
}
