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

-- =====================================================
-- PAYMENTS TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS payments (
                                        id INT AUTO_INCREMENT PRIMARY KEY,
                                        reservation_id INT NOT NULL,
                                        montant DECIMAL(12,2) NOT NULL,
                                        mode_paiement ENUM('especes', 'cheque', 'virement', 'carte') NOT NULL,
                                        reference_paiement VARCHAR(100),
                                        statut ENUM('en_attente', 'valide', 'refuse', 'annule') DEFAULT 'en_attente',
                                        date_paiement TIMESTAMP NULL,
                                        commentaire TEXT,
                                        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                                        FOREIGN KEY (reservation_id) REFERENCES reservations(id) ON DELETE CASCADE,

                                        INDEX idx_reservation (reservation_id),
                                        INDEX idx_statut (statut),
                                        INDEX idx_date_paiement (date_paiement)
);

-- =====================================================
-- MAINTENANCE TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS maintenance (
                                           id INT AUTO_INCREMENT PRIMARY KEY,
                                           emplacement_id INT NOT NULL,
                                           type_maintenance ENUM('preventive', 'corrective', 'renovation') NOT NULL,
                                           description TEXT NOT NULL,
                                           date_debut DATE NOT NULL,
                                           date_fin_prevue DATE NOT NULL,
                                           date_fin_reelle DATE NULL,
                                           cout_estime DECIMAL(10,2),
                                           cout_reel DECIMAL(10,2),
                                           statut ENUM('planifiee', 'en_cours', 'terminee', 'annulee') DEFAULT 'planifiee',
                                           responsable VARCHAR(100),
                                           commentaire TEXT,
                                           created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                                           updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

                                           FOREIGN KEY (emplacement_id) REFERENCES emplacements(id) ON DELETE CASCADE,

                                           INDEX idx_emplacement (emplacement_id),
                                           INDEX idx_type (type_maintenance),
                                           INDEX idx_statut (statut),
                                           INDEX idx_dates (date_debut, date_fin_prevue)
);

-- =====================================================
-- AUDIT LOG TABLE
-- =====================================================
CREATE TABLE IF NOT EXISTS audit_log (
                                         id INT AUTO_INCREMENT PRIMARY KEY,
                                         user_id INT NULL,
                                         action VARCHAR(100) NOT NULL,
                                         table_name VARCHAR(50) NOT NULL,
                                         record_id INT NULL,
                                         old_values JSON NULL,
                                         new_values JSON NULL,
                                         ip_address VARCHAR(45),
                                         user_agent TEXT,
                                         created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

                                         FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,

                                         INDEX idx_user (user_id),
                                         INDEX idx_action (action),
                                         INDEX idx_table (table_name),
                                         INDEX idx_record (record_id),
                                         INDEX idx_created (created_at)
);

-- =====================================================
-- SAMPLE DATA INSERTION
-- =====================================================

-- Insert default admin user (password: admin123)
INSERT INTO users (username, email, password, full_name, role, company_name) VALUES
                                                                                 ('admin', 'admin@marsamaroc.ma', 'admin123', 'Administrateur Principal', 'admin', 'Marsa Maroc'),
                                                                                 ('manager', 'manager@marsamaroc.ma', 'admin123', 'Gestionnaire Portuaire', 'manager', 'Marsa Maroc'),
                                                                                 ('user1', 'user1@example.com', 'admin123', 'Mohammed Alami', 'user', 'Société Maritime Alami'),
                                                                                 ('user2', 'user2@example.com', 'admin123', 'Fatima Benjelloun', 'user', 'Transport Maritime Benjelloun');

