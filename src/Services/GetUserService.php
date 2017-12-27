<?php

namespace App\Services;

class GetUserService extends APIService {
    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new GetUserService();
        }
        return self::$instance;
    }

    public static function getUser($user_id) {
        $params = 'users.get?user_ids='.$user_id.'&fields=photo_100&v='.getenv('VK_API_VERSION');
        $response = self::getInstance()->get(getenv('VK_API_URL'), $params);
        return [
            'photo' => $response->response[0]->photo_100,
            'first_name' => $response->response[0]->first_name,
            'last_name' => $response->response[0]->last_name
        ];
    }
}