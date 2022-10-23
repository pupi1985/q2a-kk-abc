<?php

/*
	Question2Answer (c) Gideon Greenspan

	https://www.question2answer.org/

	This program is free software; you can redistribute it and/or
	modify it under the terms of the GNU General Public License
	as published by the Free Software Foundation; either version 2
	of the License, or (at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	More about this license: https://www.question2answer.org/license.php
*/

class KK_ABC_Page
{
    private $directory;

    function load_module($directory, $urltoroot)
    {
        $this->directory = $directory;
    }

    public function match_request($request)
    {
        return $request === 'kk_abc_page';
    }

    public function process_request($request)
    {
        $count = qa_opt('antibotcaptcha_count');
        $font_size_min = 20; // minimum symobl height
        $font_size_max = 32; // maximum symobl height
        $font_file = $this->directory . 'gothic.otf'; // font name, otf or ttfs
        $char_angle_min = -10; // maximum skew of the symbol to the left*/
        $char_angle_max = 10; // maximum skew of the symbol to the right
        $char_angle_shadow = 5; // shadow size
        $char_align = 40; // align symbol verticaly
        $start = 5; // first symbol position
        $interval = 24; // interval between the start position of characters
        $noise = 0; // noise level (0 or positive integer)
        $chars = qa_opt('antibotcaptcha_charset'); // charset
        $width = ($count + 1) * $interval; // image width
        $height = 48; // image height

        $image = imagecreatetruecolor($width, $height);

        $background_color = imagecolorallocate($image, 255, 255, 255); // rbg background color
        $font_color = imagecolorallocate($image, 0, 0, 0); // rbg shadow color

        imagefill($image, 0, 0, $background_color);
        imagecolortransparent($image, $background_color);

        $str = '';

        $num_chars = strlen($chars);
        for ($i = 0; $i < $count; $i++) {
            $char = $chars[rand(0, $num_chars - 1)];
            $font_size = rand($font_size_min, $font_size_max);
            $char_angle = rand($char_angle_min, $char_angle_max);
            imagettftext($image, $font_size, $char_angle, $start, $char_align, $font_color, $font_file, $char);
            imagettftext($image, $font_size, $char_angle + $char_angle_shadow * (rand(0, 1) * 2 - 1), $start, $char_align, $background_color, $font_file, $char);
            $start += $interval;
            $str .= $char;
        }

        $_SESSION['IMAGE_CODE'] = $str;

        $this->applyNoise($noise, $width, $height, $image);

        $this->outputImage($image);
    }

    /**
     * @param $image
     *
     * @return void
     */
    private function outputImage($image)
    {
        if (function_exists('imagepng')) {
            header('Content-type: image/png');
            imagepng($image);
        } else if (function_exists('imagegif')) {
            header('Content-type: image/gif');
            imagegif($image);
        } else if (function_exists('imagejpeg')) {
            header('Content-type: image/jpeg');
            imagejpeg($image);
        }

        imagedestroy($image);
    }

    /**
     * @param int $noise
     * @param $width
     * @param int $height
     * @param $image
     *
     * @return void
     */
    private function applyNoise(int $noise, $width, int $height, $image)
    {
        if ($noise <= 0) {
            return;
        }
        for ($i = 0; $i < $width; $i++) {
            for ($j = 0; $j < $height; $j++) {
                $rgb = imagecolorat($image, $i, $j);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $k = rand(-$noise, $noise);
                $rn = $r + 255 * $k / 100;
                $gn = $g + 255 * $k / 100;
                $bn = $b + 255 * $k / 100;
                if ($rn < 0) {
                    $rn = 0;
                }
                if ($gn < 0) {
                    $gn = 0;
                }
                if ($bn < 0) {
                    $bn = 0;
                }
                if ($rn > 255) {
                    $rn = 255;
                }
                if ($gn > 255) {
                    $gn = 255;
                }
                if ($bn > 255) {
                    $bn = 255;
                }
                $color = imagecolorallocate($image, $rn, $gn, $bn);
                imagesetpixel($image, $i, $j, $color);
            }
        }
    }
}
