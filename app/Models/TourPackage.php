<?php

namespace App\Models;

use App\Enums\TourPackageStatus;
use App\Support\StorageImage;
use Database\Factories\TourPackageFactory;
use Illuminate\Database\Eloquent\Builder;
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
     * Case-insensitive, partial-match search across the package's title,
     * destination, location fields, description, and included services
     * (matched as raw JSON text so "guide" still finds ["Guide"]).
     */
    public function scopeSearchKeyword(Builder $query, string $keyword): Builder
    {
        $term = '%'.mb_strtolower($keyword).'%';

        return $query->where(function (Builder $query) use ($term): void {
            $query->whereRaw('LOWER(title) LIKE ?', [$term])
                ->orWhereRaw('LOWER(destination) LIKE ?', [$term])
                ->orWhereRaw('LOWER(division) LIKE ?', [$term])
                ->orWhereRaw('LOWER(district) LIKE ?', [$term])
                ->orWhereRaw('LOWER(description) LIKE ?', [$term])
                ->orWhereRaw('LOWER(included_services) LIKE ?', [$term]);
        });
    }

    /**
     * Apply the shared advanced-search filters.
     *
     * @param  array{division?: ?string, district?: ?string, destination?: ?string, min_price?: ?float, max_price?: ?float, duration?: ?string, max_travelers?: ?int}  $filters
     */
    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['division'] ?? null, fn (Builder $query, string $division) => $query->where('division', $division))
            ->when($filters['district'] ?? null, fn (Builder $query, string $district) => $query->where('district', $district))
            ->when($filters['destination'] ?? null, function (Builder $query, string $destination): void {
                $term = '%'.mb_strtolower($destination).'%';

                $query->where(function (Builder $query) use ($term): void {
                    $query->whereRaw('LOWER(title) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(destination) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(division) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(district) LIKE ?', [$term]);
                });
            })
            ->when($filters['min_price'] ?? null, fn (Builder $query, $min) => $query->where('price', '>=', $min))
            ->when($filters['max_price'] ?? null, fn (Builder $query, $max) => $query->where('price', '<=', $max))
            ->when($filters['duration'] ?? null, function (Builder $query, string $duration): void {
                match ($duration) {
                    '1-3' => $query->whereBetween('duration_days', [1, 3]),
                    '4-7' => $query->whereBetween('duration_days', [4, 7]),
                    '8+' => $query->where('duration_days', '>=', 8),
                    default => null,
                };
            })
            ->when($filters['max_travelers'] ?? null, fn (Builder $query, $count) => $query->where('max_travelers', '>=', $count));
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
