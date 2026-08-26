<?php

namespace App\Models;

use Database\Factories\TourPlanDayFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TourPlanDay extends Model
{
    /** @use HasFactory<TourPlanDayFactory> */
    use HasFactory;

    protected $fillable = [
        'tour_plan_id',
        'day_number',
        'title',
        'theme',
        'budget_allocated',
        'description',
        'suggested_availability_id',
    ];

    protected function casts(): array
    {
        return [
            'day_number' => 'integer',
            'budget_allocated' => 'decimal:2',
        ];
    }

    public function tourPlan(): BelongsTo
    {
        return $this->belongsTo(TourPlan::class);
    }

    public function suggestedAvailability(): BelongsTo
    {
        return $this->belongsTo(GuideAvailability::class, 'suggested_availability_id');
    }

    public function hasSuggestion(): bool
    {
        return $this->suggested_availability_id !== null;
    }

    /**
     * A suggestion is only a lead, not a hold — the guide may have since
     * blocked, filled, or removed the slot entirely.
     */
    public function suggestionIsStillBookable(): bool
    {
        return $this->hasSuggestion()
            && $this->suggestedAvailability !== null
            && $this->suggestedAvailability->isBookable();
    }
}
