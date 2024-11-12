<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FilterTable extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name' , 'type'];

    public function scopeSectionGame($query)
    {
        return $query->where('type', 'section_game');
    }

    public function scopeAllGame($query)
    {
        return $query->where('name', 'Full game');
    }
}
