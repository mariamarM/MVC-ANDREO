<?php
// models/Like.php
class Like {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    /**
     * Dar like a un contenido (review o canción)
     */
    public function addLike($user_id, $content_type, $content_id) {
        try {
            // Verificar si ya existe el like
            $stmt = $this->pdo->prepare("
                SELECT id FROM likes 
                WHERE user_id = ? AND content_type = ? AND content_id = ?
            ");
            $stmt->execute([$user_id, $content_type, $content_id]);
            
            if ($stmt->fetch()) {
                return ['success' => false, 'message' => 'Ya has dado like'];
            }
            
            // Insertar nuevo like
            $stmt = $this->pdo->prepare("
                INSERT INTO likes (user_id, content_type, content_id) 
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$user_id, $content_type, $content_id]);
            
            return [
                'success' => true, 
                'message' => 'Like agregado',
                'like_id' => $this->pdo->lastInsertId()
            ];
            
        } catch (PDOException $e) {
            error_log("Error al agregar like: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos'];
        }
    }
    
    /**
     * Quitar like
     */
    public function removeLike($user_id, $content_type, $content_id) {
        try {
            $stmt = $this->pdo->prepare("
                DELETE FROM likes 
                WHERE user_id = ? AND content_type = ? AND content_id = ?
            ");
            $stmt->execute([$user_id, $content_type, $content_id]);
            
            return [
                'success' => true, 
                'message' => 'Like eliminado',
                'affected_rows' => $stmt->rowCount()
            ];
            
        } catch (PDOException $e) {
            error_log("Error al eliminar like: " . $e->getMessage());
            return ['success' => false, 'message' => 'Error en la base de datos'];
        }
    }
    
    /**
     * Verificar si el usuario ha dado like
     */
    public function hasLiked($user_id, $content_type, $content_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT id FROM likes 
                WHERE user_id = ? AND content_type = ? AND content_id = ?
            ");
            $stmt->execute([$user_id, $content_type, $content_id]);
            
            return $stmt->fetch() ? true : false;
            
        } catch (PDOException $e) {
            error_log("Error al verificar like: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Contar likes de un contenido
     */
    public function countLikes($content_type, $content_id) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT COUNT(*) as total FROM likes 
                WHERE content_type = ? AND content_id = ?
            ");
            $stmt->execute([$content_type, $content_id]);
            $result = $stmt->fetch();
            
            return $result ? (int)$result['total'] : 0;
            
        } catch (PDOException $e) {
            error_log("Error al contar likes: " . $e->getMessage());
            return 0;
        }
    }
    
    /**
     * Obtener usuarios que dieron like
     */
    public function getLikers($content_type, $content_id, $limit = 10) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT u.id, u.name, u.email, l.created_at 
                FROM likes l
                JOIN users u ON l.user_id = u.id
                WHERE l.content_type = ? AND l.content_id = ?
                ORDER BY l.created_at DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $content_type, PDO::PARAM_STR);
            $stmt->bindValue(2, $content_id, PDO::PARAM_INT);
            $stmt->bindValue(3, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al obtener likers: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Obtener likes recientes del usuario
     */
    public function getUserLikes($user_id, $limit = 20) {
        try {
            $stmt = $this->pdo->prepare("
                SELECT l.*, 
                       CASE 
                         WHEN l.content_type = 'review' THEN r.comment
                         WHEN l.content_type = 'song' THEN c.title
                       END as content_title,
                       CASE 
                         WHEN l.content_type = 'review' THEN c2.title
                         WHEN l.content_type = 'song' THEN c.artist
                       END as content_subtitle
                FROM likes l
                LEFT JOIN reviews r ON l.content_type = 'review' AND l.content_id = r.id
                LEFT JOIN canciones c ON l.content_type = 'song' AND l.content_id = c.id
                LEFT JOIN canciones c2 ON r.song_id = c2.id
                WHERE l.user_id = ?
                ORDER BY l.created_at DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $user_id, PDO::PARAM_INT);
            $stmt->bindValue(2, $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
            
        } catch (PDOException $e) {
            error_log("Error al obtener likes del usuario: " . $e->getMessage());
            return [];
        }
    }
}