<?php

namespace App\Api;

class Lidarr{
    public function get($endpoint) : array{
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, env("LIDARR_URL") . $endpoint);
        $headers = [
            "X-Api-Key: " . env("LIDARR_API")
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);

        return json_decode($response, true);
    }
}