<?php
// confirm_plan.php
session_start();
$active = 'plans';

/* Plans disponibles */
$PLANS = [
  'solo'  => [
    'label'  => 'SOLO',
    'price'  => 20,
    'features' => [
      "Accès à tous les équipements",
      "Ambiance motivante & conviviale",
      "Sans frais cachés",
    ],
  ],
  'solo+' => [
    'label'  => 'SOLO +',
    'price'  => 50,
    'features' => [
      "Accès à tous les équipements",
      "2h de coaching offertes / mois",
      "Suivi personnalisé",
    ],
  ],
  'duo'   => [
    'label'  => 'DUO',
    'price'  => 30,
    'features' => [
      "Abonnement pour 2 personnes",
      "Accès à tous les équipements",
      "Venez ensemble, progressez ensemble",
    ],
  ],
  'duo+'  => [
    'label'  => 'DUO +',
    'price'  => 80,
    'features' => [
      "Abonnement pour 2 personnes",
      "Accès à tous les équipements",
      "4h de coaching offertes / mois",
    ],
  ],
];

/* Récupération du plan depuis POST (depuis Abonnements.php) ou GET */
$plan = $_POST['plan'] ?? $_GET['plan'] ?? null;

if (!$plan || !isset($PLANS[$plan])) {
  // plan invalide -> retour à la liste des abonnements
  header('Location: abonnements.php');
  exit;
}

$cur = $PLANS[$plan];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8">
  <title>Confirmation du plan – Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <link rel="stylesheet" href="assets/css/confirm_plan.css">

</head>
<body>

<?php include __DIR__ . '/../views/partials/header.php'; ?>

<div class="wrap">
  <h1>Confirmer votre choix</h1>
  <div class="card">
    <div class="grid">
      <div class="offer">
        <h2 style="margin:0 0 6px;color:var(--orange)"><?= htmlspecialchars($cur['label']) ?></h2>
        <div class="price"><?= (int)$cur['price'] ?>€ <span class="muted" style="font-weight:400;font-size:16px">/ mois</span></div>
        <ul class="features">
          <?php foreach($cur['features'] as $f): ?>
            <li><?= htmlspecialchars($f) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="offer">
        <h3 style="margin:0 0 10px;">Récapitulatif</h3>
        <p><strong>Formule :</strong> <?= htmlspecialchars($cur['label']) ?><br>
           <strong>Prix :</strong> <?= (int)$cur['price'] ?>€ / mois</p>
        <p class="muted">En cliquant sur « Continuer vers le paiement », vous serez redirigé vers la
        page de paiement (démo) pour saisir vos informations (adresse + carte). Rien n’est réellement débité.</p>

        <!-- 👉 IMPORTANT : on va vers pay.php (et pas vers pay_result/profile) -->
        <div class="actions">
          <a class="btn" href="pay.php?plan=<?= urlencode($plan) ?>">Continuer vers le paiement</a>
          <a class="btn secondary" href="abonnements.php">Retour</a>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
