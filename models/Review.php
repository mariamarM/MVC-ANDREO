<?php

require_once __DIR__ . '/../n8n/utils/HTTPClient.php'; // Para enviar webhooks
class Review extends Model {
       private $http;
    
    public function __construct() {
        parent::__construct();
        $this->http = new HTTPClient();
    }
    public function getReviewsByAlbum($albumName) {
        // PRIMERO: Verificar que recibimos el nombre del álbum
        error_log("Buscando reviews para álbum: " . $albumName);
        
        $sql = "SELECT 
                    r.id,
                    r.song_id,
                    r.user_id,
                    r.rating,
                    r.comment,
                    r.created_at,
                    u.username,
                    c.title as song_title,
                    c.album
                FROM reviews r
                JOIN canciones c ON r.song_id = c.id
                LEFT JOIN users u ON r.user_id = u.id
                WHERE c.album = :album
                ORDER BY r.created_at DESC
                LIMIT 20";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':album', $albumName, PDO::PARAM_STR);
            $stmt->execute();
            
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            error_log("Reviews encontradas: " . count($results));
            
            return $results;
            
        } catch (PDOException $e) {
            error_log("Error en getReviewsByAlbum: " . $e->getMessage());
            return [];
        }
    }
    
    public function getReviewStats($songId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    AVG(rating) as average,
                    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive
                FROM reviews 
                WHERE song_id = :song_id";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':song_id', $songId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getReviewStats: " . $e->getMessage());
            return ['total' => 0, 'average' => 0, 'positive' => 0];
        }
    }
    
    // MÉTODO ADICIONAL PARA DEPURACIÓN: Ver reviews de una canción específica
    public function getReviewsBySong($songId) {
        $sql = "SELECT r.*, u.username 
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                WHERE r.song_id = :song_id
                ORDER BY r.created_at DESC";
        
        try {
            $stmt = $this->db->prepare($sql);
            $stmt->bindParam(':song_id', $songId, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error en getReviewsBySong: " . $e->getMessage());
            return [];
        }
    }
     public function create($userId, $songId, $rating, $comment) {
        $sql = "INSERT INTO reviews (user_id, song_id, rating, comment, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$userId, $songId, $rating, $comment]);
        
        if ($result) {
            $reviewId = $this->db->lastInsertId();
            
            // Obtener info del usuario y canción para el webhook
            $user = $this->getUserInfo($userId);
            $song = $this->getSongInfo($songId);
            
            // Enviar webhook de review creada
            $this->sendReviewCreatedWebhook($reviewId, [
                'review_id' => $reviewId,
                'user_id' => $userId,
                'user_email' => $user['email'] ?? '',
                'user_username' => $user['username'] ?? '',
                'song_id' => $songId,
                'song_title' => $song['title'] ?? '',
                'song_artist' => $song['artist'] ?? '',
                'rating' => $rating,
                'comment' => $comment,
                'created_at' => date('Y-m-d H:i:s')
            ]);
            
            return $reviewId;
        }
        
        return false;
         }
    
    // Actualizar review (con webhook)
    public function update($reviewId, $rating, $comment, $userId) {
        // Primero obtener la review actual
        $sql = "SELECT * FROM reviews WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reviewId]);
        $oldReview = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$oldReview) {
            return false;
        }
        
        // Actualizar
        $sql = "UPDATE reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
         $result = $stmt->execute([$rating, $comment, $reviewId]);
        
        if ($result) {
            // Obtener info del usuario y canción
            $user = $this->getUserInfo($userId);
            $song = $this->getSongInfo($oldReview['song_id']);
            
            // Enviar webhook de review actualizada
            $this->sendReviewUpdatedWebhook($reviewId, [
                'review_id' => $reviewId,
                'user_id' => $userId,
                'user_email' => $user['email'] ?? '',
                'user_username' => $user['username'] ?? '',
                'song_id' => $oldReview['song_id'],
                'song_title' => $song['title'] ?? '',
                'song_artist' => $song['artist'] ?? '',
                'old_rating' => $oldReview['rating'],
                'new_rating' => $rating,
                'old_comment' => $oldReview['comment'],
                'new_comment' => $comment,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            return true;
        }
        
        return false;
    }
      public function delete($reviewId) {
        // Primero obtener los datos de la review antes de eliminar
        $sql = "SELECT r.*, u.email, u.username, c.title as song_title, c.artist 
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN canciones c ON r.song_id = c.id
                WHERE r.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$review) {
            return false;
        }
        
        // Guardar datos para el webhook
        $reviewData = [
            'review_id' => $reviewId,
            'user_id' => $review['user_id'],
            'user_email' => $review['email'] ?? '',
            'user_username' => $review['username'] ?? '',
            'song_id' => $review['song_id'],
            'song_title' => $review['song_title'] ?? '',
            'song_artist' => $review['artist'] ?? '',
            'rating' => $review['rating'],
            'comment' => $review['comment'],
            'deleted_by' => $_SESSION['user_id'] ?? null,
            'deleted_at' => date('Y-m-d H:i:s')
        ];
        
        // Eliminar la review
        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$reviewId]);
        
        if ($result) {
            // Enviar webhook de review eliminada
            $this->sendReviewDeletedWebhook($reviewId, $reviewData);
            return true;
        }
        
        return false;
    }
    
    // Métodos para enviar webhooks
    private function sendReviewCreatedWebhook($reviewId, $data) {
        $url = getenv('N8N_WEBHOOK_REVIEW_CREATED') ?: 'http://mvc_n8n:5678/webhook/review.created';
        $token = getenv('N8N_SHARED_TOKEN') ?: 'default_token';
        
        return $this->http->post($url, [
            'headers' => ['X-Shared-Token' => $token],
            'json' => [
                'event' => 'review.created',
                'data' => $data
            ]
        ]);
    }
    
    private function sendReviewUpdatedWebhook($reviewId, $data) {
        $url = getenv('N8N_WEBHOOK_REVIEW_UPDATED') ?: 'http://mvc_n8n:5678/webhook/review.updated';
        $token = getenv('N8N_SHARED_TOKEN') ?: 'default_token';
        
        return $this->http->post($url, [
            'headers' => ['X-Shared-Token' => $token],
            'json' => [
                'event' => 'review.updated',
                'data' => $data
            ]
        ]);
    }
    
    private function sendReviewDeletedWebhook($reviewId, $data) {
        $url = getenv('N8N_WEBHOOK_REVIEW_DELETED') ?: 'http://mvc_n8n:5678/webhook/review.deleted';
        $token = getenv('N8N_SHARED_TOKEN') ?: 'default_token';
        
        return $this->http->post($url, [
            'headers' => ['X-Shared-Token' => $token],
            'json' => [
                'event' => 'review.deleted',
                'data' => $data
            ]
        ]);
    }
    
    // Métodos auxiliares
    private function getUserInfo($userId) {
        $sql = "SELECT username, email FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    private function getSongInfo($songId) {
        $sql = "SELECT title, artist FROM canciones WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$songId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>

