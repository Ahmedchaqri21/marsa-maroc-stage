<?php
/**
 * Script de migration de la structure de dossiers
 * Marsa Maroc Port Management System
 * Date: 2025-08-27
 */

class ProjectMigration {
    private $basePath;
    
    private $oldToNewMapping = [
        // Pages principales
        'admin-dashboard.php' => 'pages/admin/dashboard.php',
        'admin-dashboard-fixed.php' => 'pages/admin/dashboard-fixed.php',
        'user-dashboard.php' => 'pages/user/dashboard.php',
        'login.php' => 'pages/auth/login.php',
        'login-new.php' => 'pages/auth/login-new.php',
        'index.php' => 'index.php', // Garder à la racine
        
        // Configuration
        'config/database.php' => 'config/database.php', // Déjà bien placé
        'config/session_check.php' => 'config/session_check.php', // Déjà bien placé
        
        // Assets CSS
        'assets/css/marsa-maroc-style.css' => 'assets/css/main.css',
        
        // Scripts de test et debug (vers tests/)
        'test-connection.php' => 'tests/database/test-connection.php',
        'test-api.php' => 'tests/api/test-api.php',
        'test-users-api.php' => 'tests/api/test-users.php',
        'test-apis-simple.php' => 'tests/api/test-apis-simple.php',
        'test-emplacement-crud.php' => 'tests/api/test-emplacements.php',
        'test-delete-emplacement.php' => 'tests/api/test-delete-emplacement.php',
        'database-diagnostic.php' => 'tests/database/diagnostic.php',
        'database-diagnostics.php' => 'tests/database/diagnostics.php',
        
        // Scripts utilitaires (vers utils/)
        'init-database.php' => 'utils/init-database.php',
        'setup-database.php' => 'utils/setup-database.php',
        'update-passwords.php' => 'utils/update-passwords.php',
        'mysql-mode-fix.php' => 'utils/mysql-mode-fix.php',
        
        // Scripts de debug (vers tests/)
        'debug-api.php' => 'tests/debug/debug-api.php',
        'debug-users-api.php' => 'tests/debug/debug-users-api.php',
        'dashboard-api-debug.php' => 'tests/debug/dashboard-api-debug.php',
        'simple-test.php' => 'tests/debug/simple-test.php',
        
        // Fichiers de backup et fixes
        'dashboard-backup.php' => 'backup/dashboard-backup.php',
        'database-fix.php' => 'backup/database-fix.php',
        'modern-dashboard.php' => 'backup/modern-dashboard.php',
    ];

    private $newFolders = [
        'config',
        'api',
        'database/migrations',
        'database/seeds',
        'database/backup',
        'assets/css',
        'assets/js',
        'assets/images/logos',
        'assets/images/icons',
        'assets/images/backgrounds',
        'assets/documents',
        'pages/auth',
        'pages/admin',
        'pages/user',
        'pages/manager',
        'includes',
        'classes',
        'utils',
        'tests/api',
        'tests/database',
        'tests/debug',
        'tests/integration',
        'logs',
        'uploads/documents',
        'uploads/images',
        'uploads/temp',
        'vendor',
        'backup'
    ];

    public function __construct($basePath = '.') {
        $this->basePath = rtrim($basePath, '/');
    }

    public function migrate() {
        echo "🚀 Début de la migration de structure pour Marsa Maroc...\n";
        echo "📁 Répertoire de base: " . realpath($this->basePath) . "\n\n";
        
        try {
            // Créer les nouveaux dossiers
            $this->createFolders();
            
            // Déplacer les fichiers existants
            $this->moveFiles();
            
            // Créer les fichiers de base
            $this->createBaseFiles();
            
            // Créer les classes de base
            $this->createBaseClasses();
            
            // Créer des fichiers d'inclusion
            $this->createIncludes();
            
            echo "\n✅ Migration terminée avec succès!\n";
            echo "📋 Vérifiez la nouvelle structure et mettez à jour les liens si nécessaire.\n";
            
        } catch (Exception $e) {
            echo "❌ Erreur lors de la migration: " . $e->getMessage() . "\n";
        }
    }

    private function createFolders() {
        echo "📁 Création des dossiers...\n";
        
        foreach ($this->newFolders as $folder) {
            $fullPath = $this->basePath . '/' . $folder;
            if (!is_dir($fullPath)) {
                if (mkdir($fullPath, 0755, true)) {
                    echo "  ✅ Créé: $folder\n";
                } else {
                    echo "  ❌ Échec: $folder\n";
                }
            } else {
                echo "  ℹ️  Existe: $folder\n";
            }
        }
        echo "\n";
    }

