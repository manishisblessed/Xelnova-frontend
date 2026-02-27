<?php

use App\Support\FileUrl;

if (!function_exists('file_url')) {
    /**
     * Get a secure URL for the given file path.
     *
     * @param string|null $path
     * @param int $ttl Minutes valid (default 60)
     * @return string|null
     */
    function file_url(?string $path, int $ttl = 60): ?string
    {
        return FileUrl::get($path, $ttl);
    }
}
