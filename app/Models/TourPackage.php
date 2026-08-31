<?php

namespace App\Models;

use App\Enums\TourPackageStatus;
use App\Support\StorageImage;
use Database\Factories\TourPackageFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class TourPackage extends Model
{
    /** @use HasFactory<TourPackageFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'destination',
        'division',
        'district',
        'description',
        'duration_days',
        'duration_nights',
        'price',
        'max_travelers',
        'meeting_point',
        'start_location',
        'itinerary',
        'included_services',
        'excluded_services',
        'cover_image',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'included_services' => 'array',
            'excluded_services' => 'array',
            'price' => 'decimal:2',
        ];
    }

    /**
     * The Travel Partner who owns this tour package.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * The gallery images attached to this tour package.
     */
    public function images(): HasMany
    {
        return $this->hasMany(TourPackageImage::class);
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
        return $this->status === TourPackageStatus::ACTIVE->value;
    }

    public function isInactive(): bool
    {
        return $this->status === TourPackageStatus::INACTIVE->value;
    }

    /**
     * Delete the cover image and every gallery image (each via its own
     * model delete, so TourPackageImage's own file-cleanup hook runs too).
     */
    protected static function booted(): void
    {
        static::deleting(function (TourPackage $tourPackage): void {
            if ($tourPackage->cover_image) {
                Storage::disk('public')->delete($tourPackage->cover_image);
            }

            $tourPackage->images->each->delete();
        });
    }
}