    private function moveFiles() {
        echo "📄 Déplacement des fichiers...\n";
        
        foreach ($this->oldToNewMapping as $old => $new) {
            $oldPath = $this->basePath . '/' . $old;
            $newPath = $this->basePath . '/' . $new;
            
            if (file_exists($oldPath) && $old !== $new) {
                // Créer le répertoire de destination si nécessaire
                $dir = dirname($newPath);
                if (!is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }
                
                if (rename($oldPath, $newPath)) {
                    echo "  ✅ Déplacé: $old → $new\n";
                } else {
                    echo "  ❌ Échec: $old → $new\n";
                }
            } elseif (!file_exists($oldPath)) {
                echo "  ⚠️  Non trouvé: $old\n";
            } else {
                echo "  ℹ️  Déjà en place: $new\n";
            }
        }
        echo "\n";
    }

    private function createBaseFiles() {
        echo "📝 Création des fichiers de configuration...\n";
        
        // Créer .htaccess
        $htaccess = "# Marsa Maroc Port Management System - Apache Configuration\n";
        $htaccess .= "RewriteEngine On\n\n";
        $htaccess .= "# Redirection vers index.php pour les routes inexistantes\n";
        $htaccess .= "RewriteCond %{REQUEST_FILENAME} !-f\n";
        $htaccess .= "RewriteCond %{REQUEST_FILENAME} !-d\n";
        $htaccess .= "RewriteRule ^(.*)$ index.php [QSA,L]\n\n";
        $htaccess .= "# Protection des dossiers sensibles\n";
        $htaccess .= "RedirectMatch 403 ^/config/\n";
        $htaccess .= "RedirectMatch 403 ^/classes/\n";
        $htaccess .= "RedirectMatch 403 ^/utils/\n";
        $htaccess .= "RedirectMatch 403 ^/logs/\n";
        file_put_contents($this->basePath . '/.htaccess', $htaccess);
        echo "  ✅ Créé: .htaccess\n";

        // Créer .gitignore
        $gitignore = "# Marsa Maroc - Fichiers à ignorer\n\n";
        $gitignore .= "# Configuration sensible\n";
        $gitignore .= "config/database.php\n";
        $gitignore .= ".env\n\n";
        $gitignore .= "# Logs\n";
        $gitignore .= "logs/*.log\n";
        $gitignore .= "logs/*.txt\n\n";
        $gitignore .= "# Uploads\n";
        $gitignore .= "uploads/*\n";
        $gitignore .= "!uploads/.gitkeep\n\n";
        $gitignore .= "# Vendor\n";
        $gitignore .= "vendor/\n\n";
        $gitignore .= "# Backup\n";
        $gitignore .= "backup/*\n";
        $gitignore .= "!backup/.gitkeep\n\n";
        $gitignore .= "# IDE\n";
        $gitignore .= ".vscode/\n";
        $gitignore .= ".idea/\n";
        file_put_contents($this->basePath . '/.gitignore', $gitignore);
        echo "  ✅ Créé: .gitignore\n";

        // Créer fichiers .gitkeep pour dossiers vides
        $keepDirs = ['logs', 'uploads/documents', 'uploads/images', 'uploads/temp', 'backup'];
        foreach ($keepDirs as $dir) {
            $keepFile = $this->basePath . '/' . $dir . '/.gitkeep';
            if (!file_exists($keepFile)) {
                file_put_contents($keepFile, '');
                echo "  ✅ Créé: $dir/.gitkeep\n";
            }
        }
        
        echo "\n";
    }

