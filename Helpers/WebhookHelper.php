<?php
// app/helpers/WebhookHelper.php

class WebhookHelper
{
    private static $sharedToken = '5ba0f659-d18b-4edd-82b6-ed115eafa3c9';
    
    /**
     * Envía webhook cuando se crea una review
     */
    public static function sendReviewCreated($reviewData)
    {
        $url = self::getWebhookUrl('review_created');
        
        $payload = [
            'review_id' => $reviewData['review_id'],
            'song_id' => $reviewData['song_id'],
            'song_title' => $reviewData['song_title'],
            'artist' => $reviewData['artist'],
            'rating' => $reviewData['rating'],
            'comment' => $reviewData['comment'],
            'user_id' => $reviewData['user_id'],
            'user_email' => $reviewData['user_email'],
            'username' => $reviewData['username'],
            'created_at' => $reviewData['created_at'],
            'action' => $reviewData['action'],
            'summary' => self::createSummary($reviewData['comment']),
            'link' => self::createReviewLink($reviewData['review_id'])
        ];
        
        return self::send($url, $payload);
    }
    
    /**
     * Envía webhook cuando se elimina una review
     */
    public static function sendReviewDeleted($reviewData)
    {
        $url = self::getWebhookUrl('review_deleted');
        
        $payload = [
            'review_id' => $reviewData['review_id'],
            'song_title' => $reviewData['song_title'],
            'artist' => $reviewData['artist'],
            'user_email' => $reviewData['user_email'],
            'username' => $reviewData['username'],
            'deleted_at' => date('Y-m-d H:i:s'),
            'action' => 'deleted'
        ];
        
        return self::send($url, $payload);
    }
    
    /**
     * Envía webhook cuando se actualiza una review
     */
    public static function sendReviewUpdated($reviewData)
    {
        $url = self::getWebhookUrl('review_updated');
        
        $payload = [
            'review_id' => $reviewData['review_id'],
            'song_title' => $reviewData['song_title'],
            'artist' => $reviewData['artist'],
            'old_rating' => $reviewData['old_rating'],
            'new_rating' => $reviewData['rating'],
            'comment' => $reviewData['comment'],
            'user_email' => $reviewData['user_email'],
            'username' => $reviewData['username'],
            'updated_at' => date('Y-m-d H:i:s'),
            'action' => 'updated',
            'summary' => self::createSummary($reviewData['comment']),
            'link' => self::createReviewLink($reviewData['review_id'])
        ];
        
        return self::send($url, $payload);
    }
    
    /**
     * Obtiene la URL del webhook según el tipo
     */
    private static function getWebhookUrl($type)
    {
        $urls = [
            'review_created' => 'http://localhost:5678/webhook-test/review-created',
            'review_updated' => 'http://localhost:5678/webhook-test/review-updated',
            'review_deleted' => 'http://localhost:5678/webhook-test/review-deleted'
        ];
        
        return $urls[$type] ?? null;
    }
    
    /**
     * Crea un resumen del comentario
     */
    private static function createSummary($comment, $length = 150)
    {
        $cleanComment = strip_tags($comment);
        if (strlen($cleanComment) > $length) {
            return substr($cleanComment, 0, $length) . '...';
        }
        return $cleanComment;
    }
    
    /**
     * Crea el link a la review
     */
    private static function createReviewLink($reviewId)
    {
        $baseUrl = 'http://localhost:8081';
        return $baseUrl . "/views/reviews/show.php?id=" . $reviewId;
    }
    
    /**
     * Método principal para enviar webhooks
     */
    private static function send($url, $payload)
{
    if (!$url) {
        error_log("⚠️ URL de webhook no configurada");
        return ['success' => false, 'error' => 'URL no configurada'];
    }
    
    $ch = curl_init($url);
    
    $headers = [
        'Content-Type: application/json',
        'X-Shared-Token: ' . self::$sharedToken,
        'Accept: application/json'
    ];
    
    // CONFIGURACIÓN EXPLÍCITA PARA POST
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'POST',  // Fuerza POST explícitamente
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_CONNECTTIMEOUT => 5,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_MAXREDIRS => 10
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    $success = ($httpCode >= 200 && $httpCode < 300);
    
    // Log detallado
    error_log("📤 [POST] Webhook enviado a: $url");
    error_log("📦 Payload size: " . strlen(json_encode($payload)) . " bytes");
    error_log("📥 HTTP Code: $httpCode, Success: " . ($success ? 'Sí' : 'No'));
    
    if (!$success) {
        error_log("❌ Error: " . ($error ?: 'HTTP ' . $httpCode));
    } else {
        error_log("✅ POST exitoso");
    }
    
    return [
        'success' => $success,
        'http_code' => $httpCode,
        'response' => $response,
        'error' => $error
    ];
}
    
    /**
     * Test de conexión con n8n
     */
    public static function testConnection()
    {
        $testData = [
            'review_id' => 999,
            'song_id' => 123,
            'song_title' => 'Test Song',
            'artist' => 'Test Artist',
            'rating' => 5,
            'comment' => 'This is a test review from WebhookHelper',
            'user_id' => 1,
            'user_email' => 'test@example.com',
            'username' => 'testuser',
            'created_at' => date('Y-m-d H:i:s'),
            'action' => 'test'
        ];
        
        return self::sendReviewCreated($testData);
    }
}