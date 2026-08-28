<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;

class StorageImage
{
    /**
     * Resolve a public-disk path to an absolute URL, falling back to a
     * placeholder image when no path is stored or the file is missing
     * on disk (e.g. a broken storage symlink, or a manually removed file).
     */
    public static function url(?string $path, string $fallback): string
    {
        if ($path && Storage::disk('public')->exists($path)) {
            return asset('storage/'.ltrim($path, '/'));
        }

        return asset($fallback);
    }
}
