<?php
// Header commun — collé dans includes/header.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$active = $active ?? '';                 // 'home' | 'plans' | 'about' | 'account'
$logged = !empty($_SESSION['user_id']);
$first  = $logged ? ($_SESSION['first_name'] ?? 'Moi') : null;
?>


<header class="site-header">
  <a class="site-brand" href="index.php" title="Accueil">
    <img src="assets/img/logo.png" alt="Le Muscle Sympa">
    <h2>Le Muscle Sympa</h2>
  </a>
  
<link rel="stylesheet" href="assets/css/base.css">
<link rel="stylesheet" href="assets/css/header.css">
<link rel="stylesheet" href="assets/css/header.css">

  <nav class="site-nav">
    <a class="site-link <?= $active==='home' ? 'active' : '' ?>" href="index.php">ACCUEIL</a>
    <a class="site-link <?= $active==='plans' ? 'active' : '' ?>" href="abonnements.php">ABONNEMENTS</a>
    <a class="site-link <?php echo $active==='programs'?'active':'' ?>" href="programmes.php">PROGRAMMES</a>
    <a class="site-link <?= $active==='about' ? 'active' : '' ?>" href="a_propos.php">À PROPOS</a>

    <?php if ($logged): ?>
      <a class="userpill <?= $active==='account' ? 'active' : '' ?>" href="profile.php" title="Mon compte">
        <span class="avatar"><?= strtoupper(substr($first,0,1)) ?></span>
        <span class="name"><?= htmlspecialchars($first) ?></span>
      </a>
    <?php else: ?>
      <a class="userpill" href="auth.php" title="Connexion">
        <span class="avatar">↪</span>
        <span class="name">Connexion</span>
      </a>
    <?php endif; ?>
  </nav>
</header>

<!-- Bouton remonter en haut -->
<button id="toTop" class="to-top" aria-label="Remonter en haut" title="Haut">
  <!-- petite flèche -->
  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" aria-hidden="true">
    <path d="M6 14l6-6 6 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
  </svg>
</button>


<script src="assets/js/header.js"></script>
