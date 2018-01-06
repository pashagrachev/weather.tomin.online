<?php

namespace App\Services;

class RatingUsersService {
    public static function getRating() {
        $mongo = new \MongoDB\Client();
        $db = $mongo->tomin_weather;
        $collection = $db->rating->find();

        $rating_arr = [];

        foreach ($collection as $item) {
            $rating_arr[$item['user_id']] = $item['count'];
        }

        arsort($rating_arr);

        foreach ($rating_arr as $key => $value) {
            $user_info = GetUserService::getUser($key);
            $top_user = array(
                'first_name' => $user_info['first_name'],
                'last_name' => $user_info['last_name'],
                'photo' => $user_info['photo']
            );
            break;
        }

        return $top_user;
    }
}