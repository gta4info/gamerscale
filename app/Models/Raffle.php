<?php

namespace App\Models;

use App\Http\Controllers\RaffleController;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Log;
use Throwable;

class Raffle extends Model
{
    use HasFactory;

    public $guarded = [];

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->start_at = Carbon::parse($model->start_at)->subHours(3);
            $model->end_at = Carbon::parse($model->end_at)->subHours(3);
        });

        self::updating(function ($model) {
            if($model->isDirty('start_at')) {
                $model->start_at = Carbon::parse($model->start_at)->subHours(3)->format('Y-m-d H:i:s');
            }
            if($model->isDirty('end_at')) {
                $model->end_at = Carbon::parse($model->end_at)->subHours(3)->format('Y-m-d H:i:s');
            }

            if($model->discord_message_id) {
                $response = (new RaffleController())->updateMessage($model);
                if(!$response) return false;
            }

            return true;
        });
    }

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

    protected function startAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('Y-m-d H:i'),
        );
    }

    protected function endAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => Carbon::parse($value)->format('Y-m-d H:i'),
        );
    }

    protected function cost(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (int)$value,
            set: fn ($value) => $value,
        );
    }

    public function winners(): array
    {
        $winners = [];
        $raffle = Raffle::find($this->attributes['id']);

        $raffle->tickets()
            ->with('user')
            ->whereIn('id', $raffle->winner_ticket_ids)
            ->get()
            ->each(function ($ticket) use (&$winners) {
                $winners[] = $ticket->user->oauth_id;
            });

        return $winners;
    }
}
