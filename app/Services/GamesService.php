<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GamesService
{
    public function all(): Collection
    {
        $dir = public_path('games');
        $files = glob("$dir/*.{png,jpg,jpeg}", GLOB_BRACE) ?: [];

        return collect($files)
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
                    'slug' => $slug,
                    'name' => Str::title(str_replace(['-', '_'], ' ', $slug)),
                    'file' => $filename,
                    'path' => '/games/'.$filename,
                    'width' => (int) $width,
                    'height' => (int) $height,
                    'extension' => strtolower($ext),
                ];
            })
            ->filter()
            ->values();
    }
}
