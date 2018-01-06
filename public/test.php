<?php
$response = json_decode(file_get_contents('https://api.vk.com/method/messages.getDialogs?count=200&v=5.69&access_token=7000237610e3b407d57850f88e13a7adbe5c0ebaa69afb65615c163e51b3bbaa5a5d2760a828dbaf6de93'));
$items = $response->response->items;
$dialogs_arr = [];

foreach ($items as $item) {
    array_push($dialogs_arr, $item->message->user_id);
}

$rating_arr = [];
foreach ($dialogs_arr as $dialog) {
    $response = json_decode(file_get_contents('https://api.vk.com/method/messages.search?q=Обновление&peer_id=' . $dialog . '&count=100&v=5.69&access_token=7000237610e3b407d57850f88e13a7adbe5c0ebaa69afb65615c163e51b3bbaa5a5d2760a828dbaf6de93'));
    $rating_arr[$dialog] = count($response->response->items);
}
arsort($rating_arr);

require_once '../vendor/autoload.php';

$mongo = new \MongoDB\Client();
$db = $mongo->tomin_weather;
$collection = $db->rating;

foreach ($rating_arr as $key => $value) {
    $result = $collection->insertOne(['user_id' => $key, 'count' => $value]);
    echo 'ID: ' . $key . ' | Link: https://vk.com/id' . $key . ' | Count: ' . $value . '<br>';
}