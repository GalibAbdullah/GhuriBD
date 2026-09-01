<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\BookingType;
use App\Enums\PaymentStatus;
use Database\Factories\BookingFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class Booking extends Model
{
    /** @use HasFactory<BookingFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'resort_id',
        'room_id',
        'tour_package_id',
        'booking_type',
        'check_in_date',
        'check_out_date',
        'travel_date',
        'guests',
        'total_amount',
        'booking_status',
        'payment_status',
        'special_request',
    ];

    protected function casts(): array
    {
        return [
            'check_in_date' => 'date',
            'check_out_date' => 'date',
            'travel_date' => 'date',
            'total_amount' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resort(): BelongsTo
    {
        return $this->belongsTo(Resort::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function isResort(): bool
    {
        return $this->booking_type === BookingType::RESORT->value;
    }

    public function isPackage(): bool
    {
        return $this->booking_type === BookingType::PACKAGE->value;
    }

    public function isCombined(): bool
    {
        return $this->booking_type === BookingType::COMBINED->value;
    }

    public function isPending(): bool
    {
        return $this->booking_status === BookingStatus::PENDING->value;
    }

    public function isConfirmed(): bool
    {
        return $this->booking_status === BookingStatus::CONFIRMED->value;
    }

    public function isCancelled(): bool
    {
        return $this->booking_status === BookingStatus::CANCELLED->value;
    }

    public function isCompleted(): bool
    {
        return $this->booking_status === BookingStatus::COMPLETED->value;
    }

    /**
     * The date that determines whether this booking is upcoming or past:
     * the travel date for a package booking, otherwise the check-in date.
     */
    public function relevantDate(): ?Carbon
    {
        return $this->travel_date ?? $this->check_in_date;
    }

    /**
     * A booking can only be cancelled by its owner while it hasn't already
     * been cancelled/completed and its relevant date hasn't passed.
     */
    public function isCancellable(): bool
    {
        if (in_array($this->booking_status, [BookingStatus::CANCELLED->value, BookingStatus::COMPLETED->value], true)) {
            return false;
        }

        $relevantDate = $this->relevantDate();

        return ! $relevantDate || $relevantDate->isFuture() || $relevantDate->isToday();
    }

    public function nights(): ?int
    {
        if (! $this->check_in_date || ! $this->check_out_date) {
            return null;
        }

        return $this->check_in_date->diffInDays($this->check_out_date);
    }

    /**
     * Bookings visible to a Travel Partner: any booking touching a resort or
     * tour package the given user owns.
     */
    public function scopeForPartner(Builder $query, User $partner): Builder
    {
        return $query->where(function (Builder $query) use ($partner): void {
            $query->whereHas('resort', fn (Builder $query) => $query->where('user_id', $partner->id))
                ->orWhereHas('tourPackage', fn (Builder $query) => $query->where('user_id', $partner->id));
        });
    }

    /**
     * Bookings whose relevant date (travel date, or check-in date) is today
     * or later and that are not cancelled.
     */
    public function scopeUpcoming(Builder $query): Builder
    {
        return $query
            ->whereRaw('COALESCE(travel_date, check_in_date) >= ?', [now()->toDateString()])
            ->where('booking_status', '!=', BookingStatus::CANCELLED->value);
    }

    /**
     * Bookings that are in the past, cancelled, or completed.
     */
    public function scopeHistory(Builder $query): Builder
    {
        return $query->where(function (Builder $query): void {
            $query->whereRaw('COALESCE(travel_date, check_in_date) < ?', [now()->toDateString()])
                ->orWhereIn('booking_status', [BookingStatus::CANCELLED->value, BookingStatus::COMPLETED->value]);
        });
    }

    /**
     * Active (Pending/Confirmed) bookings for a room whose stay overlaps the
     * given date range — used both to validate availability and to prevent
     * double-booking under concurrent requests.
     */
    public function scopeOverlappingForRoom(Builder $query, int $roomId, string $checkIn, string $checkOut): Builder
    {
        return $query
            ->where('room_id', $roomId)
            ->whereIn('booking_status', [BookingStatus::PENDING->value, BookingStatus::CONFIRMED->value])
            ->where('check_in_date', '<', $checkOut)
            ->where('check_out_date', '>', $checkIn);
    }

    /**
     * Generate a unique, human-readable booking reference such as
     * "GB-20260901-K3F9QZ".
     */
    public static function generateReference(): string
    {
        do {
            $reference = 'GB-'.now()->format('Ymd').'-'.strtoupper(Str::random(6));
        } while (self::query()->where('booking_reference', $reference)->exists());

        return $reference;
    }

    protected static function booted(): void
    {
        static::creating(function (Booking $booking): void {
            $booking->booking_reference ??= self::generateReference();
        });
    }
}
