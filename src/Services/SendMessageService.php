<?php

namespace App\Services;

class SendMessageService extends APIService {
    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new SendMessageService();
        }
        return self::$instance;
    }

    public static function sendMessage($user_id, $message) {
        $params = 'user_id='.$user_id.'&message='.urlencode($message).'&access_token='.getenv('VK_API_ACCESS_TOKEN').'&v='.getenv('VK_API_VERSION');
        $endpoint = 'messages.send';
        $response = self::getInstance()->post(getenv('VK_API_URL').$endpoint, $params);
        return $response;
    }
}