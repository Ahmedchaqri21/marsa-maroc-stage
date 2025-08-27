<?php
/**
 * Classe de gestion des emplacements
 * Marsa Maroc Port Management System
 */

class Emplacement {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }
    
    public function getAllEmplacements() {
        $stmt = $this->db->query("SELECT * FROM emplacements ORDER BY id DESC");
        return $stmt->fetchAll();
    }
    
    public function getEmplacementById($id) {
        $stmt = $this->db->prepare("SELECT * FROM emplacements WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
    
    public function createEmplacement($data) {
        $sql = "INSERT INTO emplacements (code, nom, type, superficie, longueur, largeur, 
                tarif_journalier, etat, capacite_navire, equipements) 
                VALUES (:code, :nom, :type, :superficie, :longueur, :largeur, 
                :tarif_journalier, :etat, :capacite_navire, :equipements)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($data);
    }
}
