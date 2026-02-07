<?php

class Review extends Model {
    
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
}
?>