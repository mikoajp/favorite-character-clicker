<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'session_id',
        'total_characters',
        'current_round',
        'characters',
        'eliminated_characters',
        'winner_character_id',
        'status',
        'score'
    ];

    protected $casts = [
        'characters' => 'array',
        'eliminated_characters' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(GameRound::class);
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function getRemainingCharactersCount(): int
    {
        return count($this->characters ?? []);
    }

    public function calculateScore(): int
    {
        // Score based on total characters and rounds completed
        $baseScore = $this->total_characters * 10;
        $roundBonus = $this->rounds()->count() * 5;
        return $baseScore + $roundBonus;
    }
}