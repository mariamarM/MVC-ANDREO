<?php

namespace Utils;

class HTTPClient
{
    public function post(string $url, array $options = []): array
    {
        $headers = $options['headers'] ?? [];
        $json = $options['json'] ?? [];
        $timeout = $options['timeout'] ?? 10;

        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($json));
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Content-Type: application/json',
            'Accept: application/json'
        ], $this->formatHeaders($headers)));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'status_code' => $httpCode,
            'body' => $response,
            'error' => $error ?: null
        ];
    }

    public function get(string $url, array $options = []): array
    {
        $headers = $options['headers'] ?? [];
        $query = $options['query'] ?? [];
        $timeout = $options['timeout'] ?? 10;

        if (!empty($query)) {
            $url .= '?' . http_build_query($query);
        }

        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge([
            'Accept: application/json'
        ], $this->formatHeaders($headers)));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);

        return [
            'success' => $httpCode >= 200 && $httpCode < 300,
            'status_code' => $httpCode,
            'body' => $response,
            'error' => $error ?: null
        ];
    }

    private function formatHeaders(array $headers): array
    {
        $formatted = [];
        foreach ($headers as $key => $value) {
            $formatted[] = "$key: $value";
        }
        return $formatted;
    }
}