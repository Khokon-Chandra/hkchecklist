<?php

namespace App\Services;

use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageTimestampService
{
    public static function overlay(string $absolutePath, \DateTimeInterface $when): void
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($absolutePath);

        $text = 'Captured: ' . $when->format('Y-m-d H:i:s T');
        $image->text($text, 20, $image->height() - 20, function ($font) {
            $font->size(28);
            $font->color('#ffffff');
            $font->align('left');
            $font->valign('bottom');
            $font->stroke(1, '#000000');
        });

        $image->save($absolutePath, 85);
    }
}
