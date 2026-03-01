<?php
require_once 'Model.php';

class Admin extends Model
{
    // Métodos existentes
    public function getAllUsers()
    {
        $sql = "SELECT id, username, email, role, created_at 
                FROM users 
                ORDER BY username ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getAllReviews()
    {
        $sql = "SELECT r.*, u.username, c.title as song_title 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                LEFT JOIN canciones c ON r.song_id = c.id 
                ORDER BY r.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    /**
 * Verificar si un usuario existe por ID
 */
public function getUserById($userId) {
    $sql = "SELECT id, username, email, role FROM users WHERE id = ?";
    $stmt = $this->db->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetch();
}
/**
 * Eliminar una review por su ID
 */
public function deleteReview($reviewId) {
    try {
        $sql = "DELETE FROM reviews WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$reviewId]);
    } catch (PDOException $e) {
        error_log("Error al eliminar review: " . $e->getMessage());
        return false;
    }
}

/**
 * Obtener una review por su ID
 */
public function getReviewById($reviewId) {
    try {
        $sql = "SELECT r.*, u.username, c.title as song_title 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                LEFT JOIN canciones c ON r.song_id = c.id 
                WHERE r.id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$reviewId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log("Error al obtener review: " . $e->getMessage());
        return null;
    }
}
public function deleteUser($userId)
{
    try {
        $userId = (int)$userId;
        error_log("Modelo - Iniciando deleteUser para ID: $userId");
        
        // Verificar la conexión a la base de datos
        if (!$this->db) {
            error_log("ERROR: No hay conexión a la base de datos");
            return false;
        }
        
        // Primero, verificar si el usuario existe
        $checkSql = "SELECT id, username, role FROM users WHERE id = ?";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute([$userId]);
        $user = $checkStmt->fetch();
        
        if (!$user) {
            error_log("ERROR: Usuario con ID $userId no existe en la base de datos");
            return false;
        }
        
        error_log("Usuario encontrado: " . $user['username'] . " con rol: " . $user['role']);
        
        // Verificar si tiene reviews asociadas (para mantener integridad referencial)
        $checkReviewsSql = "SELECT COUNT(*) as total FROM reviews WHERE user_id = ?";
        $checkReviewsStmt = $this->db->prepare($checkReviewsSql);
        $checkReviewsStmt->execute([$userId]);
        $reviewsCount = $checkReviewsStmt->fetch()['total'];
        error_log("El usuario tiene $reviewsCount reviews asociadas");
        
        // Iniciar transacción para asegurar consistencia
        $this->db->beginTransaction();
        error_log("Transacción iniciada");
        
        // Eliminar reviews asociadas primero (si existen)
        if ($reviewsCount > 0) {
            $deleteReviewsSql = "DELETE FROM reviews WHERE user_id = ?";
            $deleteReviewsStmt = $this->db->prepare($deleteReviewsSql);
            $reviewsDeleted = $deleteReviewsStmt->execute([$userId]);
            error_log("Reviews eliminadas: " . ($reviewsDeleted ? "sí" : "no"));
            
            if (!$reviewsDeleted) {
                throw new Exception("Error al eliminar reviews asociadas");
            }
        }
        
        // Eliminar el usuario
        $deleteUserSql = "DELETE FROM users WHERE id = ?";
        $deleteUserStmt = $this->db->prepare($deleteUserSql);
        $userDeleted = $deleteUserStmt->execute([$userId]);
        error_log("Usuario eliminado: " . ($userDeleted ? "sí" : "no"));
        
        if (!$userDeleted) {
            throw new Exception("Error al eliminar el usuario");
        }
        
        // Confirmar transacción
        $this->db->commit();
        error_log("Transacción confirmada - USUARIO ELIMINADO CORRECTAMENTE");
        
        return true;
        
    } catch (PDOException $e) {
        // Revertir transacción en caso de error
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
            error_log("Transacción revertida por error");
        }
        
        error_log("ERROR PDO en deleteUser: " . $e->getMessage());
        error_log("Código de error: " . $e->getCode());
        return false;
        
    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
            error_log("Transacción revertida por error");
        }
        
        error_log("ERROR GENERAL en deleteUser: " . $e->getMessage());
        return false;
    }
}
    public function getAllSongs()
    {
        $sql = "SELECT * FROM canciones ORDER BY title ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function searchUsers($search)
    {
        $sql = "SELECT id, username, email, role, created_at 
                FROM users 
                WHERE username LIKE ? OR email LIKE ? 
                ORDER BY username ASC";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%{$search}%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function searchReviews($search)
    {
        $sql = "SELECT r.*, u.username, c.title as song_title 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                LEFT JOIN canciones c ON r.song_id = c.id 
                WHERE r.comment LIKE ? OR u.username LIKE ? OR c.title LIKE ? 
                ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%{$search}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function searchSongs($search)
    {
        $sql = "SELECT * FROM canciones 
                WHERE title LIKE ? OR artist LIKE ? OR album LIKE ? OR genre LIKE ? 
                ORDER BY title ASC";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%{$search}%";
        $stmt->execute([$searchTerm, $searchTerm, $searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function getStats()
    {
        $sql = "SELECT 
                (SELECT COUNT(*) FROM users) as total_users,
                (SELECT COUNT(*) FROM reviews) as total_reviews,
                (SELECT COUNT(*) FROM canciones) as total_songs,
                (SELECT COUNT(*) FROM reproducciones) as total_plays,
                (SELECT COUNT(*) FROM playlists) as total_playlists";
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
    
    // Nuevos métodos optimizados
    
    public function getUsersForSelect()
    {
        $sql = "SELECT id, username, email, role FROM users ORDER BY username ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getSongsForSelect()
    {
        $sql = "SELECT id, title, artist, album, release_year, genre FROM canciones ORDER BY title ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    
    public function getAlbumsForSelect()
    {
        $sql = "SELECT DISTINCT album FROM canciones WHERE album IS NOT NULL AND album != '' ORDER BY album ASC";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        return $results ? $results : [];
    }
    
    public function getGenresForSelect()
    {
        $sql = "SELECT DISTINCT genre FROM canciones WHERE genre IS NOT NULL AND genre != '' ORDER BY genre ASC";
        $stmt = $this->db->query($sql);
        $results = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);
        return $results ? $results : [];
    }
    
    public function createSong($songData)
    {
        $sql = "INSERT INTO canciones (title, artist, album, release_year, genre, duration, file_path, album_cover) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $songData['title'],
            $songData['artist'],
            $songData['album'] ?? null,
            $songData['release_year'] ?? null,
            $songData['genre'] ?? null,
            $songData['duration'] ?? null,
            $songData['file_path'] ?? null,
            $songData['album_cover'] ?? null
        ]);
    }
    
    public function updateUserRole($userId, $newRole)
    {
        $sql = "UPDATE users SET role = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$newRole, $userId]);
    }
    
    public function createAdminUser($adminData)
    {
        $sql = "INSERT INTO users (username, email, password_hash, role) 
                VALUES (?, ?, ?, 'admin')";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $adminData['username'],
            $adminData['email'],
            $adminData['password_hash']
        ]);
    }
    
    public function updateSong($songId, $songData)
    {
        $sql = "UPDATE canciones SET 
                title = ?, 
                artist = ?, 
                album = ?, 
                release_year = ?, 
                genre = ?, 
                duration = ?,
                file_path = COALESCE(?, file_path),
                album_cover = COALESCE(?, album_cover)
                WHERE id = ?";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $songData['title'],
            $songData['artist'],
            $songData['album'] ?? null,
            $songData['release_year'] ?? null,
            $songData['genre'] ?? null,
            $songData['duration'] ?? null,
            $songData['file_path'] ?? null,
            $songData['album_cover'] ?? null,
            $songId
        ]);
    }
    
    public function getSongById($songId)
    {
        $sql = "SELECT * FROM canciones WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$songId]);
        return $stmt->fetch();
    }
    
    public function emailExists($email, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE email = ?";
        $params = [$email];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
    
    public function usernameExists($username, $excludeId = null)
    {
        $sql = "SELECT COUNT(*) FROM users WHERE username = ?";
        $params = [$username];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn() > 0;
    }
     public function createUser($userData)
    {
        $sql = "INSERT INTO users (username, email, password_hash, role, created_at) 
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $userData['username'],
            $userData['email'],
            $userData['password_hash'],
            $userData['role']
        ]);
    }
    
    /**
     * Actualizar información de usuario (sin rol)
     */
    public function updateUser($userId, $userData)
    {
        // Construir consulta dinámica
        $fields = [];
        $params = [];
        
        foreach ($userData as $field => $value) {
            if ($field === 'username') {
                $fields[] = "username = ?";
                $params[] = $value;
            } elseif ($field === 'email') {
                $fields[] = "email = ?";
                $params[] = $value;
            } elseif ($field === 'password_hash') {
                $fields[] = "password_hash = ?";
                $params[] = $value;
            }
        }
        
        if (empty($fields)) {
            return false;
        }
        
        $fields[] = "created_at = CURRENT_TIMESTAMP";
        $sql = "UPDATE users SET " . implode(', ', $fields) . " WHERE id = ?";
        $params[] = $userId;
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Obtener último ID insertado
     */
    public function getLastInsertId()
    {
        return $this->db->lastInsertId();
    }
    
    /**
     * Método auxiliar para crear usuario normal
     */
    public function createNormalUser($userData)
    {
        $userData['role'] = 'user';
        return $this->createUser($userData);
    }


public function deleteSong($id) {
    $stmt = $this->db->prepare("DELETE FROM canciones WHERE id = ?");
    return $stmt->execute([$id]);
}
    
}
?>