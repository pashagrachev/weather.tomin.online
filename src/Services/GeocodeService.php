<?php

namespace App\Services;

class GeocodeService extends APIService {
    private static $instance;

    public static function getInstance() {
        if (!self::$instance) {
            self::$instance = new GeocodeService();
        }
        return self::$instance;
    }

    public static function getPlace($latitude, $longitude) {
        $params = '?geocode='.$latitude.','.$longitude.'&format=json';
        $endpoint = getenv('YANDEX_MAPS_GEOCODER_VERSION').'/' . $params;
        $response = self::getInstance()->get(getenv('YANDEX_MAPS_GEOCODER_URL'), $endpoint);
        return $response->response->GeoObjectCollection->featureMember[0]->GeoObject->metaDataProperty->GeocoderMetaData->text;
    }
}