<?php
/**
 * Classe de gestion des utilisateurs
 * Marsa Maroc Port Management System
 */

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function authenticate($username, $password) {
        // Logique d'authentification
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return false;
    }
    
    public function getAllUsers() {
        $stmt = $this->db->query("SELECT * FROM users ORDER BY id DESC");
        return $stmt->fetchAll();
    }
}
