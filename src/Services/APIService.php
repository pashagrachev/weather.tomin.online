<?php

namespace App\Services;

class APIService {
    private $curl;
    private $params = [];

    public function init() {
        $this->curl = curl_init();
        //curl_setopt($this->curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($this->curl, CURLOPT_HEADER, 0);
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($this->curl, CURLOPT_SSL_VERIFYPEER, false);
    }

    public function get($curl_url, $endpoint) {
        $this->init();
        curl_setopt($this->curl, CURLOPT_URL, $curl_url . $endpoint);
        $out = curl_exec($this->curl);
        curl_close($this->curl);

        return json_decode($out);
    }

    public function post($curl_url, $endpoint) {
        $this->init();
        curl_setopt($this->curl, CURLOPT_POST, true);
        curl_setopt($this->curl, CURLOPT_URL, $curl_url . $endpoint);
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($this->params));
        $out = curl_exec($this->curl);
        curl_close($this->curl);

        return json_decode($out);
    }

    public function build($params) {
        $this->params = $params;

        return $this;
    }
}