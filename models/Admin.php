<?php

class Admin extends Model
{
    
    public function getAll()
    {
        $sql = "SELECT id, username, email, role, created_at 
                FROM users ORDER BY created_at DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }
    public function create($username, $email, $password, $role = 'subscriber'): mixed
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (username, email, password_hash, role) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$username, $email, $hash, $role]);
    }

} ?>