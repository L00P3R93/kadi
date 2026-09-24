<?php

namespace App\Referrals;

use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * The QR code for a referral link, as an SVG data URI (KadiApi accepts any data URI up to 500,000
 * characters; this is a few KB). SVG because bacon renders PNG only through imagick, which the
 * servers do not have. The referral page turns it into a PNG in the browser for download.
 */
class ReferralQr
{
    public const MAX_LENGTH = 500_000;

    public static function dataUri(string $link, int $size = 320): string
    {
        $svg = (new Writer(new ImageRenderer(new RendererStyle($size, 2), new SvgImageBackEnd)))->writeString($link);

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
