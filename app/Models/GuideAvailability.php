<?php

namespace App\Models;

use App\Enums\AvailabilityStatus;
use Carbon\CarbonInterface;
use Database\Factories\GuideAvailabilityFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

class GuideAvailability extends Model
{
    /** @use HasFactory<GuideAvailabilityFactory> */
    use HasFactory;

    protected $table = 'guide_availabilities';

    protected $fillable = [
        'user_id',
        'available_date',
        'start_time',
        'end_time',
        'capacity',
        'price',
        'status',
        'notes',
    ];

    /**
     * booked_count is owned by the booking system, never by a guide-facing form,
     * so it is deliberately absent from $fillable.
     */
    protected function casts(): array
    {
        return [
            'available_date' => 'date',
            'capacity' => 'integer',
            'booked_count' => 'integer',
            'price' => 'decimal:2',
            'status' => AvailabilityStatus::class,
        ];
    }

    /**
     * The business timezone used to resolve "today" and "now".
     */
    public static function timezone(): string
    {
        return config('ghuribd.timezone', 'Asia/Dhaka');
    }

    public static function today(): Carbon
    {
        return Carbon::now(self::timezone())->startOfDay();
    }

    /**
     * Normalise any accepted time input to zero-padded H:i:s.
     *
     * Overlap detection compares times as strings, and SQLite persists exactly
     * what it is given. Without this, a browser sending "9:00" would sort before
     * "10:00:00" and silently defeat every conflict check.
     */
    public static function normalizeTime(?string $value): ?string
    {
        if ($value === null || trim($value) === '') {
            return null;
        }

        $value = trim($value);

        if (preg_match('/^\d{1,2}:\d{1,2}(:\d{1,2})?$/', $value) !== 1) {
            // Unparseable — hand it back untouched so validation rejects it
            // with a field error instead of throwing.
            return $value;
        }

        $padded = self::padTime($value);

        return Carbon::hasFormat($padded, 'H:i:s')
            ? Carbon::createFromFormat('H:i:s', $padded)->format('H:i:s')
            : $value;
    }

    private static function padTime(string $value): string
    {
        $parts = explode(':', $value);

        $parts = array_map(
            static fn (string $part): string => str_pad($part, 2, '0', STR_PAD_LEFT),
            $parts,
        );

        while (count($parts) < 3) {
            $parts[] = '00';
        }

        return implode(':', array_slice($parts, 0, 3));
    }

    protected function startTime(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => self::normalizeTime($value),
        );
    }

    protected function endTime(): Attribute
    {
        return Attribute::make(
            set: fn (?string $value): ?string => self::normalizeTime($value),
        );
    }

    protected function timeRange(): Attribute
    {
        return Attribute::make(
            get: fn (): string => $this->startsAt()->format('g:i A').' – '.$this->endsAt()->format('g:i A'),
        );
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function startsAt(): CarbonInterface
    {
        return Carbon::parse($this->available_date->toDateString().' '.$this->start_time, self::timezone());
    }

    public function endsAt(): CarbonInterface
    {
        return Carbon::parse($this->available_date->toDateString().' '.$this->end_time, self::timezone());
    }

    public function durationMinutes(): int
    {
        return (int) round($this->startsAt()->diffInMinutes($this->endsAt()));
    }

    /**
     * True once the slot's end time has passed. A slot earlier today is finished;
     * a slot later today is still live and still editable.
     */
    public function hasEnded(): bool
    {
        return $this->endsAt()->isPast();
    }

    public function hasBookings(): bool
    {
        return $this->booked_count > 0;
    }

    public function remainingCapacity(): int
    {
        return max(0, $this->capacity - $this->booked_count);
    }

    public function isFullyBooked(): bool
    {
        return $this->remainingCapacity() === 0;
    }

    /**
     * A slot is frozen once it carries bookings or has already finished —
     * editing either would rewrite history a traveler has already paid against.
     */
    public function canBeModified(): bool
    {
        return ! $this->hasBookings() && ! $this->hasEnded();
    }

    /**
     * Bookable from a traveler's point of view.
     */
    public function isBookable(): bool
    {
        return $this->status === AvailabilityStatus::AVAILABLE
            && ! $this->isFullyBooked()
            && ! $this->hasEnded();
    }

    public function scopeForGuide(Builder $query, User|int $guide): Builder
    {
        return $query->where('user_id', $guide instanceof User ? $guide->id : $guide);
    }

    public function scopeOnDate(Builder $query, string $date): Builder
    {
        return $query->whereDate('available_date', $date);
    }

    /**
     * Half-open interval overlap: two slots clash when each starts before the
     * other ends. Slots that merely touch (09:00-12:00 and 12:00-15:00) do not.
     */
    public function scopeOverlapping(Builder $query, string $date, string $startTime, string $endTime): Builder
    {
        return $query->onDate($date)
            ->where('start_time', '<', self::normalizeTime($endTime))
            ->where('end_time', '>', self::normalizeTime($startTime));
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereDate('available_date', '>=', self::today()->toDateString());
    }

    public function scopePast(Builder $query): Builder
    {
        return $query->whereDate('available_date', '<', self::today()->toDateString());
    }

    public function scopeWithStatus(Builder $query, string $status): Builder
    {
        return $query->where('status', $status);
    }

    /**
     * Bookable from a traveler's point of view, expressed in SQL: available,
     * not full, and not yet ended — the query-side twin of isBookable().
     */
    public function scopeBookable(Builder $query): Builder
    {
        $now = Carbon::now(self::timezone());

        return $query
            ->where('status', AvailabilityStatus::AVAILABLE->value)
            ->whereColumn('booked_count', '<', 'capacity')
            ->where(function (Builder $query) use ($now): void {
                $query->whereDate('available_date', '>', $now->toDateString())
                    ->orWhere(function (Builder $query) use ($now): void {
                        $query->whereDate('available_date', $now->toDateString())
                            ->where('end_time', '>', $now->format('H:i:s'));
                    });
            });
    }

    public function scopeInMonth(Builder $query, int $year, int $month): Builder
    {
        $start = Carbon::create($year, $month, 1, 0, 0, 0, self::timezone());

        return $query->whereBetween('available_date', [
            $start->toDateString(),
            $start->copy()->endOfMonth()->toDateString(),
        ]);
    }
}
