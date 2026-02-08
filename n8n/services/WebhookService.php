<?php

namespace App\Services;

use Utils\HTTPClient;

class WebhookService
{
    private $httpClient;
    private $sharedToken;

    public function __construct()
    {
        $this->httpClient = new HTTPClient();
        $this->sharedToken = getenv('N8N_SHARED_TOKEN') ?: '5ba0f659-d18b-4edd-82b6-ed115eafa3c9';
    }

    /**
     * Enviar webhook cuando se crea una publicación
     */
    public function sendPostCreated($postId, $title, $content, $userId, $userEmail)
    {
        $url = getenv('N8N_WEBHOOK_POST_CREATED') ?: 'http://mvc_n8n:5678/webhook/post.created';
        
        $payload = [
            'event' => 'post.created',
            'data' => [
                'post_id' => $postId,
                'title' => $title,
                'content' => substr($content, 0, 500), // Limitar contenido
                'user_id' => $userId,
                'user_email' => $userEmail,
                'timestamp' => date('Y-m-d H:i:s'),
                'url' => "http://localhost:8081/posts/$postId"
            ]
        ];

        return $this->sendWebhook($url, $payload);
    }

    /**
     * Enviar webhook cuando se crea una review
     */
    public function sendReviewCreated($reviewId, $songId, $rating, $comment, $userId, $userEmail, $songTitle)
    {
        $url = getenv('N8N_WEBHOOK_REVIEW_CREATED') ?: 'http://mvc_n8n:5678/webhook/review.created';
        
        $payload = [
            'event' => 'review.created',
            'data' => [
                'review_id' => $reviewId,
                'song_id' => $songId,
                'song_title' => $songTitle,
                'rating' => $rating,
                'comment' => substr($comment, 0, 300),
                'user_id' => $userId,
                'user_email' => $userEmail,
                'timestamp' => date('Y-m-d H:i:s'),
                'url' => "http://localhost:8081/songs?id=$songId"
            ]
        ];

        return $this->sendWebhook($url, $payload);
    }

    /**
     * Enviar webhook cuando se elimina una review
     */
    public function sendReviewDeleted($reviewId, $songId, $userId, $userEmail)
    {
        $url = getenv('N8N_WEBHOOK_REVIEW_DELETED') ?: 'http://mvc_n8n:5678/webhook/review.deleted';
        
        $payload = [
            'event' => 'review.deleted',
            'data' => [
                'review_id' => $reviewId,
                'song_id' => $songId,
                'user_id' => $userId,
                'user_email' => $userEmail,
                'deleted_at' => date('Y-m-d H:i:s'),
                'deleted_by' => $_SESSION['user_id'] ?? null
            ]
        ];

        return $this->sendWebhook($url, $payload);
    }

    /**
     * Método privado para enviar webhooks
     */
    private function sendWebhook($url, $payload)
    {
        try {
            $response = $this->httpClient->post($url, [
                'headers' => [
                    'X-Shared-Token' => $this->sharedToken,
                    'Content-Type' => 'application/json'
                ],
                'json' => $payload,
                'timeout' => 5
            ]);

            // Log para depuración
            error_log("Webhook enviado a $url: " . json_encode([
                'success' => $response['success'] ?? false,
                'status_code' => $response['status_code'] ?? 0
            ]));

            return $response;
        } catch (Exception $e) {
            error_log("Error enviando webhook: " . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}