<?php

namespace App\Models;

use Database\Factories\ResortImageFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class ResortImage extends Model
{
    /** @use HasFactory<ResortImageFactory> */
    use HasFactory;

    protected $fillable = [
        'resort_id',
        'image_path',
    ];

    /**
     * The resort this gallery image belongs to.
     */
    public function resort(): BelongsTo
    {
        return $this->belongsTo(Resort::class);
    }

    /**
     * Serve the gallery image from Laravel Storage (public disk).
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn () => asset('storage/'.ltrim($this->image_path, '/')),
        );
    }

    /**
     * Delete the stored file when the model is removed.
     */
    protected static function booted(): void
    {
        static::deleting(function (ResortImage $image): void {
            Storage::disk('public')->delete($image->image_path);
        });
    }
}
