<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GameRound extends Model
{
    use HasFactory;

    protected $fillable = [
        'game_id',
        'round_number',
        'character1_id',
        'character2_id',
        'selected_character_id',
        'eliminated_character_id'
    ];

    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }
}