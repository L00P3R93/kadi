<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Intervention\Image\Exceptions\DriverException;
use Intervention\Image\Exceptions\ImageDecoderException;
use Intervention\Image\Exceptions\InvalidArgumentException;
use Intervention\Image\Image;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

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
     * @throws ImageDecoderException
     * @throws DriverException
     * @throws InvalidArgumentException
     * @throws ConnectionException
     * @throws RequestException
     */
    public function upload(int $accountId, $file): array
    {
        // Resize Main Image
        $image = $this->manager
            ->decode($file)
            ->scaleDown(
                width: 800
            );

        $mainPath = tempnam(
            sys_get_temp_dir(),
            'profile_'
        ).'.webp';

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
        ).'.webp';

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
                'account_id' => $accountId
            ]
        )
        ->throw()
        ->json();

        unlink($mainPath);
        unlink($thumbPath);

        return $response;
    }
}
