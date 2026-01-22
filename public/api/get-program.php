<?php
session_start();

if (empty($_SESSION['user_id']) || empty($_GET['id'])) {
  http_response_code(403);
  echo 'Accès refusé';
  exit;
}

try {
  require_once __DIR__ . '/../../app/db.php';
  
  $stmt = $pdo->prepare("
    SELECT id, program_name, program_type, program_content, purchased_at
    FROM user_programs
    WHERE id = ? AND user_id = ?
  ");
  
  $stmt->execute([$_GET['id'], $_SESSION['user_id']]);
  $program = $stmt->fetch(PDO::FETCH_ASSOC);
  
  if (!$program) {
    http_response_code(404);
    echo 'Programme non trouvé';
    exit;
  }
  
  echo $program['program_content'];
  
} catch (Throwable $e) {
  http_response_code(500);
  echo 'Erreur serveur';
}
