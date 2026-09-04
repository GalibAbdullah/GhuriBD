<?php

namespace App\Models;

use App\Enums\ResortStatus;
use App\Support\StorageImage;
use Database\Factories\ResortFactory;
use Illuminate\Database\Eloquent\Builder;
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
        'latitude',
        'longitude',
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
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
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
     * Travelers who have saved this resort to their wishlist.
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Reviews left for this resort.
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Average star rating across all reviews, rounded to 1 decimal place.
     */
    public function averageRating(): float
    {
        return round((float) $this->reviews()->avg('rating'), 1);
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
     * Whether the Travel Partner has pinned this resort's location.
     */
    public function hasCoordinates(): bool
    {
        return $this->latitude !== null && $this->longitude !== null;
    }

    /**
     * A link that opens this resort's location directly in Google Maps.
     */
    public function googleMapsUrl(): ?string
    {
        if (! $this->hasCoordinates()) {
            return null;
        }

        return "https://www.google.com/maps?q={$this->latitude},{$this->longitude}";
    }

    /**
     * Case-insensitive, partial-match search across the resort's name,
     * location fields, description, and amenities (matched as raw JSON text
     * so "pool" still finds "Swimming Pool").
     */
    public function scopeSearchKeyword(Builder $query, string $keyword): Builder
    {
        $term = '%'.mb_strtolower($keyword).'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->whereRaw('LOWER(name) LIKE ?', [$term])
                ->orWhereRaw('LOWER(division) LIKE ?', [$term])
                ->orWhereRaw('LOWER(district) LIKE ?', [$term])
                ->orWhereRaw('LOWER(address) LIKE ?', [$term])
                ->orWhereRaw('LOWER(description) LIKE ?', [$term])
                ->orWhereRaw('LOWER(amenities) LIKE ?', [$term]);
        });
    }

    /**
     * Apply the shared advanced-search filters. Resorts have no numeric price
     * column of their own, so the price range filter matches against the
     * price of any room belonging to the resort.
     *
     * @param  array{division?: ?string, district?: ?string, destination?: ?string, min_price?: ?float, max_price?: ?float, amenities?: ?array<int, string>}  $filters
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['division'] ?? null, fn (Builder $query, string $division) => $query->where('division', $division))
            ->when($filters['district'] ?? null, fn (Builder $query, string $district) => $query->where('district', $district))
            ->when($filters['destination'] ?? null, function (Builder $query, string $destination): void {
                $term = '%'.mb_strtolower($destination).'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(division) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(district) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(address) LIKE ?', [$term]);
                });
            })
            ->when(
                ($filters['min_price'] ?? null) !== null || ($filters['max_price'] ?? null) !== null,
                function (Builder $query) use ($filters): void {
                    $query->whereHas('rooms', function (Builder $query) use ($filters): void {
                        $query->when($filters['min_price'] ?? null, fn (Builder $query, $min) => $query->where('price_per_night', '>=', $min))
                            ->when($filters['max_price'] ?? null, fn (Builder $query, $max) => $query->where('price_per_night', '<=', $max));
                    });
                }
            )
            ->when(! empty($filters['amenities']), function (Builder $query) use ($filters): void {
                foreach ($filters['amenities'] as $amenity) {
                    $query->whereRaw('LOWER(amenities) LIKE ?', ['%'.mb_strtolower($amenity).'%']);
                }
            });
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
