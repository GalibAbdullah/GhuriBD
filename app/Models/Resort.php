<?php

namespace App\Models;

use App\Enums\ResortStatus;
use App\Support\StorageImage;
use Database\Factories\ResortFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Resort extends Model
{
    /** @use HasFactory<ResortFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'description',
        'division',
        'district',
        'address',
        'contact_phone',
        'price_range',
        'amenities',
        'cover_image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
        ];
    }

    /**
     * The Travel Partner who owns this resort listing.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The gallery images attached to this resort.
     */
    public function images(): HasMany
    {
        return $this->hasMany(ResortImage::class);
    }

    /**
     * The rooms belonging to this resort.
     */
    public function rooms(): HasMany
    {
        return $this->hasMany(Room::class);
    }

    /**
     * All bookings placed against this resort.
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    /**
     * Serve the cover image from Laravel Storage (public disk).
     */
    protected function coverImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => StorageImage::url($this->cover_image, 'images/default-cover.svg'),
        );
    }

    public function isActive(): bool
    {
        return $this->status === ResortStatus::ACTIVE->value;
    }

    public function isInactive(): bool
    {
        return $this->status === ResortStatus::INACTIVE->value;
    }

    /**
     * Delete the cover image and every gallery image (each via its own
     * model delete, so ResortImage's own file-cleanup hook runs too).
     */
    protected static function booted(): void
    {
        static::deleting(function (Resort $resort): void {
            if ($resort->cover_image) {
                Storage::disk('public')->delete($resort->cover_image);
            }

            $resort->images->each->delete();
        });
    }
}
