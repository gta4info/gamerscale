<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Http\Controllers\UserBalanceController;
use App\Http\Enums\RaffleStatusEnum;
use App\Http\Enums\UserBalanceTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
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

    public function achievementPrizes(): HasMany
    {
        return $this->hasMany(AchievementPrizeToUser::class);
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
}
