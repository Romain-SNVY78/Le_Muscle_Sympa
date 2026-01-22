<?php
session_start();
require_once __DIR__ . '/../app/db.php';

/* --- Flash message poussé par une redirection (ex : vouloir payer sans être connecté) --- */
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_error']); // on consomme le flash

/* --- Si un 'next' est fourni en GET, on le mémorise pour la reprise après login --- */
if (!empty($_GET['next'])) {
  $_SESSION['auth_next'] = $_GET['next'];
}

/* Map libellé d'abonnement */
function plan_label($code) {
  $m = ['solo'=>'SOLO','solo+'=>'SOLO +','duo'=>'DUO','duo+'=>'DUO +'];
  return isset($m[$code]) ? $m[$code] : '—';
}

$errors  = [];
$success = null;
$tab     = 'login';

/* Petit confort : si on arrive avec ?need_login=1, on passe direct sur l’onglet login */
if (!empty($_GET['need_login'])) {
  $tab = 'login';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  /* ---------------------- INSCRIPTION ---------------------- */
  if ($action === 'register') {
    $tab = 'register';

    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name']  ?? '');
    $email      = strtolower(trim($_POST['email'] ?? ''));
    $phone      = trim($_POST['phone'] ?? '');
    $birthdate  = $_POST['birthdate'] ?? null;
    $password   = $_POST['password'] ?? '';
    $password2  = $_POST['password2'] ?? '';

    if ($first_name === '' || $last_name === '')            $errors[] = "Nom et prénom requis.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))         $errors[] = "Email invalide.";
    if (strlen($password) < 6)                              $errors[] = "Mot de passe : 6 caractères minimum.";
    if ($password !== $password2)                           $errors[] = "Les mots de passe ne correspondent pas.";

    if (!$errors) {
      // email déjà utilisé ?
      $st = $pdo->prepare("SELECT id FROM users WHERE email=?");
      $st->execute([$email]);
      if ($st->fetch()) {
        $errors[] = "Cet email est déjà utilisé.";
      } else {
        // INSERT uniquement dans les colonnes réellement présentes
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $st = $pdo->prepare("
          INSERT INTO users (first_name, last_name, email, phone, birthdate, password_hash, created_at)
          VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $st->execute([$first_name, $last_name, $email, $phone, $birthdate, $hash]);

        $success = "Compte créé ! Vous pouvez maintenant vous connecter.";
        $tab = 'login';
      }
    }
  }

  /* ------------------------ CONNEXION ------------------------ */
  if ($action === 'login') {
    $tab = 'login';

    $email    = strtolower(trim($_POST['email'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Email invalide.";
    if ($password === '')                           $errors[] = "Mot de passe requis.";

    if (!$errors) {
      $st = $pdo->prepare("
        SELECT id, first_name, last_name, email, plan, password_hash
        FROM users
        WHERE email = ?
      ");
      $st->execute([$email]);
      $u = $st->fetch(PDO::FETCH_ASSOC);

      if (!$u) {
        $errors[] = "Email introuvable.";
      } elseif (!password_verify($password, $u['password_hash'])) {
        $errors[] = "Mot de passe incorrect.";
      } else {
        $_SESSION['user_id']    = (int)$u['id'];
        $_SESSION['first_name'] = $u['first_name'] ?? '';
        $_SESSION['last_name']  = $u['last_name']  ?? '';
        $_SESSION['email']      = $u['email']      ?? '';

        $planCode = $u['plan'] ?? null;
        $_SESSION['active_plan_code']  = $planCode;
        $_SESSION['active_plan_label'] = ($planCode && in_array($planCode, ['solo','solo+','duo','duo+']))
                                       ? (['solo'=>'SOLO','solo+'=>'SOLO +','duo'=>'DUO','duo+'=>'DUO +'][$planCode])
                                       : '—';

        /* Si un plan avait été choisi hors connexion (pending_plan), on l'écrit en BDD ici */
        if (!empty($_SESSION['pending_plan'])) {
          $pending = $_SESSION['pending_plan'];
          if (in_array($pending, ['solo','solo+','duo','duo+'])) {
            $st2 = $pdo->prepare("UPDATE users SET plan = ? WHERE id = ?");
            $st2->execute([$pending, $_SESSION['user_id']]);
            // on met à jour la session pour cohérence
            $_SESSION['active_plan_code']  = $pending;
            $_SESSION['active_plan_label'] = ['solo'=>'SOLO','solo+'=>'SOLO +','duo'=>'DUO','duo+'=>'DUO +'][$pending];
          }
          unset($_SESSION['pending_plan']);
        }

        /* Redirection prioritaire : reprendre là où l'utilisateur voulait aller */
        $next = $_POST['next'] ?? $_SESSION['auth_next'] ?? $_SESSION['intended_url'] ?? null;
        unset($_SESSION['auth_next'], $_SESSION['intended_url']);

        if ($next && preg_match('#^[\w\-\./\?\=&]+$#', $next)) { // petite sécurité sur le format
          header('Location: ' . $next);
          exit;
        }

        // Sinon, accueil
        header('Location: index.php');
        exit;
      }
    }
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8">
  <title>Connexion / Inscription - Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <link rel="stylesheet" href="assets/css/auth.css">

  <script src="assets/js/auth.js"></script>
  <script>window.addEventListener('DOMContentLoaded', ()=>{ switchTab('<?php echo $tab ?>'); });</script>
</head>
<body>
  <?php include __DIR__ . '/../views/partials/header.php'; ?>

  <div class="wrap">
    <h1>Mon compte</h1>

    <?php if (!empty($flash_error)): ?>
      <div class="alert error"><?php echo htmlspecialchars($flash_error); ?></div>
    <?php endif; ?>

    <?php if ($errors): ?>
      <div class="alert error">
        <?php foreach($errors as $e) echo "• ".htmlspecialchars($e)."<br>"; ?>
      </div>
    <?php endif; ?>

    <?php if ($success): ?>
      <div class="alert success"><?php echo htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Connexion -->
    <div class="card" id="pane-login">
      <form method="post" action="auth.php">
        <input type="hidden" name="action" value="login">
        <!-- véhicule la prochaine destination si on en a une -->
        <input type="hidden" name="next" value="<?php echo htmlspecialchars($_SESSION['auth_next'] ?? ($_GET['next'] ?? '')); ?>">
        <div class="row">
          <div>
            <label for="login-email">Email</label>
            <input id="login-email" name="email" type="email" required placeholder="vous@example.com">
          </div>
          <div>
            <label for="login-pass">Mot de passe</label>
            <input id="login-pass" name="password" type="password" required placeholder="••••••••">
          </div>
        </div>
        <div class="actions">
          <button class="btn" type="submit">Se connecter</button>
          <a class="btn secondary" href="index.php">Retour</a>
        </div>
        <div>
          <small><a href="forgot.php" class="small-action">Mot de passe oublié ?</a></small>
        </div>
        <small>Pas encore de compte ? <a href="#" onclick="switchTab('register');return false;" class="small-action">Créer un compte</a></small>
      </form>
    </div>

    <!-- Inscription -->
    <div class="card" id="pane-register" style="display:none; margin-top:16px;">
      <form method="post" action="auth.php">
        <input type="hidden" name="action" value="register">
        <div class="row">
          <div><label for="first_name">Prénom</label><input id="first_name" name="first_name" required></div>
          <div><label for="last_name">Nom</label><input id="last_name" name="last_name" required></div>
          <div><label for="email">Email</label><input id="email" name="email" type="email" required></div>
          <div><label for="phone">Téléphone</label><input id="phone" name="phone" type="text" placeholder="06..."></div>
          <div><label for="birthdate">Date de naissance</label><input id="birthdate" name="birthdate" type="date"></div>
          <div><label for="password">Mot de passe</label><input id="password" name="password" type="password" required minlength="6"></div>
          <div><label for="password2">Confirmer</label><input id="password2" name="password2" type="password" required minlength="6"></div>
        </div>
        <div class="actions">
          <button class="btn" type="submit">Créer mon compte</button>
          <button class="btn secondary" type="button" onclick="switchTab('login')">J’ai déjà un compte</button>
        </div>
        <small>Données utilisées uniquement dans le cadre du projet BTS SIO.</small>
      </form>
    </div>
  </div>
</body>
</html>
