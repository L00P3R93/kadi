<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;

class KadiProfileImageService
{
    protected ImageManager $manager;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct()
    {
        $this->manager = ImageManager::usingDriver(Driver::class);
    }

    /**
     * @throws Exception
     */
    public function upload(int $accountId, $file): array
    {
        try {
            // Resize Main Image
            $image = $this->manager
                ->decode($file)
                ->scaleDown(
                    width: 800
                );

            $mainPath = tempnam(
                sys_get_temp_dir(),
                'profile_'
            ).'.png';

            $image->save(
                $mainPath,
                quality: 80
            );

            // Thumbnail
            $thumbnail = $this->manager
                ->decode($file)
                ->cover(
                    width: 200,
                    height: 200
                );

            $thumbPath = tempnam(
                sys_get_temp_dir(),
                'thumb_'
            ).'.png';

            $thumbnail->save(
                $thumbPath,
                quality: 70
            );

            // Upload to game.kadikings.co.ke
            $response = Http::attach(
                'pic', fopen($mainPath, 'r'), basename($mainPath)
            )->attach(
                'thumb', fopen($thumbPath, 'r'), basename($thumbPath)
            )->post(
                config('services.kadi.image_upload_url'),
                [
                    'api_key' => config('services.kadi.image_upload_key'),
                    'account_id' => $accountId,
                ]
            )
                ->throw()
                ->json();

            unlink($mainPath);
            unlink($thumbPath);

            return $response;
        } catch (\Throwable $e) {
            Log::error("Error Uploading to gamesapi: {$e->getMessage()}");
            throw new Exception($e->getMessage());
        }
    }
}
