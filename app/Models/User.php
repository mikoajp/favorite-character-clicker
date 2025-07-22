<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }

    public function favoriteCharacters(): HasMany
    {
        return $this->hasMany(FavoriteCharacter::class);
    }

    public function characterRatings(): HasMany
    {
        return $this->hasMany(CharacterRating::class);
    }

    public function getCompletedGamesCount(): int
    {
        return $this->games()->where('status', 'completed')->count();
    }

    public function getTotalScore(): int
    {
        return $this->games()->where('status', 'completed')->sum('score');
    }

    public function getAverageScore(): float
    {
        $completedGames = $this->games()->where('status', 'completed');
        return $completedGames->count() > 0 ? $completedGames->avg('score') : 0;
    }
}
