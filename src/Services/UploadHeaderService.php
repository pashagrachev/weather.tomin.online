<?php

namespace App\Services;

use CURLFile;

class UploadHeaderService extends APIService {
    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new UploadHeaderService();
        }
        return self::$instance;
    }

    public static function getServer() {
        $params = 'photos.getOwnerCoverPhotoUploadServer?group_id='.getenv('VK_GROUP_ID').'&crop_x2=1590&crop_y2=400&v='.getenv('VK_API_VERSION').'&access_token='.getenv('VK_API_ACCESS_TOKEN');
        $response = self::getInstance()->get(getenv('VK_API_URL'), $params);
        return $response->response->upload_url;
    }

    public static function uploadPhoto($header) {
        $upload_url = self::getServer();
        $params = array(
            'photo' => new CURLFile($_SERVER['DOCUMENT_ROOT'].$header, 'multipart/form-data', 'header.jpg')
        );
        $response = self::getInstance()->post($upload_url, $params);
        return [
            'hash' => $response->hash,
            'photo' => $response->photo
        ];
    }

    public static function savePhoto($header) {
        $photo_arr = self::uploadPhoto($header);
        $params = 'photos.saveOwnerCoverPhoto?hash='.$photo_arr['hash'].'&photo='.$photo_arr['photo'].'&v='.getenv('VK_API_VERSION').'&access_token='.getenv('VK_API_ACCESS_TOKEN');
        $response = self::getInstance()->get(getenv('VK_API_URL'), $params);
        return $response;
    }
}