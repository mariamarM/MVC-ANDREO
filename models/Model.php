<?php
namespace Models;

abstract class Model {
    protected $db;
    
    public function __construct() {
        $this->db = "\config\Database"::getConnection();
    }
    
    protected function executeQuery($sql, $params = []) {
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
}