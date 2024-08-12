<?php

namespace App\Traits;

use Illuminate\Support\Facades\Http;

trait ApiRequest
{
    public static function httpGet($url, $data, $headers = [])
    {
        $response = Http::withHeaders($headers)->get($url, $data);

        return response()->json($response->json(), $response->status());
    }

    public static function httpPost($url, $data, $headers = [])
    {
        $response = Http::withHeaders($headers)->post($url, $data);

        return response()->json($response->json(), $response->status());
    }

    public static function httpPut($url, $data, $headers = [])
    {
        $response = Http::withHeaders($headers)->put($url, $data);

        return response()->json($response->json(), $response->status());
    }

    public static function httpDelete($url, $data, $headers = [])
    {
        $response = Http::withHeaders($headers)->delete($url, $data);

        return response()->json($response->json(), $response->status());
    }
}
