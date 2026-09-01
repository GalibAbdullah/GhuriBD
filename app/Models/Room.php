<?php

namespace App\Models;

use App\Enums\RoomStatus;
use App\Support\StorageImage;
use Database\Factories\RoomFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Room extends Model
{
    /** @use HasFactory<RoomFactory> */
    use HasFactory;

    protected $fillable = [
        'resort_id',
        'room_name',
        'room_type',
        'description',
        'price_per_night',
        'capacity',
        'total_rooms',
        'available_rooms',
        'bed_type',
        'room_size',
        'amenities',
        'cover_image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'amenities' => 'array',
            'price_per_night' => 'decimal:2',
        ];
    }

    /**
     * The resort this room belongs to.
     */
    public function resort(): BelongsTo
    {
        return $this->belongsTo(Resort::class);
    }

    /**
     * The gallery images attached to this room.
     */
    public function images(): HasMany
    {
        return $this->hasMany(RoomImage::class);
    }

    /**
     * All bookings placed against this room.
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

    public function isAvailable(): bool
    {
        return $this->status === RoomStatus::AVAILABLE->value;
    }

    public function isUnavailable(): bool
    {
        return $this->status === RoomStatus::UNAVAILABLE->value;
    }

    /**
     * Delete the cover image and every gallery image (each via its own
     * model delete, so RoomImage's own file-cleanup hook runs too).
     */
    protected static function booted(): void
    {
        static::deleting(function (Room $room): void {
            if ($room->cover_image) {
                Storage::disk('public')->delete($room->cover_image);
            }

            $room->images->each->delete();
        });
    }
}
