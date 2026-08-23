<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class UnsplashService
{
    protected string $baseUrl = 'https://api.unsplash.com';

    /**
     * Search Unsplash and return one random image.
     */
    public function randomImage(string $query): ?array
    {
        $response = Http::acceptJson()
            ->withHeaders([
                'Authorization' => 'Client-ID '.config('services.unsplash.access_key'),
                'Accept-Version' => 'v1',
            ])
            ->timeout(30)
            ->get("{$this->baseUrl}/search/photos", [
                'query' => $query,
                'per_page' => 20,
                'orientation' => 'squarish',
                'content_filter' => 'high',
            ]);

        if ($response->failed()) {
            throw new RuntimeException(
                "Unsplash API error {$response->status()}: {$response->body()}"
            );
        }

        $results = $response->json('results', []);

        if (empty($results)) {
            return null;
        }

        $photo = $results[array_rand($results)];

        return [
            'id' => $photo['id'] ?? null,

            'url' => $photo['urls']['regular'] ?? null,

            'download_location' => $photo['links']['download_location'] ?? null,

            'description' => $photo['alt_description']
                ?? $photo['description']
                    ?? null,

            'photographer' => [
                'name' => $photo['user']['name'] ?? null,
                'username' => $photo['user']['username'] ?? null,
                'profile_url' => $photo['user']['links']['html'] ?? null,
            ],

            'unsplash_url' => $photo['links']['html'] ?? null,
        ];
    }

    /**
     * Trigger Unsplash's download tracking endpoint.
     *
     * @throws ConnectionException
     */
    public function trackDownload(?string $downloadLocation): void
    {
        if (! $downloadLocation) {
            return;
        }

        Http::acceptJson()
            ->withHeaders([
                'Authorization' => 'Client-ID '.config('services.unsplash.access_key'),
                'Accept-Version' => 'v1',
            ])
            ->timeout(10)
            ->get($downloadLocation);
    }

    /**
     * Download the actual image.
     *
     * @throws ConnectionException
     */
    public function download(string $url): string
    {
        $response = Http::timeout(60)
            ->get($url);

        if ($response->failed()) {
            throw new RuntimeException(
                "Unable to download Unsplash image: HTTP {$response->status()}"
            );
        }

        $contentType = $response->header('Content-Type');

        $extension = match (true) {
            str_contains($contentType, 'png') => 'png',
            str_contains($contentType, 'webp') => 'webp',
            str_contains($contentType, 'avif') => 'avif',
            default => 'jpg',
        };

        $path = tempnam(
            sys_get_temp_dir(),
            'unsplash_'
        );

        $finalPath = "{$path}.{$extension}";

        rename($path, $finalPath);

        file_put_contents(
            $finalPath,
            $response->body()
        );

        return $finalPath;
    }
}