    private function createBaseClasses() {
        echo "🏗️ Création des classes de base...\n";
        
        // Classe Database
        $databaseClass = "<?php\n/**\n * Classe de gestion de base de données\n * Marsa Maroc Port Management System\n */\n\nclass Database {\n    private static \$instance = null;\n    private \$pdo;\n    \n    private function __construct() {\n        require_once __DIR__ . '/../config/database.php';\n        \$this->pdo = getDBConnection();\n    }\n    \n    public static function getInstance() {\n        if (self::\$instance === null) {\n            self::\$instance = new self();\n        }\n        return self::\$instance;\n    }\n    \n    public function getConnection() {\n        return \$this->pdo;\n    }\n}\n";
        file_put_contents($this->basePath . '/classes/Database.php', $databaseClass);
        echo "  ✅ Créé: classes/Database.php\n";

        // Classe User
        $userClass = "<?php\n/**\n * Classe de gestion des utilisateurs\n * Marsa Maroc Port Management System\n */\n\nclass User {\n    private \$db;\n    \n    public function __construct() {\n        \$this->db = Database::getInstance()->getConnection();\n    }\n    \n    public function authenticate(\$username, \$password) {\n        // Logique d'authentification\n        \$stmt = \$this->db->prepare(\"SELECT * FROM users WHERE username = :username\");\n        \$stmt->execute([':username' => \$username]);\n        \$user = \$stmt->fetch();\n        \n        if (\$user && password_verify(\$password, \$user['password'])) {\n            return \$user;\n        }\n        return false;\n    }\n    \n    public function getAllUsers() {\n        \$stmt = \$this->db->query(\"SELECT * FROM users ORDER BY id DESC\");\n        return \$stmt->fetchAll();\n    }\n}\n";
        file_put_contents($this->basePath . '/classes/User.php', $userClass);
        echo "  ✅ Créé: classes/User.php\n";

        // Classe Emplacement
        $emplacementClass = "<?php\n/**\n * Classe de gestion des emplacements\n * Marsa Maroc Port Management System\n */\n\nclass Emplacement {\n    private \$db;\n    \n    public function __construct() {\n        \$this->db = Database::getInstance()->getConnection();\n    }\n    \n    public function getAllEmplacements() {\n        \$stmt = \$this->db->query(\"SELECT * FROM emplacements ORDER BY id DESC\");\n        return \$stmt->fetchAll();\n    }\n    \n    public function getEmplacementById(\$id) {\n        \$stmt = \$this->db->prepare(\"SELECT * FROM emplacements WHERE id = :id\");\n        \$stmt->execute([':id' => \$id]);\n        return \$stmt->fetch();\n    }\n    \n    public function createEmplacement(\$data) {\n        \$sql = \"INSERT INTO emplacements (code, nom, type, superficie, longueur, largeur, \n                tarif_journalier, etat, capacite_navire, equipements) \n                VALUES (:code, :nom, :type, :superficie, :longueur, :largeur, \n                :tarif_journalier, :etat, :capacite_navire, :equipements)\";\n        \$stmt = \$this->db->prepare(\$sql);\n        return \$stmt->execute(\$data);\n    }\n}\n";
        file_put_contents($this->basePath . '/classes/Emplacement.php', $emplacementClass);
        echo "  ✅ Créé: classes/Emplacement.php\n";
        
        echo "\n";
    }

    private function createIncludes() {
        echo "📑 Création des fichiers d'inclusion...\n";
        
        // Header commun
        $header = "<?php\n/**\n * En-tête commun - Marsa Maroc\n */\n?>\n<!DOCTYPE html>\n<html lang=\"fr\">\n<head>\n    <meta charset=\"UTF-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\">\n    <title><?php echo \$pageTitle ?? 'Marsa Maroc - Système de Gestion Portuaire'; ?></title>\n    <link rel=\"stylesheet\" href=\"<?php echo \$baseUrl ?? ''; ?>/assets/css/main.css\">\n    <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css\">\n</head>\n<body>\n";
        file_put_contents($this->basePath . '/includes/header.php', $header);
        echo "  ✅ Créé: includes/header.php\n";

        // Footer commun
        $footer = "<?php\n/**\n * Pied de page commun - Marsa Maroc\n */\n?>\n    <footer class=\"main-footer\">\n        <div class=\"footer-content\">\n            <p>&copy; <?php echo date('Y'); ?> Marsa Maroc. Tous droits réservés.</p>\n            <p>Système de Gestion des Emplacements Portuaires</p>\n        </div>\n    </footer>\n    \n    <script src=\"<?php echo \$baseUrl ?? ''; ?>/assets/js/main.js\"></script>\n</body>\n</html>\n";
        file_put_contents($this->basePath . '/includes/footer.php', $footer);
        echo "  ✅ Créé: includes/footer.php\n";
        
        echo "\n";
    }
}

// Vérifier si le script est exécuté directement
if (basename($_SERVER['PHP_SELF']) === 'migrate-structure.php') {
    echo "🏗️ Migration de Structure - Marsa Maroc Port Management System\n";
    echo "================================================================\n\n";
    
    $migration = new ProjectMigration('.');
    $migration->migrate();
    
    echo "\n📋 Prochaines étapes recommandées:\n";
    echo "1. Vérifiez les liens dans les fichiers déplacés\n";
    echo "2. Mettez à jour les chemins d'inclusion\n";
    echo "3. Testez l'application\n";
    echo "4. Commitez les changements dans Git\n\n";
}
?>
