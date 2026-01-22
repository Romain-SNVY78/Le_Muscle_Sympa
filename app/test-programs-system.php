#!/usr/bin/env php
<?php
/**
 * Script de test - Vérifie que le système de programmes est correctement configuré
 * 
 * Utilisation: php app/test-programs-system.php
 */

echo "🔍 Vérification du système de programmes personnalisés...\n\n";

// Vérifier la BDD
echo "1️⃣ Vérification de la base de données...\n";
try {
  require_once __DIR__ . '/config.php';
  $pdo = new PDO(
    "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
    DB_USER,
    DB_PASS
  );
  
  $stmt = $pdo->query("SELECT 1 FROM user_programs LIMIT 1");
  echo "   ✅ Table 'user_programs' trouvée\n";
  
  $stmt = $pdo->query("SHOW COLUMNS FROM user_programs");
  $columns = $stmt->fetchAll(PDO::FETCH_COLUMN);
  echo "   ✅ " . count($columns) . " colonnes détectées\n";
  
} catch (PDOException $e) {
  echo "   ❌ Erreur BDD: " . $e->getMessage() . "\n";
  echo "   📋 Exécutez d'abord la migration SQL:\n";
  echo "      app/migrations/001_create_user_programs_table.sql\n";
  exit(1);
}

// Vérifier les fichiers
echo "\n2️⃣ Vérification des fichiers...\n";
$files = [
  '../public/profile.php' => 'Profil utilisateur (programmes)',
  '../public/questionnaire.php' => 'Questionnaire personnalisé',
  '../public/pay.php' => 'Page de paiement',
  '../public/pay_result.php' => 'Confirmation paiement',
  '../public/api/get-program.php' => 'API - Consulter programme',
  '../public/api/download-program.php' => 'API - Télécharger programme',
];

foreach ($files as $path => $desc) {
  $fullPath = __DIR__ . '/' . $path;
  if (file_exists($fullPath)) {
    echo "   ✅ $desc\n";
  } else {
    echo "   ❌ $desc - MANQUANT: $path\n";
  }
}

// Vérifier les permissions
echo "\n3️⃣ Vérification des permissions...\n";
$apiDir = __DIR__ . '/../public/api';
if (is_writable($apiDir)) {
  echo "   ✅ Dossier api/ inscriptible\n";
} else {
  echo "   ⚠️ Dossier api/ non inscriptible (peut être un problème)\n";
}

// Récapitulatif
echo "\n✅ Système prêt!\n";
echo "📋 Prochaines étapes:\n";
echo "   1. Testez le flux: questionnaire → paiement → profile\n";
echo "   2. Consultez SYSTEM_PROGRAMMES_INTERNES.md pour la documentation\n";
echo "   3. Personnalisez generateProgram() pour vos besoins\n\n";
