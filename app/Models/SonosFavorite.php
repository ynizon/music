<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SonosFavorite extends Model
{
    protected $fillable = [
        'user_id',
        'artist',
        'album_name',
        'path',
        'type',
        'cover',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
