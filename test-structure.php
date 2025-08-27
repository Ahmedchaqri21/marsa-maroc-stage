<?php
/**
 * Test de Structure - Vérification des Chemins et Liens
 * Marsa Maroc Port Management System
 */

echo "<h1>🧪 Test de la Nouvelle Structure</h1>";

// Test 1: Vérification des chemins de fichiers
echo "<h2>📁 Vérification des Chemins</h2>";

$files_to_check = [
    'index.php' => 'Point d\'entrée principal',
    'pages/admin/dashboard.php' => 'Dashboard administrateur',
    'pages/auth/login.php' => 'Page de connexion',
    'pages/user/dashboard.php' => 'Dashboard utilisateur',
    'config/database.php' => 'Configuration base de données',
    'config/session_check.php' => 'Vérification de session',
    'api/emplacements-fixed.php' => 'API Emplacements',
    'api/users-fixed.php' => 'API Utilisateurs',
    'api/logout.php' => 'API Déconnexion'
];

foreach ($files_to_check as $file => $description) {
    $status = file_exists($file) ? '✅' : '❌';
    echo "<div style='padding: 5px; border-left: 3px solid " . (file_exists($file) ? 'green' : 'red') . "; margin: 5px 0;'>";
    echo "<strong>$status $file</strong> - $description";
    if (!file_exists($file)) {
        echo " <em style='color: red;'>(MANQUANT)</em>";
    }
    echo "</div>";
}

// Test 2: Vérification de la structure des dossiers
echo "<h2>📂 Structure des Dossiers</h2>";

$directories = [
    'pages' => 'Pages d\'interface',
    'pages/admin' => 'Interface administrateur',
    'pages/auth' => 'Pages d\'authentification',
    'pages/user' => 'Interface utilisateur',
    'api' => 'Endpoints API',
    'config' => 'Configuration système',
    'assets' => 'Ressources statiques',
    'database' => 'Scripts de base de données',
    'classes' => 'Classes PHP',
    'includes' => 'Fichiers d\'inclusion',
    'utils' => 'Utilitaires'
];

foreach ($directories as $dir => $description) {
    $status = is_dir($dir) ? '✅' : '❌';
    echo "<div style='padding: 5px; border-left: 3px solid " . (is_dir($dir) ? 'green' : 'red') . "; margin: 5px 0;'>";
    echo "<strong>$status $dir/</strong> - $description";
    if (is_dir($dir)) {
        $count = count(scandir($dir)) - 2; // -2 pour . et ..
        echo " <em style='color: green;'>($count éléments)</em>";
    } else {
        echo " <em style='color: red;'>(MANQUANT)</em>";
    }
    echo "</div>";
}

// Test 3: Test de l'inclusion des fichiers critiques
echo "<h2>🔗 Test d'Inclusion des Fichiers</h2>";

// Test config/database.php
if (file_exists('config/database.php')) {
    try {
        require_once 'config/database.php';
        echo "<div style='padding: 5px; border-left: 3px solid green; margin: 5px 0;'>";
        echo "<strong>✅ config/database.php</strong> - Chargé avec succès";
        echo "</div>";
    } catch (Exception $e) {
        echo "<div style='padding: 5px; border-left: 3px solid red; margin: 5px 0;'>";
        echo "<strong>❌ config/database.php</strong> - Erreur: " . $e->getMessage();
        echo "</div>";
    }
} else {
    echo "<div style='padding: 5px; border-left: 3px solid red; margin: 5px 0;'>";
    echo "<strong>❌ config/database.php</strong> - Fichier manquant";
    echo "</div>";
}

// Test 4: URLs de test
echo "<h2>🌐 URLs de Test</h2>";

$base_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);

$test_urls = [
    '' => 'Page d\'accueil (redirection)',
    'pages/auth/login.php' => 'Page de connexion',
    'pages/admin/dashboard.php' => 'Dashboard admin',
    'pages/user/dashboard.php' => 'Dashboard utilisateur',
    'api/emplacements-fixed.php' => 'API Emplacements'
];

echo "<div style='background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
foreach ($test_urls as $path => $description) {
    $full_url = $base_url . '/' . $path;
    echo "<div style='margin: 5px 0;'>";
    echo "<strong>$description:</strong><br>";
    echo "<a href='$full_url' target='_blank' style='color: #0066cc; text-decoration: none;'>$full_url</a>";
    echo "</div>";
}
echo "</div>";

// Test 5: Résumé
echo "<h2>📊 Résumé</h2>";

$total_files = count($files_to_check);
$existing_files = 0;
foreach ($files_to_check as $file => $desc) {
    if (file_exists($file)) $existing_files++;
}

$total_dirs = count($directories);
$existing_dirs = 0;
foreach ($directories as $dir => $desc) {
    if (is_dir($dir)) $existing_dirs++;
}

echo "<div style='background: #e8f5e8; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
echo "<h3>✅ Fichiers: $existing_files/$total_files</h3>";
echo "<h3>✅ Dossiers: $existing_dirs/$total_dirs</h3>";

if ($existing_files == $total_files && $existing_dirs == $total_dirs) {
    echo "<h2 style='color: green;'>🎉 Structure Complètement Organisée!</h2>";
    echo "<p>La migration est réussie. Tous les fichiers et dossiers sont en place.</p>";
} else {
    echo "<h2 style='color: orange;'>⚠️ Migration Partielle</h2>";
    echo "<p>Certains éléments nécessitent encore une attention.</p>";
}
echo "</div>";

echo "<hr>";
echo "<p><em>Test généré le: " . date('Y-m-d H:i:s') . "</em></p>";
?>
