<?php

namespace App\Services;

class WeatherService extends APIService {
    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new WeatherService();
        }
        return self::$instance;
    }

    public static function getWeather($latitude, $longitude) {

        $params = '?lat='.$latitude.'&lon='.$longitude.'&units=metric&lang=ru&APPID='.getenv('OPENWEATHERMAP_API_ACCESS_TOKEN');
        $endpoint = getenv('OPENWEATHERMAP_API_VERSION').'/weather' . $params;
        $response = self::getInstance()->get(getenv('OPENWEATHERMAP_API_URL'), $endpoint);

        $icon = array(
            '01d' => '&#9728;',   '01n' => '&#127769;',
            '02d' => '&#127781;', '02n' => '&#127769; &#9729;',
            '03d' => '&#9729;',   '03n' => '&#9729;',
            '04d' => '&#9729;',   '04n' => '&#9729;',
            '09d' => '&#127783;', '09n' => '&#127783;',
            '10d' => '&#127782;', '10n' => '&#127783;',
            '11d' => '&#127785;', '11n' => '&#127785;',
            '13d' => '&#127784;', '13n' => '&#127784;',
            '50d' => '&#127787;', '50n' => '&#127787;'
        );

        switch ($response->wind->deg) {
            case $response->wind->deg >= 0 && $response->wind->deg <= 22:
                $wind_deg = 'северный';
                break;
            case $response->wind->deg > 22 && $response->wind->deg <= 67:
                $wind_deg = 'северо-восточный';
                break;
            case $response->wind->deg > 67 && $response->wind->deg <= 112:
                $wind_deg = 'восточный';
                break;
            case $response->wind->deg > 112 && $response->wind->deg <= 157:
                $wind_deg = 'юго-восточный';
                break;
            case $response->wind->deg > 157 && $response->wind->deg <= 202:
                $wind_deg = 'южный';
                break;
            case $response->wind->deg > 202 && $response->wind->deg <= 247:
                $wind_deg = 'юго-западный';
                break;
            case $response->wind->deg > 247 && $response->wind->deg <= 292:
                $wind_deg = 'западный';
                break;
            case $response->wind->deg > 292 && $response->wind->deg <= 337:
                $wind_deg = 'северо-западный';
                break;
            case $response->wind->deg > 337 && $response->wind->deg <= 360:
                $wind_deg = 'северный';
                break;
            default:
                $wind_deg = 'недоступно';
        }

        $temperature = round($response->main->temp, 1);
        $humidity = $response->main->humidity;
        $temperature_felt = round($temperature - 0.4 * ($temperature - 10) * (1 - $humidity / 100), 1);

        return [
            'latitude' => $response->coord->lat,
            'longitude' => $response->coord->lon,
            'description' => $response->weather[0]->description,
            'icon' => $icon[$response->weather[0]->icon],
            'temperature' => $temperature,
            'temperature_felt' => $temperature_felt,
            'humidity' => $humidity,
            'pressure' => round($response->main->pressure * 0.750063, 2),
            'clouds' => $response->clouds->all,
            'wind_deg' => $wind_deg,
            'wind_speed' => $response->wind->speed,
            'datetime' => date('d.m.Y в H:i', $response->dt)
        ];
    }
}