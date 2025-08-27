-- =====================================================
-- Database Schema for Marsa Maroc Port Location System
-- =====================================================

-- Create database if not exists
CREATE DATABASE IF NOT EXISTS gestion_operations_portuaires 
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE gestion_operations_portuaires;

-- =====================================================
-- USERS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    company_name VARCHAR(100),
    tax_id VARCHAR(50),
    role ENUM('admin', 'user', 'manager') DEFAULT 'user',
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    last_login TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_username (username),
    INDEX idx_email (email),
    INDEX idx_role (role),
    INDEX idx_status (status)
);

-- =====================================================
-- PORT LOCATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS emplacements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    nom VARCHAR(100) NOT NULL,
    type ENUM('quai', 'digue', 'bassin', 'zone_amarrage') DEFAULT 'quai',
    superficie DECIMAL(10,2) NOT NULL,
    longueur DECIMAL(8,2),
    largeur DECIMAL(8,2),
    profondeur DECIMAL(6,2),
    tarif_horaire DECIMAL(10,2) NOT NULL,
    tarif_journalier DECIMAL(10,2) NOT NULL,
    tarif_mensuel DECIMAL(10,2) NOT NULL,
    etat ENUM('disponible', 'occupe', 'maintenance', 'reserve') DEFAULT 'disponible',
    capacite_navire VARCHAR(100),
    equipements TEXT,
    description TEXT,
    zone VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_code (code),
    INDEX idx_type (type),
    INDEX idx_etat (etat),
    INDEX idx_zone (zone),
    INDEX idx_tarif (tarif_journalier)
);

-- =====================================================
-- RESERVATIONS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS reservations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_reservation VARCHAR(20) UNIQUE NOT NULL,
    user_id INT NOT NULL,
    emplacement_id INT NOT NULL,
    date_debut DATETIME NOT NULL,
    date_fin DATETIME NOT NULL,
    duree_jours INT GENERATED ALWAYS AS (DATEDIFF(date_fin, date_debut)) STORED,
    statut ENUM('en_attente', 'validee', 'refusee', 'terminee', 'annulee') DEFAULT 'en_attente',
    montant_total DECIMAL(12,2) NOT NULL,
    montant_acompte DECIMAL(12,2) DEFAULT 0.00,
    montant_restant DECIMAL(12,2) GENERATED ALWAYS AS (montant_total - montant_acompte) STORED,
    mode_paiement ENUM('especes', 'cheque', 'virement', 'carte') DEFAULT 'virement',
    statut_paiement ENUM('en_attente', 'partiel', 'complete') DEFAULT 'en_attente',
    commentaire TEXT,
    motif_refus TEXT,
    date_validation TIMESTAMP NULL,
    valide_par INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (emplacement_id) REFERENCES emplacements(id) ON DELETE CASCADE,
    FOREIGN KEY (valide_par) REFERENCES users(id) ON DELETE SET NULL,
    
    INDEX idx_numero (numero_reservation),
    INDEX idx_user (user_id),
    INDEX idx_emplacement (emplacement_id),
    INDEX idx_dates (date_debut, date_fin),
    INDEX idx_statut (statut),
    INDEX idx_statut_paiement (statut_paiement)
);

-- Insert default admin user (password: admin123)
INSERT IGNORE INTO users (username, email, password, full_name, role, company_name) VALUES 
('admin', 'admin@marsamaroc.ma', 'admin123', 'Administrateur Principal', 'admin', 'Marsa Maroc'),
('manager', 'manager@marsamaroc.ma', 'admin123', 'Gestionnaire Portuaire', 'manager', 'Marsa Maroc'),
('user1', 'user1@example.com', 'admin123', 'Mohammed Alami', 'user', 'Société Maritime Alami'),
('user2', 'user2@example.com', 'admin123', 'Fatima Benjelloun', 'user', 'Transport Maritime Benjelloun');

-- Insert sample port locations
INSERT IGNORE INTO emplacements (code, nom, type, superficie, longueur, largeur, profondeur, tarif_horaire, tarif_journalier, tarif_mensuel, etat, capacite_navire, equipements, description, zone) VALUES 
('QA1', 'Quai Principal A1', 'quai', 150.50, 120.00, 25.00, 12.50, 25.00, 500.00, 12000.00, 'disponible', 'Navires jusqu\'à 5000 tonnes', 'Grue 50T, Bittes d\'amarrage, Éclairage', 'Quai principal pour navires de commerce international', 'Zone A'),
('QA2', 'Quai Secondaire A2', 'quai', 200.00, 150.00, 30.00, 10.00, 20.00, 400.00, 10000.00, 'disponible', 'Navires jusqu\'à 3000 tonnes', 'Grue 25T, Bittes d\'amarrage', 'Quai pour navires de pêche et cabotage', 'Zone A'),
('QB1', 'Quai B1', 'quai', 120.75, 80.00, 20.00, 8.00, 15.00, 300.00, 7500.00, 'occupe', 'Navires jusqu\'à 1500 tonnes', 'Bittes d\'amarrage, Éclairage', 'Quai pour petits navires et yachts', 'Zone B'),
('QB2', 'Quai B2', 'quai', 180.25, 120.00, 25.00, 9.50, 18.00, 350.00, 8500.00, 'maintenance', 'Navires jusqu\'à 2000 tonnes', 'En rénovation', 'Quai en rénovation complète', 'Zone B'),
('D1', 'Digue Nord', 'digue', 300.00, 250.00, 15.00, 6.00, 12.00, 250.00, 6000.00, 'disponible', 'Navires jusqu\'à 1000 tonnes', 'Bittes d\'amarrage simples', 'Digue pour navires de pêche locale', 'Zone Nord'),
('B1', 'Bassin Ouest', 'bassin', 500.00, 200.00, 100.00, 5.00, 10.00, 200.00, 5000.00, 'disponible', 'Petits navires et bateaux', 'Zone d\'ancrage', 'Bassin protégé pour petits bateaux', 'Zone Ouest');

-- Insert sample reservations
INSERT IGNORE INTO reservations (numero_reservation, user_id, emplacement_id, date_debut, date_fin, statut, montant_total, montant_acompte, mode_paiement, statut_paiement, commentaire) VALUES 
('RES-2024-001', 3, 3, '2024-01-15 08:00:00', '2024-01-20 18:00:00', 'validee', 1500.00, 500.00, 'virement', 'complete', 'Réservation pour navire de pêche'),
('RES-2024-002', 4, 1, '2024-01-25 06:00:00', '2024-01-30 20:00:00', 'en_attente', 2500.00, 0.00, 'virement', 'en_attente', 'Réservation pour navire commercial'),
('RES-2024-003', 3, 5, '2024-02-01 07:00:00', '2024-02-05 17:00:00', 'validee', 1000.00, 1000.00, 'cheque', 'complete', 'Amarrage sur digue'),
('RES-2024-004', 4, 6, '2024-02-10 09:00:00', '2024-02-15 16:00:00', 'en_attente', 1000.00, 0.00, 'virement', 'en_attente', 'Ancrage en bassin');
