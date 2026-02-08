<?php
// app/services/WebhookService.php



class WebhookService
{
    private $httpClient;
    private $sharedToken;

    public function __construct()
    {
        // IMPORTANTE: Asegurar que HTTPClient se carga
        require_once __DIR__ . '/../../utils/HTTPClient.php';
        
        // Corrección: HTTPClient tiene namespace Utils, usar con \
        $this->httpClient = new \Utils\HTTPClient();
        
        // Token - puedes usar getenv o valor fijo
        $this->sharedToken = '5ba0f659-d18b-4edd-82b6-ed115eafa3c9';
        
        // O si prefieres variables de entorno:
        // $this->sharedToken = getenv('N8N_SHARED_TOKEN') ?: '5ba0f659-d18b-4edd-82b6-ed115eafa3c9';
    }

    /**
     * Enviar webhook cuando se crea una review - VERSIÓN MEJORADA
     * Ahora acepta array completo de datos
     */
    public function sendReviewCreated(array $data)
    {
        // URL dentro de Docker (entre contenedores)
        $url = 'http://mvc_n8n:5678/webhook/review-created';
        
        // O si pruebas desde fuera:
        // $url = 'http://localhost:5678/webhook/review-created';
        
        // Estructura que espera n8n (más simple)
        $payload = [
            // Mantén compatibilidad con tu código actual
            'review_id' => $data['review_id'],
            'song_id' => $data['song_id'] ?? null,
            'song_title' => $data['song_title'] ?? ($data['song_title'] ?? 'Canción'),
            'song_artist' => $data['artist'] ?? ($data['song_artist'] ?? 'Artista'),
            'album' => $data['album'] ?? 'Álbum desconocido',
            'duration' => $data['duration'] ?? '--:--',
            'genre' => $data['genre'] ?? 'No especificado',
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? '',
            'user_id' => $data['user_id'],
            'user_email' => $data['user_email'] ?? ($data['email'] ?? 'usuario@ejemplo.com'),
            'username' => $data['username'] ?? ($data['user_name'] ?? 'Usuario'),
            'created_at' => $data['created_at'] ?? date('Y-m-d H:i:s'),
            'timestamp' => $data['timestamp'] ?? time(),
            'url' => $data['url'] ?? 'http://localhost:8081'
        ];

        return $this->sendWebhook($url, $payload);
    }

    /**
     * Enviar webhook cuando se elimina una review - VERSIÓN MEJORADA
     */
    public function sendReviewDeleted(array $data)
    {
        $url = 'http://mvc_n8n:5678/webhook/review-deleted';
        
        $payload = [
            'review_id' => $data['review_id'],
            'song_id' => $data['song_id'] ?? null,
            'song_title' => $data['song_title'] ?? 'Canción',
            'song_artist' => $data['song_artist'] ?? 'Artista',
            'user_id' => $data['user_id'],
            'user_email' => $data['user_email'] ?? 'usuario@ejemplo.com',
            'username' => $data['username'] ?? 'Usuario',
            'deleted_by' => $data['deleted_by'] ?? null,
            'deleted_at' => $data['deleted_at'] ?? date('Y-m-d H:i:s'),
            'reason' => $data['reason'] ?? 'Eliminado por el usuario'
        ];

        return $this->sendWebhook($url, $payload);
    }

    /**
     * Método privado para enviar webhooks - CON MEJOR LOGGING
     */
    private function sendWebhook($url, $payload)
    {
        try {
            // Log antes de enviar
            error_log("[WebhookService] Enviando a: $url");
            error_log("[WebhookService] Payload: " . json_encode($payload, JSON_PRETTY_PRINT));
            
            $response = $this->httpClient->post($url, [
                'headers' => [
                    'X-Shared-Token' => $this->sharedToken,
                    'Content-Type' => 'application/json'
                ],
                'json' => $payload,
                'timeout' => 5
            ]);

            // Log detallado del resultado
            $success = $response['success'] ?? false;
            $statusCode = $response['status_code'] ?? 0;
            
            error_log("[WebhookService] Respuesta - Éxito: " . ($success ? 'Sí' : 'No') . 
                     ", Código: $statusCode" . 
                     ($response['error'] ? ", Error: " . $response['error'] : ''));

            return $response;
            
        } catch (\Exception $e) {
            // Usar \Exception para namespace global
            error_log("[WebhookService] EXCEPCIÓN: " . $e->getMessage());
            return [
                'success' => false, 
                'error' => $e->getMessage(),
                'exception' => true
            ];
        }
    }
    
    /**
     * Método para testing
     */
    public function testConnection()
    {
        $testData = [
            'review_id' => 999,
            'song_id' => 123,
            'song_title' => 'Canción de prueba',
            'rating' => 5,
            'comment' => 'Esto es una prueba',
            'user_id' => 1,
            'user_email' => 'test@example.com',
            'username' => 'TestUser'
        ];
        
        return $this->sendReviewCreated($testData);
    }
}