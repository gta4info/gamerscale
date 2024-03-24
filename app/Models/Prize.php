<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Prize extends Model
{
    use HasFactory;

    public $guarded = [];

    public function achievements(): BelongsToMany
    {
        return $this->belongsToMany(Achievement::class, 'prize_achievement');
    }

    public function achievementPrizeToUser(): BelongsToMany
    {
        return $this->belongsToMany(AchievementPrizeToUser::class, 'achievement_prize_to_users', 'prize_id');
    }

    public function getIconAttribute($value): string|null
    {
        if($value) {
            return config('app.VITE_APP_URL').'/'.$value;
        }

        return null;
    }
}