-- Insert sample port locations
INSERT INTO emplacements (code, nom, type, superficie, longueur, largeur, profondeur, tarif_horaire, tarif_journalier, tarif_mensuel, etat, capacite_navire, equipements, description, zone) VALUES
                                                                                                                                                                                                 ('QA1', 'Quai Principal A1', 'quai', 150.50, 120.00, 25.00, 12.50, 25.00, 500.00, 12000.00, 'disponible', 'Navires jusqu\'à 5000 tonnes', 'Grue 50T, Bittes d\'amarrage, Éclairage', 'Quai principal pour navires de commerce international', 'Zone A'),
                                                                                                                                                                                                 ('QA2', 'Quai Secondaire A2', 'quai', 200.00, 150.00, 30.00, 10.00, 20.00, 400.00, 10000.00, 'disponible', 'Navires jusqu\'à 3000 tonnes', 'Grue 25T, Bittes d\'amarrage', 'Quai pour navires de pêche et cabotage', 'Zone A'),
                                                                                                                                                                                                 ('QB1', 'Quai B1', 'quai', 120.75, 80.00, 20.00, 8.00, 15.00, 300.00, 7500.00, 'occupe', 'Navires jusqu\'à 1500 tonnes', 'Bittes d\'amarrage, Éclairage', 'Quai pour petits navires et yachts', 'Zone B'),
                                                                                                                                                                                                 ('QB2', 'Quai B2', 'quai', 180.25, 120.00, 25.00, 9.50, 18.00, 350.00, 8500.00, 'maintenance', 'Navires jusqu\'à 2000 tonnes', 'En rénovation', 'Quai en rénovation complète', 'Zone B'),
                                                                                                                                                                                                 ('D1', 'Digue Nord', 'digue', 300.00, 250.00, 15.00, 6.00, 12.00, 250.00, 6000.00, 'disponible', 'Navires jusqu\'à 1000 tonnes', 'Bittes d\'amarrage simples', 'Digue pour navires de pêche locale', 'Zone Nord'),
                                                                                                                                                                                                 ('B1', 'Bassin Ouest', 'bassin', 500.00, 200.00, 100.00, 5.00, 10.00, 200.00, 5000.00, 'disponible', 'Petits navires et bateaux', 'Zone d\'ancrage', 'Bassin protégé pour petits bateaux', 'Zone Ouest');

-- Insert sample reservations
INSERT INTO reservations (numero_reservation, user_id, emplacement_id, date_debut, date_fin, statut, montant_total, montant_acompte, mode_paiement, statut_paiement, commentaire) VALUES
                                                                                                                                                                                      ('RES-2024-001', 3, 3, '2024-01-15 08:00:00', '2024-01-20 18:00:00', 'validee', 1500.00, 500.00, 'virement', 'complete', 'Réservation pour navire de pêche'),
                                                                                                                                                                                      ('RES-2024-002', 4, 1, '2024-01-25 06:00:00', '2024-01-30 20:00:00', 'en_attente', 2500.00, 0.00, 'virement', 'en_attente', 'Réservation pour navire commercial'),
                                                                                                                                                                                      ('RES-2024-003', 3, 5, '2024-02-01 07:00:00', '2024-02-05 17:00:00', 'validee', 1000.00, 1000.00, 'cheque', 'complete', 'Amarrage sur digue'),
                                                                                                                                                                                      ('RES-2024-004', 4, 6, '2024-02-10 09:00:00', '2024-02-15 16:00:00', 'en_attente', 1000.00, 0.00, 'virement', 'en_attente', 'Ancrage en bassin');

-- Insert sample payments
INSERT INTO payments (reservation_id, montant, mode_paiement, reference_paiement, statut, date_paiement, commentaire) VALUES
                                                                                                                          (1, 500.00, 'virement', 'VIR-2024-001', 'valide', '2024-01-10 14:30:00', 'Acompte versé'),
                                                                                                                          (1, 1000.00, 'virement', 'VIR-2024-002', 'valide', '2024-01-18 16:45:00', 'Solde versé'),
                                                                                                                          (3, 1000.00, 'cheque', 'CHQ-2024-001', 'valide', '2024-01-28 11:20:00', 'Paiement complet par chèque');

