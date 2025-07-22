<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FavoriteCharacter extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'character_id',
        'character_name',
        'character_image'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}