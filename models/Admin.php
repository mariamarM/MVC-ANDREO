<?php
require_once 'Model.php';
class Admin extends Model
{
    public function getAllUsers()
    {
        $sql = "SELECT id, username, email, role, created_at 
                FROM users 
                ORDER BY 
                    CASE WHEN role = 'admin' THEN 1 
                         WHEN role = 'user' THEN 2 
                         ELSE 3 
                    END,
                    created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getAllReviews()
    {
        $sql = "SELECT r.*, u.username, s.title as song_title 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                LEFT JOIN canciones s ON r.song_id = s.id 
                ORDER BY r.created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getAllSongs()
    {
        $sql = "SELECT * FROM canciones ORDER BY release_year DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function searchUsers($search)
    {
        $sql = "SELECT id, username, email, role, created_at 
                FROM users 
                WHERE username LIKE ? OR email LIKE ? 
                ORDER BY 
                    CASE WHEN role = 'admin' THEN 1 
                         WHEN role = 'user' THEN 2 
                         ELSE 3 
                    END,
                    created_at DESC";
        $stmt = $this->db->prepare($sql);
        $searchTerm = "%{$search}%";
        $stmt->execute([$searchTerm, $searchTerm]);
        return $stmt->fetchAll();
    }

    public function searchReviews($search)
    {
        $sql = "SELECT r.*, u.username, s.title as song_title 
                FROM reviews r 
                LEFT JOIN users u ON r.user_id = u.id 
                LEFT JOIN songs s ON r.song_id = s.id 
                WHERE r.comment LIKE ? OR u.username LIKE ? OR s.title LIKE ? 
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
                ORDER BY release_year DESC";
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
                (SELECT COUNT(*) FROM canciones) as total_songs";
        $stmt = $this->db->query($sql);
        return $stmt->fetch();
    }
}
?>