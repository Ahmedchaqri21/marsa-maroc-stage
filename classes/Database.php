<?php
/**
 * Classe de gestion de base de données
 * Marsa Maroc Port Management System
 */

class Database {
    private static $instance = null;
    private $pdo;
    
    private function __construct() {
        require_once __DIR__ . '/../config/database.php';
        $this->pdo = getDBConnection();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}
