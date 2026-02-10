<?php
// app/services/WebhookService.php

require_once dirname(__DIR__) . '/n8n/utils/HttpClient.php'; // Asegúrate de la ruta correcta

class WebhookService
{
    private $sharedToken;
    private $httpClient;
    
    public function __construct()
    {
        $this->sharedToken = '5ba0f659-d18b-4edd-82b6-ed115eafa3c9';
        $this->httpClient = new HttpClient();
    }
    
    public function sendReviewCreated(array $data)
    {
        $url = 'http://localhost:5678/webhook-test/reviews-created';
        
        error_log("📤 Enviando webhook a: $url");
        error_log("📦 Datos: " . json_encode($data));
        
        return $this->httpClient->post($url, [
            'headers' => [
                'X-Shared-Token' => $this->sharedToken
            ],
            'json' => $data
        ]);
    }
}
?>