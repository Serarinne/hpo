<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model; // Pastikan extends Model

class FetchedWallpaper extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'tags' => 'array',
        'characters' => 'array',
        'artists' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}