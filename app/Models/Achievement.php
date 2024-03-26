<?php

namespace App\Models;

use App\Jobs\AssignAchievementToUsers;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Achievement extends Model
{
    use HasFactory;

    public $guarded = [];

    protected $casts = [
        'levels' => 'array'
    ];

    public static function boot(): void
    {
        parent::boot();

        self::updated(function ($model) {
            // If status is updated to active
            if($model->is_active) {
                // Assign achievement to users
                AssignAchievementToUsers::dispatch($model);
            }
        });
    }

    public function users(): HasMany
    {
        return $this->hasMany(AchievementToUser::class);
    }

    public function getIconAttribute($value): string|null
    {
        if($value) {
            return config('app.VITE_APP_URL').'/'.$value;
        }

        return null;
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(PrizeAchievement::class);
    }

    public function prizeUsers(): MorphMany
    {
        return $this->morphMany(PrizeUser::class, 'prizable');
    }
}
