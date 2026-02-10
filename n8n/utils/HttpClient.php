<?php
class HttpClient {
    public function post($url, $data) {
        $ch = curl_init($url);
        
        // Asegúrate de que el payload se codifica como JSON
        $payload = json_encode($data['json'] ?? $data);
       
        $headers = [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            'Accept: application/json'
        ];
 
        // Añadimos headers personalizados (ej: Token)
        if (isset($data['headers'])) {
            foreach ($data['headers'] as $key => $value) {
                $headers[] = "$key: $value";
            }
        }
        
        // DEBUG: Ver qué se envía
        error_log("📤 HttpClient enviando a: $url");
        error_log("📦 Payload: " . $payload);
        error_log("📋 Headers: " . implode(', ', $headers));
 
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0
        ]);
 
        $result = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        
        curl_close($ch);
        
        // DEBUG: Ver respuesta
        error_log("📥 HttpClient respuesta HTTP: $httpCode");
        error_log("📥 Error: " . ($error ?: 'Ninguno'));
        error_log("📥 Result: " . substr($result, 0, 200));
        
        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'response' => $result,
            'error' => $error
        ];
    }
}
?>