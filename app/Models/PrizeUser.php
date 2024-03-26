<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

class PrizeUser extends Model
{
    use HasFactory;

    public $guarded = [];

    protected $casts = [
        'data' => 'object'
    ];

    public function prizable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function prize(): BelongsTo
    {
        return $this->belongsTo(Prize::class);
    }

    public function parent(): BelongsTo
    {
        $className = $this->attributes['prizable_type'];
        $related = new $className();

        return $this->belongsTo($related, 'prizable_id', 'id');
    }
}
