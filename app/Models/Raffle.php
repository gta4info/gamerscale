<?php

namespace App\Models;

use App\Http\Enums\RaffleStatusEnum;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Raffle extends Model
{
    use HasFactory;

    public $guarded = [];

    public function tickets(): HasMany
    {
        return $this->hasMany(RaffleTicket::class);
    }

    protected function winnerTicketIds(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => json_decode($value, true),
            set: fn ($value) => json_encode($value),
        );
    }

    public function getStatus(): int
    {
        $startAt = $this->attributes['start_at'];
        $endAt = $this->attributes['end_at'];

        if($endAt <= Carbon::now()->toDateTimeString()) {
            return RaffleStatusEnum::COMPLETED->value;
        }

        if($startAt <= Carbon::now()->toDateTimeString()) {
            return RaffleStatusEnum::ACTIVE->value;
        }

        return RaffleStatusEnum::PENDING->value;
    }
}
