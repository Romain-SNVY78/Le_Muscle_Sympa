<?php
session_start();
/* Met en surbrillance l'onglet "Abonnements" dans le header */
$active = 'plans';
?>
<!DOCTYPE html>
<html lang="fr">
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/header.css">

<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8" />
  <title>Abonnements - Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
</head>
<body>

  <?php include __DIR__ . '/../views/partials/header.php'; ?>
  <link rel="stylesheet" href="assets/css/abonnements.css">

  <section class="hero-section">
    <span class="tag">Nos formules d'abonnement</span>
    <h1>Rejoins Le Muscle Sympa</h1>
    <p>Choisis la formule qui te convient — simple, claire et sans surprise.</p>
  </section>

  <h1 class="main-title" style="display:none;">Le Muscle Sympa</h1>
  <p class="main-subtitle" style="display:none;">Choisis la formule qui te convient — simple, claire et sans surprise.</p>

  <div class="image-container">
    <img src="assets/img/abonnement.jpg" alt="Groupe à la salle de sport" />
  </div>

  <section class="offers">

    <!-- SOLO -->
    <article class="offer">
      <h3>SOLO</h3>
      <div class="price">20€<span class="per">/ mois</span></div>
      <ul class="features">
        <li><span class="tick">✅</span> Accès à <strong>tous les équipements</strong></li>
        <li><span class="tick">✅</span> Ambiance motivante & conviviale</li>
        <li><span class="tick">✅</span> Sans frais cachés</li>
      </ul>
      <!-- GET simple -->
      <a class="cta" href="confirm_plan.php?plan=solo">Choisir SOLO</a>
      <div class="hint">Idéal pour démarrer sereinement.</div>
    </article>

    <!-- SOLO+ (populaire) -->
    <article class="offer">
      <span class="badge">Populaire</span>
      <h3>SOLO +</h3>
      <div class="price">50€<span class="per">/ mois</span></div>
      <ul class="features">
        <li><span class="tick">✅</span> Accès à <strong>tous les équipements</strong></li>
        <li><span class="tick">✅</span> <strong>2h de coaching</strong> offertes / mois</li>
        <li><span class="tick">✅</span> Suivi personnalisé</li>
      </ul>
      <!-- IMPORTANT : encodage du + => %2B -->
      <a class="cta" href="confirm_plan.php?plan=solo%2B">Choisir SOLO +</a>
      <div class="hint">Le meilleur rapport progrès/prix.</div>
    </article>

    <!-- DUO -->
    <article class="offer">
      <h3>DUO</h3>
      <div class="price">30€<span class="per">/ mois</span></div>
      <ul class="features">
        <li><span class="tick">✅</span> Pour <strong>2 personnes</strong></li>
        <li><span class="tick">✅</span> Accès à <strong>tous les équipements</strong></li>
        <li><span class="tick">✅</span> Venez ensemble, progressez ensemble</li>
      </ul>
      <a class="cta" href="confirm_plan.php?plan=duo">Choisir DUO</a>
      <div class="hint">Parfait en couple ou entre amis.</div>
    </article>

    <!-- DUO+ -->
    <article class="offer">
      <h3>DUO +</h3>
      <div class="price">80€<span class="per">/ mois</span></div>
      <ul class="features">
        <li><span class="tick">✅</span> Pour <strong>2 personnes</strong></li>
        <li><span class="tick">✅</span> Accès à <strong>tous les équipements</strong></li>
        <li><span class="tick">✅</span> <strong>4h de coaching</strong> offertes / mois</li>
      </ul>
      <!-- IMPORTANT : encodage du + => %2B -->
      <a class="cta" href="confirm_plan.php?plan=duo%2B">Choisir DUO +</a>
      <div class="hint">Coaching inclus pour progresser vite.</div>
    </article>

  </section>

</body>
</html>
