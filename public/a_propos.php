<?php
session_start();
$active = 'about'; // pour surligner "À PROPOS" dans le header
?>
<!DOCTYPE html>
<html lang="fr">
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/header.css">
<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8" />
  <title>À propos – Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/header.css">
<link rel="stylesheet" href="assets/css/a_propos.css">

</head>
<body>

  <?php include __DIR__ . '/../views/partials/header.php'; ?>

  <!-- HERO -->
  <section class="hero">
    <span class="tag">Notre histoire & nos valeurs</span>
    <h1>Le Muscle Sympa</h1>
    <p>Une salle indépendante, une ambiance bienveillante, des résultats concrets. Ici, on s’entraîne sérieusement… toujours avec le sourire.</p>
  </section>

  <main class="wrap">

    <!-- PRÉSENTATION & VALEURS -->
    <section class="panel">
      <h2 class="section-title">Qui sommes-nous ?</h2>
      <p class="lead">
        Situé au <strong>Perray-en-Yvelines</strong>, Le Muscle Sympa est une salle de sport à taille humaine,
        équipée de matériel professionnel et portée par des <strong>coach sportifs diplômés</strong>. Notre mission :
        vous accompagner vers une meilleure forme, à votre rythme, avec un suivi clair et motivant.
      </p>

      <div class="values">
        <div class="value">
          <div class="ico">🤝</div>
          <div>
            <h4>Convivialité</h4>
            <p>Un esprit club où chacun se sent le bienvenu — débutant comme confirmé.</p>
          </div>
        </div>
        <div class="value">
          <div class="ico">🎯</div>
          <div>
            <h4>Résultats</h4>
            <p>Des programmes efficaces, mesurables et adaptés à vos objectifs réels.</p>
          </div>
        </div>
        <div class="value">
          <div class="ico">🛡️</div>
          <div>
            <h4>Sécurité</h4>
            <p>Technique, posture, progressivité : on progresse sans se blesser.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- TIMELINE + ARGUMENTS -->
    <section class="grid-2" style="margin-top:20px">
      <div class="panel">
        <h2 class="section-title">Notre parcours</h2>
        <div class="timeline">
          <div class="step"><span class="dot"></span>
            <h4>2019 — Ouverture</h4>
            <p>Lancement de la salle avec une première communauté de passionnés.</p>
          </div>
          <div class="step"><span class="dot"></span>
            <h4>2021 — Coaching personnalisé</h4>
            <p>Création des packs Solo+ et Duo+ avec heures de coaching offertes.</p>
          </div>
          <div class="step"><span class="dot"></span>
            <h4>2024 — Nouvelle zone musculation</h4>
            <p>Renouvellement du parc et ajout d’un espace mobilité & recovery.</p>
          </div>
        </div>
      </div>

      <div class="panel">
        <h2 class="section-title">Pourquoi nous choisir ?</h2>
        <ul class="lead" style="padding-left:18px; margin:0">
          <li>Coachs à l’écoute, pédagogie claire</li>
          <li>Matériel pro & salle toujours propre</li>
          <li>Ambiance motivante, zéro jugement</li>
          <li>Programmes <em>vraiment</em> personnalisés</li>
        </ul>
        <div class="cta">
          <a class="btn" href="abonnements.php">Voir les abonnements</a>
          <a class="btn secondary" href="questionnaire.php">Je veux un programme</a>
        </div>
      </div>
    </section>

    <!-- ÉQUIPE -->
    <section class="panel" style="margin-top:20px">
      <h2 class="section-title">L’équipe</h2>
      <p class="lead">Une petite équipe, beaucoup d’énergie — et un vrai sens du service.</p>
      <div class="team">
        <div class="member">
          <div class="avatar">AG</div>
          <h5>Agathe</h5>
          <span>Coach — Renforcement & posture</span>
        </div>
        <div class="member">
          <div class="avatar">KA</div>
          <h5>Kylian</h5>
          <span>Coach — Endurance & HIIT</span>
        </div>
        <div class="member">
          <div class="avatar">JL</div>
          <h5>Jade</h5>
          <span>Accueil — Conseils et abonnements</span>
        </div>
      </div>
    </section>

    <!-- FAQ -->
    <section class="panel" style="margin-top:20px">
      <h2 class="section-title">FAQ</h2>
      <details open>
        <summary>Je débute, est-ce fait pour moi ?</summary>
        <p>Oui. On démarre par des bases sûres, avec des mouvements simples et un volume adapté. Vous progressez sans pression.</p>
      </details>
      <details>
        <summary>Faut-il un certificat médical ?</summary>
        <p>Recommandé si vous reprenez le sport après une longue pause, ou en cas d’antécédents. En cas de doute, demandez conseil à votre médecin.</p>
      </details>
      <details>
        <summary>Proposez-vous des séances d’essai ?</summary>
        <p>Oui, passez nous voir à l’accueil pour planifier une séance découverte.</p>
      </details>
    </section>

    <!-- CONTACT -->
    <section class="panel" style="margin-top:20px">
      <h2 class="section-title">Envie d’en savoir plus ?</h2>
      <p class="lead">Écrivez-nous ou passez à la salle — on sera ravi de vous accueillir.</p>
      <div class="cta">
        <a class="btn" href="mailto:contact@lemusclesympa.fr">Écrire un email</a>
        <a class="btn secondary" href="index.php#programmes">Découvrir les programmes</a>
      </div>
    </section>

  </main>

  <footer>
    © <span id="y"></span> Le Muscle Sympa — Perray-en-Yvelines. Tous droits réservés.
  </footer>

  <script src="assets/js/a_propos.js"></script>
</body>
</html>
