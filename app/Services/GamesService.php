<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GamesService
{
    private const CACHE_TTL_SECONDS = 3600; // 1 hour

    public function all(): Collection
    {
        $dir = public_path('games');
        $cacheKey = $this->getCacheKey($dir);

        $cached = Cache::get($cacheKey);
        if ($cached !== null) {
            return collect($cached);
        }

        $files = glob("$dir/*.{png,jpg,jpeg}", GLOB_BRACE) ?: [];

        $games = collect($files)
            ->map(function (string $filePath): ?array {
                $filename = basename($filePath);
                if (! preg_match('/^(.+?)-(\d+)x(\d+)\.(png|jpg|jpeg)$/i', $filename, $m)) {
                    if (config('app.debug')) {
                        Log::warning("GamesService: skipping file with unexpected name: $filename");
                    }

                    return null;
                }
                [, $slug, $width, $height, $ext] = $m;

                return [
                    'slug'      => $slug,
                    'name'      => Str::title(str_replace(['-', '_'], ' ', $slug)),
                    'file'      => $filename,
                    'path'      => '/games/'.$filename,
                    'thumb'     => '/games/'.$filename,
                    'width'     => (int) $width,
                    'height'    => (int) $height,
                    'extension' => strtolower($ext),
                ];
            })
            ->filter()
            ->values();

        Cache::put($cacheKey, $games->toArray(), self::CACHE_TTL_SECONDS);

        return $games;
    }

    /**
     * Clear the games cache. Call this when adding/removing game files.
     */
    public function clearCache(): void
    {
        $dir = public_path('games');
        Cache::forget($this->getCacheKey($dir));
    }

    private function getCacheKey(string $dir): string
    {
        // Include directory mtime in cache key for automatic cache busting when files change
        $mtime = is_dir($dir) ? filemtime($dir) : 0;

        return 'games_service_all_'.$mtime;
    }
}
