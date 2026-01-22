<?php
session_start();

if (empty($_SESSION['user_id']) || empty($_GET['id'])) {
  http_response_code(403);
  die('Accès refusé');
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
    die('Programme non trouvé');
  }
  
  // Génération du fichier HTML
  $html = '<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>'.htmlspecialchars($program['program_name']).'</title>
  <style>
    body { font-family: Arial, sans-serif; margin: 20px; line-height: 1.6; }
    h1, h2, h3 { color: #ff8000; }
    .info { background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 20px 0; }
  </style>
</head>
<body>
  <h1>'.htmlspecialchars($program['program_name']).'</h1>
  <div class="info">
    <p><strong>Acheté le:</strong> '.date('d/m/Y à H:i', strtotime($program['purchased_at'])).'</p>
  </div>
  ' . $program['program_content'] . '
  <hr>
  <p style="color: #999; font-size: 12px; margin-top: 30px;">
    Document généré le '.date('d/m/Y à H:i').' - Le Muscle Sympa
  </p>
</body>
</html>';
  
  // Envoyer en téléchargement
  header('Content-Type: text/html; charset=UTF-8');
  header('Content-Disposition: attachment; filename="programme_'.date('Ymd_His').'.html"');
  echo $html;
  
} catch (Throwable $e) {
  http_response_code(500);
  die('Erreur serveur');
}
