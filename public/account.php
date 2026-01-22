<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: auth.php');
  exit;
}
$first = htmlspecialchars($_SESSION['first_name'] ?? 'Utilisateur');
?>
<!DOCTYPE html>
<html lang="fr">
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/header.css">

<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8">
  <title>Espace membre - Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <link rel="stylesheet" href="assets/css/account.css">


</head>
<body>
  <div class="wrap">
    <h1>Bonjour, <?php echo $first; ?> 👋</h1>
    <div class="card">
      <p>Bienvenue dans votre espace. (Démo BTS SIO)</p>
      <p><a class="btn" href="index.php">Retour à l'accueil</a>
         <a class="btn" href="logout.php">Se déconnecter</a></p>
    </div>
  </div>
</body>
</html>
