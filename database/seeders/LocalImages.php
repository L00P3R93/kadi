<?php

namespace Database\Seeders;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\SplFileInfo;

class LocalImages
{
    public const string SIZE_200x200 = '200x200';

    public const string SIZE_1280x720 = '1280x720';

    public static function getRandomFile(?string $size = LocalImages::SIZE_200x200): Collection|SplFileInfo
    {
        return collect(
            File::files(database_path('seeders/local_images/'.$size))
        )->random();
    }
}
