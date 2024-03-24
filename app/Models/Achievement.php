<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Achievement extends Model
{
    use HasFactory;

    public $guarded = [];

    protected $casts = [
        'levels' => 'array'
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(PrizeAchievement::class, 'achievement_id', 'id');
    }

    public function userPrizes(): HasMany
    {
        return $this->hasMany(AchievementPrizeToUser::class);
    }

    public function getIconAttribute($value): string|null
    {
        if($value) {
            return config('app.VITE_APP_URL').'/'.$value;
        }

        return null;
    }
}