-- Insert sample maintenance records
INSERT INTO maintenance (emplacement_id, type_maintenance, description, date_debut, date_fin_prevue, cout_estime, statut, responsable, commentaire) VALUES
                                                                                                                                                        (4, 'renovation', 'Rénovation complète du quai B2', '2024-01-01', '2024-03-31', 50000.00, 'en_cours', 'Entreprise de Construction Maritime', 'Rénovation des structures et équipements'),
                                                                                                                                                        (2, 'preventive', 'Inspection et maintenance préventive', '2024-02-15', '2024-02-20', 5000.00, 'planifiee', 'Équipe technique interne', 'Vérification des équipements de sécurité');

-- Insert sample audit log entries
INSERT INTO audit_log (user_id, action, table_name, record_id, new_values, ip_address) VALUES
                                                                                           (1, 'CREATE', 'users', 3, '{"username":"user1","email":"user1@example.com","role":"user"}', '192.168.1.100'),
                                                                                           (1, 'CREATE', 'emplacements', 1, '{"code":"QA1","nom":"Quai Principal A1","type":"quai"}', '192.168.1.100'),
                                                                                           (2, 'UPDATE', 'reservations', 1, '{"statut":"validee","date_validation":"2024-01-12 10:00:00"}', '192.168.1.101');

-- =====================================================
-- VIEWS FOR COMMON QUERIES
-- =====================================================

-- View for active reservations with user and emplacement details
CREATE OR REPLACE VIEW v_reservations_actives AS
SELECT
    r.id,
    r.numero_reservation,
    r.date_debut,
    r.date_fin,
    r.duree_jours,
    r.statut,
    r.montant_total,
    r.montant_restant,
    r.statut_paiement,
    u.full_name as client,
    u.company_name,
    e.nom as emplacement,
    e.code as code_emplacement,
    e.zone
FROM reservations r
         JOIN users u ON r.user_id = u.id
         JOIN emplacements e ON r.emplacement_id = e.id
WHERE r.statut IN ('en_attente', 'validee')
ORDER BY r.date_debut;

-- View for emplacement availability
CREATE OR REPLACE VIEW v_disponibilite_emplacements AS
SELECT
    e.id,
    e.code,
    e.nom,
    e.type,
    e.superficie,
    e.tarif_journalier,
    e.etat,
    e.zone,
    CASE
        WHEN e.etat = 'disponible' THEN 'Libre'
        WHEN e.etat = 'occupe' THEN 'Occupé'
        WHEN e.etat = 'maintenance' THEN 'Maintenance'
        WHEN e.etat = 'reserve' THEN 'Réservé'
        END as statut_lisible
FROM emplacements e
ORDER BY e.zone, e.code;

-- View for revenue statistics
CREATE OR REPLACE VIEW v_statistiques_revenus AS
SELECT
    YEAR(r.date_debut) as annee,
    MONTH(r.date_debut) as mois,
    COUNT(*) as nombre_reservations,
    SUM(r.montant_total) as revenus_totaux,
    AVG(r.montant_total) as revenu_moyen,
    SUM(r.montant_acompte) as acomptes_recus,
    SUM(r.montant_restant) as montants_restants
FROM reservations r
WHERE r.statut = 'validee'
GROUP BY YEAR(r.date_debut), MONTH(r.date_debut)
ORDER BY annee DESC, mois DESC;

-- =====================================================
-- STORED PROCEDURES
-- =====================================================

DELIMITER //

