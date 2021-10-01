<?php

namespace Pillar\Controllers;

use Symfony\Component\HttpFoundation\Request;

class ImageController {

    private static $width = 100;
    private static $height = 100;
    private static $bgcolor = 'FF357A';

    public static function show() {
        // get image data
        $image_data = self::getImageSettings();

        // hex
        $hex = (string) $image_data[2];

        // convert 3 digit hex to 6 digit
        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        $color = hexdec($hex);

        $r = 0xFF & ($color >> 0x10);
        $g = 0xFF & ($color >> 0x8);
        $b = 0xFF & $color;

        // set header
        header('Content-Type: image/png');

        // create image
        $image = imagecreate($image_data[0], $image_data[1]);

        // set background color
        $background_color = imagecolorallocate($image, $r, $g, $b);

        // imagepng
        imagepng($image);
    }


    public static function getImageSettings() {
        // Get current request
        $request = Request::createFromGlobals();

        // get uri
        $uri = trim($request->getRequestUri(), '/');

        // regex
        $regex = '/(\d+)x(\d+)\/(.*)|(\d+)x(\d+)/m';

        // run regex
        preg_match($regex, $uri, $match);

        // remove empty values
        $match = array_filter($match);

        // if no match, return default
        if (!isset($match) || empty($match)) {
            return [
                self::$width,
                self::$height,
                self::$bgcolor
            ];
        }

        if (!isset($match[2])) {
            $match[2] = self::$bgcolor;
        }

        // unset
        unset($match[0]);

        return array_values($match);
    }
}
