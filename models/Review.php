<?php

class Review extends Model {
    
    public function getReviewsByAlbum($albumName) {
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
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':album', $albumName);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getReviewStats($songId) {
        $sql = "SELECT 
                    COUNT(*) as total,
                    AVG(rating) as average,
                    SUM(CASE WHEN rating >= 4 THEN 1 ELSE 0 END) as positive
                FROM reviews 
                WHERE song_id = :song_id";
        $stmt = $this->db->prepare($sql);
        $stmt->bindParam(':song_id', $songId);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>