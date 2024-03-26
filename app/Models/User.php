<?php

namespace App\Models;

use App\Http\Controllers\Admin\AchievementController;
use App\Http\Controllers\UserBalanceController;
use App\Http\Enums\UserBalanceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    public $guarded = [];

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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public static function boot(): void
    {
        parent::boot();

        self::created(function ($model) {
            // Assign achievements to user
            (new AchievementController())->assignAchievementsToUser($model);
        });
    }

    public function balance(): HasMany
    {
        return $this->hasMany(UserBalance::class);
    }

    public function rafflesTickets(): HasMany
    {
        return $this->hasMany(RaffleTicket::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(AchievementToUser::class);
    }

    public function isAdmin(): bool
    {
        return $this->attributes['is_admin'];
    }

    public function currentBalances(): array
    {
        $user = User::find($this->attributes['id']);
        $arr = [];
        foreach (UserBalanceTypeEnum::cases() as $type) {
            $arr[] = [
                'type' => $type->value,
                'value' => (new UserBalanceController())->getCurrentBalanceByType($user, $type->value)
            ];
        }

        return $arr;
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(PrizeUser::class);
    }
}
