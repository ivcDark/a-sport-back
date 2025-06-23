<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Coach extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'full_name',
        'short_name',
        'birthday',
        'country_id',
        'photo',
        'slug',
    ];
}
