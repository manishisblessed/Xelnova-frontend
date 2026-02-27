<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class FileUrl
{
    /**
     * Get a secure URL for the given path.
     *
     * @param string|null $path The file path.
     * @param int $ttl Minutes the link should be valid for (default 60).
     * @return string|null
     */
    public static function get(?string $path, int $ttl = 60): ?string
    {
        if (!$path) {
            return null;
        }

        // If it's already a full URL, return it
        if (filter_var($path, FILTER_VALIDATE_URL)) {
            return $path;
        }

        $disk = Config::get('filesystems.default');
        $cacheKey = "file_url_{$disk}_{$path}_{$ttl}";

        // Try to get from cache first
        return Cache::remember($cacheKey, now()->addMinutes($ttl - 5), function () use ($disk, $path, $ttl) {
            $storage = Storage::disk($disk);

            // If disk supports temporary URLs (e.g., S3 private)
            if (method_exists($storage, 'temporaryUrl')) {
                try {
                    return $storage->temporaryUrl(
                        $path,
                        now()->addMinutes($ttl)
                    );
                } catch (\Throwable $e) {
                    // Fallback to standard URL if signing fails
                    return $storage->url($path);
                }
            }

            // Fallback for public disks (local/public/CDN)
            return $storage->url($path);
        });
    }
}