-- Procedure to calculate reservation amount
CREATE PROCEDURE CalculerMontantReservation(
    IN p_emplacement_id INT,
    IN p_date_debut DATETIME,
    IN p_date_fin DATETIME,
    OUT p_montant_total DECIMAL(12,2)
)
BEGIN
    DECLARE v_tarif_journalier DECIMAL(10,2);
    DECLARE v_nombre_jours INT;

    -- Get daily rate for the emplacement
    SELECT tarif_journalier INTO v_tarif_journalier
    FROM emplacements WHERE id = p_emplacement_id;

    -- Calculate number of days
    SET v_nombre_jours = DATEDIFF(p_date_fin, p_date_debut);

    -- Calculate total amount
    SET p_montant_total = v_tarif_journalier * v_nombre_jours;

    -- Add 1 day if less than 24 hours but spans multiple days
    IF v_nombre_jours = 0 AND p_date_debut != p_date_fin THEN
        SET p_montant_total = v_tarif_journalier;
    END IF;
END //

-- Procedure to update emplacement status
CREATE PROCEDURE MettreAJourStatutEmplacement(
    IN p_emplacement_id INT,
    IN p_nouveau_statut ENUM('disponible', 'occupe', 'maintenance', 'reserve')
)
BEGIN
    UPDATE emplacements
    SET etat = p_nouveau_statut, updated_at = CURRENT_TIMESTAMP
    WHERE id = p_emplacement_id;

    -- Log the change
    INSERT INTO audit_log (action, table_name, record_id, new_values)
    VALUES ('UPDATE_STATUS', 'emplacements', p_emplacement_id,
            JSON_OBJECT('nouveau_statut', p_nouveau_statut));
END //

DELIMITER ;

-- =====================================================
-- TRIGGERS
-- =====================================================

DELIMITER //

-- Trigger to update emplacement status when reservation is validated
CREATE TRIGGER tr_reservation_validee
    AFTER UPDATE ON reservations
    FOR EACH ROW
BEGIN
    IF NEW.statut = 'validee' AND OLD.statut != 'validee' THEN
        CALL MettreAJourStatutEmplacement(NEW.emplacement_id, 'occupe');
    END IF;

    IF NEW.statut = 'terminee' AND OLD.statut != 'terminee' THEN
        CALL MettreAJourStatutEmplacement(NEW.emplacement_id, 'disponible');
    END IF;
END //

-- Trigger to log user changes
CREATE TRIGGER tr_audit_users
    AFTER UPDATE ON users
    FOR EACH ROW
BEGIN
    INSERT INTO audit_log (user_id, action, table_name, record_id, old_values, new_values)
    VALUES (NEW.id, 'UPDATE', 'users', NEW.id,
            JSON_OBJECT('username', OLD.username, 'email', OLD.email, 'role', OLD.role),
            JSON_OBJECT('username', NEW.username, 'email', NEW.email, 'role', NEW.role));
END //

DELIMITER ;

-- =====================================================
-- INDEXES FOR PERFORMANCE
-- =====================================================

-- Additional indexes for better performance
CREATE INDEX idx_reservations_dates_composite ON reservations(date_debut, date_fin, statut);
CREATE INDEX idx_emplacements_etat_zone ON emplacements(etat, zone);
CREATE INDEX idx_users_company ON users(company_name);
CREATE INDEX idx_payments_reservation_statut ON payments(reservation_id, statut);

-- =====================================================
-- FINAL COMMENTS
-- =====================================================

/*
This database schema provides:

1. **Complete User Management**: Users with roles (admin, manager, user)
2. **Port Location Management**: Detailed emplacement information with pricing
3. **Reservation System**: Full booking lifecycle with payment tracking
4. **Maintenance Tracking**: Scheduled and emergency maintenance records
5. **Audit Logging**: Complete change history for compliance
6. **Performance Optimization**: Strategic indexes and views
7. **Business Logic**: Stored procedures for common calculations
8. **Data Integrity**: Foreign keys and constraints

Default admin credentials:
- Username: admin
- Password: admin123

To use this schema:
1. Run this script in your MySQL database
2. Update config/database.php with your database credentials
3. The system will be ready to use
*/
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
                                     INDEX idx_status (status));

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

