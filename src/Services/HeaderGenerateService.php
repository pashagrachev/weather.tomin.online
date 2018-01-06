<?php

namespace App\Services;

use Intervention\Image\ImageManagerStatic as Image;

class HeaderGenerateService {
    public static function generateHeader($join_photo, $join_first_name, $join_last_name, $top_photo, $top_first_name, $top_last_name) {
        Image::configure(array('driver' => 'gd'));

        function filletPhoto($photo) {
            $image = Image::make(file_get_contents($photo));
            $image->encode('png');

            $width = $image->getWidth();
            $height = $image->getHeight();
            $mask = Image::canvas($width, $height);

            $mask->circle($width, $width/2, $height/2, function ($draw) {
                $draw->background('#fff');
            });

            return $image->mask($mask, false);
        }

        function insertAvatar($photo, $x, $y, $first) {
            $first ? $folder = 'images' : $folder = 'uploads';
            $image = Image::make($folder.'/header.png');
            $image->insert($photo, 'top-left', $x, $y)->save('uploads/header.png');
        }

        insertAvatar(filletPhoto($join_photo), 1030, 95, true);
        insertAvatar(filletPhoto($top_photo), 1030, 260, false);

        function insertText($text, $x, $y) {
            $image = Image::make('uploads/header.png');
            $image->text($text, $x, $y, function($font) {
                $font->file('fonts/Vag_World.ttf');
                $font->size(30);
                $font->color('#304b54');
            });
            $image->save('uploads/header.png');
        }

        insertText($join_first_name, 1150, 140);
        insertText($join_last_name, 1150, 175);
        insertText($top_first_name, 1150, 300);
        insertText($top_last_name, 1150, 335);

        return [
            'image' => '/uploads/header.jpg',
        ];
    }
}