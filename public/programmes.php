<?php
session_start();
// Mettre en surbrillance l'onglet "Programmes" dans le header
$active = 'programs';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8" />
  <title>Programmes – Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
</head>
<body>

<link rel="stylesheet" href="assets/css/programmes.css">
<?php include __DIR__ . '/../views/partials/header.php'; ?>

<div class="wrap">
  <div class="intro">
    <small>Du renforcement musculaire à l’endurance</small>
    <h1>Choisissez votre objectif et obtenez des résultats rapides.</h1>
  </div>

  <div class="grid">

    <!-- Renforcement -->
    <article class="card">
      <h3>Renforcement musculaire</h3>
      <span class="badge">Programme personnalisé • 10€</span>
      <ul class="list">
        <li><span class="tick">✅</span> Cycles progressifs force/hypertrophie</li>
        <li><span class="tick">✅</span> Plans 3 à 5 jours/sem. adaptés</li>
        <li><span class="tick">✅</span> Conseils nutrition de base</li>
      </ul>
      <a class="cta" href="questionnaire.php?type=renforcement">Commandez votre programme !</a>
    </article>

    <!-- Endurance -->
    <article class="card">
      <h3>Endurance</h3>
      <span class="badge">Programme personnalisé • 10€</span>
      <ul class="list">
        <li><span class="tick">✅</span> Intervalles, seuil & fondamental</li>
        <li><span class="tick">✅</span> Suivi RPE et zones d’effort</li>
        <li><span class="tick">✅</span> Protocoles HIIT sécurisés</li>
      </ul>
      <a class="cta" href="questionnaire.php?type=endurance">Commandez votre programme !</a>
    </article>

    <!-- Esthétique -->
    <article class="card">
      <h3>Esthétique</h3>
      <span class="badge">Programme personnalisé • 10€</span>
      <ul class="list">
        <li><span class="tick">✅</span> Focus zones ciblées & posture</li>
        <li><span class="tick">✅</span> Volume maîtrisé, haute qualité</li>
        <li><span class="tick">✅</span> Routine mobilité & gainage</li>
      </ul>
      <a class="cta" href="questionnaire.php?type=esthetique">Commandez votre programme !</a>
    </article>

    <!-- Entretien -->
    <article class="card">
      <h3>Entretien</h3>
      <span class="badge">Programme personnalisé • 10€</span>
      <ul class="list">
        <li><span class="tick">✅</span> 2–3 séances rapides / semaine</li>
        <li><span class="tick">✅</span> Exercices fondamentaux sûrs</li>
        <li><span class="tick">✅</span> Étirements guidés en fin de séance</li>
      </ul>
      <a class="cta" href="questionnaire.php?type=entretien">Commandez votre programme !</a>
    </article>

  </div>
</div>

</body>
</html>
