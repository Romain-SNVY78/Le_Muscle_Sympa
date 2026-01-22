<?php
session_start();
$active = 'home';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8">
  <title>Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <!-- CSS de la page d'accueil -->
  <link rel="stylesheet" href="assets/css/index.css">
</head>
<body>
  <?php include __DIR__ . '/../views/partials/header.php'; ?>

  <div class="hero">
    <div class="overlay"></div>
    <div class="hero-content">
      <h1>Des programmes adaptés<br>à vos objectifs</h1>
      <p>Nos coachs vous guident et vous aident à atteindre vos objectifs.</p>
      <div class="buttons">
        <a href="abonnements.php" class="btn">Abonnements</a>
        <?php if(isset($_SESSION['user_id'])): ?>
          <a href="profile.php" class="btn secondary">Mon compte</a>
        <?php else: ?>
          <a href="auth.php" class="btn secondary">Connexion</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</body>
</html>
