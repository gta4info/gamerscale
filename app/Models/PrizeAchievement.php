<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrizeAchievement extends Model
{
    use HasFactory;

    protected $table = 'prize_achievement';
    public $guarded = [];

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class, 'achievement_id', 'id');
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class, 'prize_id', 'id');
    }
}
