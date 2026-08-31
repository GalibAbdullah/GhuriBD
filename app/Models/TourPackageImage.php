<?php

namespace App\Models;

use App\Support\StorageImage;
use Database\Factories\TourPackageImageFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class TourPackageImage extends Model
{
    /** @use HasFactory<TourPackageImageFactory> */
    use HasFactory;

    protected $fillable = [
        'tour_package_id',
        'image_path',
    ];

    /**
     * The tour package this gallery image belongs to.
     */
    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    /**
     * Serve the gallery image from Laravel Storage (public disk).
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => StorageImage::url($this->image_path, 'images/default-cover.svg'),
        );
    }

    /**
     * Delete the stored file when the model is removed.
     */
    protected static function booted(): void
    {
        static::deleting(function (TourPackageImage $image): void {
            Storage::disk('public')->delete($image->image_path);
        });
    }
}
