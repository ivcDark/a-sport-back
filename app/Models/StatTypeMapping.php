<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StatTypeMapping extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'source',
        'original_name',
        'original_id',
        'stat_type_id',
    ];
}
