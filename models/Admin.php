<?php
require_once 'Model.php';

class Admin extends Model
{
    // Métodos existentes
    public function getAllUsers()
    {
        $sql = "SELECT id, username, email, role, created_at, updated_at 
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
}
?>