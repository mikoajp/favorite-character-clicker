<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CharacterRating extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'character_id',
        'character_name',
        'rating',
        'comment'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeByCharacter($query, string $characterId)
    {
        return $query->where('character_id', $characterId);
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }
}