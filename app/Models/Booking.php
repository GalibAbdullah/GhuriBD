<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Str;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'traveler_id',
        'bookable_type',
        'bookable_id',
        'party_size',
        'unit_price',
        'total_price',
        'status',
        'cancellation_reason',
        'cancelled_at',
    ];

    protected function casts(): array
    {
        return [
            'party_size' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'status' => BookingStatus::class,
            'cancelled_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            $booking->reference ??= self::generateReference();
        });
    }

    /**
     * A short, human-readable code for support lookups — "GBK-7F3K9Q" reads
     * over the phone far better than an autoincrement id or a full UUID.
     */
    public static function generateReference(): string
    {
        do {
            $candidate = 'GBK-'.strtoupper(Str::random(6));
        } while (self::query()->where('reference', $candidate)->exists());

        return $candidate;
    }

    public function traveler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function bookable(): MorphTo
    {
        return $this->morphTo();
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function latestPayment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }

    public function isPendingPayment(): bool
    {
        return $this->status === BookingStatus::PENDING_PAYMENT;
    }

    public function isConfirmed(): bool
    {
        return $this->status === BookingStatus::CONFIRMED;
    }

    public function isCancelled(): bool
    {
        return $this->status === BookingStatus::CANCELLED;
    }

    /**
     * A pending booking whose slot has already started was never paid for in
     * time. Computed rather than stored, so no scheduler is needed to keep it
     * accurate — it is simply true the moment "now" passes the slot's start.
     */
    public function isExpired(): bool
    {
        return $this->isPendingPayment() && $this->slotHasStarted();
    }

    public function slotHasStarted(): bool
    {
        $bookable = $this->bookable;

        return $bookable === null || $bookable->startsAt()->isPast();
    }

    public function canBePaid(): bool
    {
        return $this->isPendingPayment() && ! $this->isExpired() && $this->bookable !== null;
    }

    /**
     * A confirmed booking is only cancellable outside the guide's cancellation
     * window; a still-unpaid one can always be dropped since it never held a
     * confirmed seat.
     */
    public function canBeCancelled(): bool
    {
        if ($this->isCancelled() || $this->slotHasStarted()) {
            return false;
        }

        if ($this->isPendingPayment()) {
            return true;
        }

        if (! $this->isConfirmed()) {
            return false;
        }

        $hoursUntilStart = now()->diffInHours($this->bookable->startsAt(), false);

        return $hoursUntilStart >= config('ghuribd.booking.cancellation_window_hours');
    }
}
