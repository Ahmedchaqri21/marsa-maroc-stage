<?php
// Script d'initialisation de la base de données
header('Content-Type: application/json; charset=utf-8');

try {
    // Connexion MySQL sans base de données spécifique
    $pdo = new PDO(
        "mysql:host=localhost;charset=utf8mb4",
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
    
    // Créer la base de données si elle n'existe pas
    $pdo->exec("CREATE DATABASE IF NOT EXISTS gestion_operations_portuaires 
                CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    
    // Utiliser la base de données
    $pdo->exec("USE gestion_operations_portuaires");
    
    // Lire et exécuter le schéma SQL
    $schema_file = __DIR__ . '/database/schema.sql';
    
    if (file_exists($schema_file)) {
        $sql = file_get_contents($schema_file);
        
        // Diviser le SQL en requêtes individuelles
        $queries = array_filter(
            array_map('trim', explode(';', $sql)),
            function($query) {
                return !empty($query) && !preg_match('/^--/', $query);
            }
        );
        
        $executed = 0;
        foreach ($queries as $query) {
            if (!empty(trim($query))) {
                try {
                    $pdo->exec($query);
                    $executed++;
                } catch (PDOException $e) {
                    // Ignorer les erreurs de tables/vues déjà existantes
                    if (!strpos($e->getMessage(), 'already exists')) {
                        throw $e;
                    }
                }
            }
        }
        
        $message = "Base de données initialisée avec succès. $executed requêtes exécutées.";
    } else {
        // Créer les tables manuellement si le fichier schema.sql n'existe pas
        $tables_sql = [
            // Table users
            "CREATE TABLE IF NOT EXISTS users (
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
            )",
            
            // Table emplacements
            "CREATE TABLE IF NOT EXISTS emplacements (
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
                INDEX idx_zone (zone)
            )",
            
            // Table reservations
            "CREATE TABLE IF NOT EXISTS reservations (
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
                INDEX idx_statut (statut)
            )"
        ];
        
        foreach ($tables_sql as $sql) {
            $pdo->exec($sql);
        }
        
        $message = "Tables créées manuellement avec succès.";
    }
    
    // Insérer des données d'exemple si les tables sont vides
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    if ($stmt->fetch()['count'] == 0) {
        // Insérer un utilisateur admin par défaut
        $pdo->exec("INSERT INTO users (username, email, password, full_name, role, company_name) VALUES 
                   ('admin', 'admin@marsamaroc.ma', 'admin123', 'Administrateur Principal', 'admin', 'Marsa Maroc')");
        
        // Insérer quelques emplacements d'exemple
        $pdo->exec("INSERT INTO emplacements (code, nom, type, superficie, longueur, largeur, profondeur, tarif_horaire, tarif_journalier, tarif_mensuel, etat, capacite_navire, equipements, zone) VALUES 
                   ('QA1', 'Quai Principal A1', 'quai', 150.50, 120.00, 25.00, 12.50, 25.00, 500.00, 12000.00, 'disponible', 'Navires jusqu\'à 5000 tonnes', 'Grue 50T, Bittes d\'amarrage', 'Zone A'),
                   ('QA2', 'Quai Secondaire A2', 'quai', 200.00, 150.00, 30.00, 10.00, 20.00, 400.00, 10000.00, 'disponible', 'Navires jusqu\'à 3000 tonnes', 'Grue 25T, Bittes d\'amarrage', 'Zone A'),
                   ('QB1', 'Quai B1', 'quai', 120.75, 80.00, 20.00, 8.00, 15.00, 300.00, 7500.00, 'occupe', 'Navires jusqu\'à 1500 tonnes', 'Bittes d\'amarrage', 'Zone B')");
        
        $message .= " Données d'exemple ajoutées.";
    }
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>
