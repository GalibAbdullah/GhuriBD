<?php

namespace App\Models;

use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'booking_id',
        'resort_id',
        'tour_package_id',
        'rating',
        'review_text',
        'partner_reply',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function resort(): BelongsTo
    {
        return $this->belongsTo(Resort::class);
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function hasReply(): bool
    {
        return filled($this->partner_reply);
    }

    /**
     * Reviews visible to a Travel Partner: any review left on a resort or
     * tour package the given user owns.
     */
    public function scopeForPartner(Builder $query, User $partner): Builder
    {
        return $query->where(function (Builder $query) use ($partner): void {
            $query->whereHas('resort', fn (Builder $query) => $query->where('user_id', $partner->id))
                ->orWhereHas('tourPackage', fn (Builder $query) => $query->where('user_id', $partner->id));
        });
    }
}
