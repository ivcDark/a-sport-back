<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExternalMapping extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'source',
        'external_id',
        'external_name',
    ];
}
