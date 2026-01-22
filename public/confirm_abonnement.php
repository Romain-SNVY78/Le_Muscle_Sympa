<?php
session_start();
$active = 'subscribe';

$plans = [
  'solo' => [
    'name' => 'SOLO',
    'price' => 20,
    'features' => [
      "Accès à tous les équipements",
      "Ambiance motivante & conviviale",
      "Sans frais cachés",
    ]
  ],
  'solo_plus' => [
    'name' => 'SOLO +',
    'price' => 50,
    'features' => [
      "Accès à tous les équipements",
      "2h de coaching offertes / mois",
      "Suivi personnalisé",
    ]
  ],
  'duo' => [
    'name' => 'DUO',
    'price' => 30,
    'features' => [
      "Abonnement pour 2 personnes",
      "Accès à tous les équipements",
      "Venez ensemble, progressez ensemble",
    ]
  ],
  'duo_plus' => [
    'name' => 'DUO +',
    'price' => 80,
    'features' => [
      "Abonnement pour 2 personnes",
      "Accès à tous les équipements",
      "4h de coaching offertes / mois",
    ]
  ],
];

$key = isset($_GET['plan']) ? strtolower(trim($_GET['plan'])) : '';
if (!isset($plans[$key])) {
  header('Location: abonnements.php');
  exit;
}
$plan = $plans[$key];

$confirmed = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Démo : on “confirme” juste côté interface.
  $confirmed = true;
  // Si tu veux mémoriser temporairement :
  $_SESSION['selected_plan_demo'] = $key;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8">
  <title>Confirmer l’abonnement – <?php echo htmlspecialchars($plan['name']) ?> | Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link rel="stylesheet" href="assets/css/confirm_abonnement.css">

</head>
<body>

  <?php include __DIR__ . '/../views/partials/header.php'; ?>

  <main class="wrap">
    <h1>Confirmer l’abonnement</h1>
    <p class="sub">Vérifie les détails ci-dessous avant de valider ton choix.</p>

    <section class="card">
      <h2 style="margin:0 0 4px"><?php echo htmlspecialchars($plan['name']); ?></h2>
      <div class="price"><?php echo (int)$plan['price']; ?>€<span class="per">/ mois</span></div>

      <h3 style="margin:10px 0 8px;font-size:18px">Ce qui est inclus :</h3>
      <ul>
        <?php foreach($plan['features'] as $f): ?>
          <li>✅ <?php echo htmlspecialchars($f); ?></li>
        <?php endforeach; ?>
      </ul>

      <form method="post" class="cta">
        <button class="btn" type="submit">Je confirme cet abonnement</button>
        <a class="btn secondary" href="abonnements.php">Retour aux offres</a>
      </form>

      <?php if ($confirmed): ?>
        <div class="alert">
          <strong>Merci !</strong> Votre choix <em><?php echo htmlspecialchars($plan['name']); ?></em> est bien noté pour cette démonstration.
          <?php if (!isset($_SESSION['user'])): ?>
            Vous pourrez l’activer définitivement après connexion.
          <?php else: ?>
            Vous pourrez finaliser l’activation depuis votre espace compte.
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
