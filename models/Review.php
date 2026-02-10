<?php
// app/models/Review.php

require_once __DIR__ . '/Model.php';
require_once __DIR__ . '/../services/WebhookService.php';

class Review extends Model {
    
    public function __construct() {
        parent::__construct();
    }
    
    // Métodos existentes que ya tienes
    public function getReviewsByAlbum($albumName) {
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
    
    // ✅ MÉTODO CORREGIDO: Solo crear review, NO enviar webhook aquí
    public function create($userId, $songId, $rating, $comment) {
        $sql = "INSERT INTO reviews (user_id, song_id, rating, comment, created_at) 
                VALUES (?, ?, ?, ?, NOW())";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$userId, $songId, $rating, $comment]);
        
        if ($result) {
            $reviewId = $this->db->lastInsertId();
            
            // 🔴 IMPORTANTE: NO enviar webhook desde aquí
            // El webhook se enviará desde el Controller después de crear
            error_log("✅ Review creada con ID: $reviewId (webhook se enviará desde controller)");
            
            return $reviewId;
        }
        
        return false;
    }
    
    // ✅ MÉTODO CORREGIDO: Solo actualizar, NO enviar webhook aquí
    public function update($reviewId, $rating, $comment, $userId) {
        // Verificar que el usuario es dueño de la review
        $sql = "SELECT user_id FROM reviews WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reviewId]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$review || $review['user_id'] != $userId) {
            error_log("❌ Usuario $userId no puede actualizar review $reviewId");
            return false;
        }
        
        // Actualizar
        $sql = "UPDATE reviews SET rating = ?, comment = ?, updated_at = NOW() WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$rating, $comment, $reviewId]);
        
        if ($result) {
            error_log("✅ Review $reviewId actualizada (webhook se enviará desde controller)");
            return true;
        }
        
        return false;
    }
    
    // ✅ MÉTODO CORREGIDO: Solo eliminar, NO enviar webhook aquí
    public function delete($reviewId) {
        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([$reviewId]);
        
        if ($result) {
            error_log("✅ Review $reviewId eliminada (webhook se enviará desde controller)");
            return true;
        }
        
        return false;
    }
    
    // ✅ Métodos auxiliares que necesitas
    public function getUserReviewForSong($userId, $songId) {
        $sql = "SELECT * FROM reviews WHERE user_id = ? AND song_id = ? LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId, $songId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getReviewsByUserId($userId) {
        $sql = "SELECT r.*, c.title as song_title, c.artist 
                FROM reviews r
                JOIN canciones c ON r.song_id = c.id
                WHERE r.user_id = ?
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function findById($id) {
        $sql = "SELECT r.*, u.username, c.title as song_title, c.artist 
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN canciones c ON r.song_id = c.id
                WHERE r.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getAll() {
        $sql = "SELECT r.*, u.username, c.title as song_title, c.artist 
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN canciones c ON r.song_id = c.id
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // ✅ Métodos para obtener información (útil para webhooks)
    public function getUserInfo($userId) {
        $sql = "SELECT username, email FROM users WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getSongInfo($songId) {
        $sql = "SELECT title, artist, album FROM canciones WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$songId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function getReviewWithDetails($reviewId) {
        $sql = "SELECT r.*, 
                       u.username, u.email as user_email,
                       c.title as song_title, c.artist, c.album
                FROM reviews r
                LEFT JOIN users u ON r.user_id = u.id
                LEFT JOIN canciones c ON r.song_id = c.id
                WHERE r.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reviewId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>