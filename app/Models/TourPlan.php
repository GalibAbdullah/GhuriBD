<?php

namespace App\Models;

use Database\Factories\TourPlanFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TourPlan extends Model
{
    /** @use HasFactory<TourPlanFactory> */
    use HasFactory;

    protected $fillable = [
        'traveler_id',
        'destination',
        'start_date',
        'duration_days',
        'budget',
        'interests',
        'regenerated_at',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'duration_days' => 'integer',
            'budget' => 'decimal:2',
            'interests' => 'array',
            'regenerated_at' => 'datetime',
        ];
    }

    public function traveler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function days(): HasMany
    {
        return $this->hasMany(TourPlanDay::class)->orderBy('day_number');
    }

    public function interestLabels(): array
    {
        return $this->interests ?? [];
    }
}
