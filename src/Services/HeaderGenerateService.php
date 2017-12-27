<?php

namespace App\Services;

class HeaderGenerateService extends APIService {
    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new HeaderGenerateService();
        }
        return self::$instance;
    }

    public static function generateHeader($photo, $first_name, $last_name) {
        file_put_contents('uploads/newavatar.jpg', file_get_contents($photo));

        list($w, $h) = getimagesize('images/header.png');
        $new_image = imagecreatefrompng('images/header.png');

        $color = imagecolorallocatealpha($new_image, 255, 255, 255, 0);
        imagettftext($new_image, 24, 0, $w-1350, $h-190, $color, 'fonts/HelveticaBold.ttf', $first_name);
        imagettftext($new_image, 24, 0, $w-1350, $h-155, $color, 'fonts/HelveticaBold.ttf', $last_name);

        $photoimage = imagecreatefromjpeg('uploads/newavatar.jpg');
        imagealphablending($photoimage, true);

        $logoimage = imagecreatefrompng('images/circle.png');
        $logow = imagesx($logoimage);
        $logoh = imagesy($logoimage);

        imagecopy($photoimage, $logoimage, 0, 0, 0, 0, $logow, $logoh);
        imagejpeg($photoimage, 'uploads/newavatar.jpg');

        list($cw, $ch) = getimagesize('uploads/newavatar.jpg');
        imagecopy($new_image, $photoimage, $w-1480, $h-230, 0, 0, $cw, $ch);

        imagejpeg($new_image, 'uploads/newimage.jpg');

        imagedestroy($photoimage);
        imagedestroy($logoimage);
        imagedestroy($new_image);

        return [
            'image' => 'uploads/newimage.jpg',
        ];
    }
}