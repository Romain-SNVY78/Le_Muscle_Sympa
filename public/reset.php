<?php
// public/reset.php
session_start();
require_once __DIR__ . '/../app/db.php';

$selector = $_GET['s'] ?? '';
$token    = $_GET['t'] ?? '';

$stage = 'check'; // check -> form -> done
$error = null;

// Quand l’utilisateur soumet le nouveau mot de passe
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $selector = $_POST['selector'] ?? '';
  $token    = $_POST['token'] ?? '';
  $pass1    = $_POST['password'] ?? '';
  $pass2    = $_POST['password2'] ?? '';

  if ($pass1 === '' || strlen($pass1) < 6) {
    $error = "Le mot de passe doit contenir au moins 6 caractères.";
  } elseif ($pass1 !== $pass2) {
    $error = "Les mots de passe ne correspondent pas.";
  } else {
    // Chercher le reset
    $st = $pdo->prepare("SELECT * FROM password_resets WHERE selector=? AND used=0 LIMIT 1");
    $st->execute([$selector]);
    if (!($row = $st->fetch())) { $error = "Lien invalide."; }
    else {
      // Vérifier expiration
      if (new DateTime() > new DateTime($row['expires_at'])) {
        $error = "Lien expiré.";
      } else {
        // Vérifier le token
        $valid = hash_equals($row['verifier_hash'], hash('sha256', $token));
        if (!$valid) { $error = "Lien invalide."; }
        else {
          // OK : mettre à jour le mot de passe
          $hash = password_hash($pass1, PASSWORD_DEFAULT);
          $pdo->beginTransaction();
          try {
            $pdo->prepare("UPDATE users SET password_hash=? WHERE id=?")->execute([$hash, (int)$row['user_id']]);
            $pdo->prepare("UPDATE password_resets SET used=1 WHERE id=?")->execute([(int)$row['id']]);
            // optionnel: supprimer autres jetons
            $pdo->prepare("DELETE FROM password_resets WHERE user_id=? AND used=0")->execute([(int)$row['user_id']]);
            $pdo->commit();
            $stage = 'done';
          } catch (Throwable $e) {
            $pdo->rollBack();
            $error = "Une erreur est survenue. Réessayez.";
          }
        }
      }
    }
  }
} else {
  // Arrivée depuis l'email -> afficher le formulaire si le lien est valide (sans divulguer trop)
  if ($selector && $token) {
    $st = $pdo->prepare("SELECT * FROM password_resets WHERE selector=? AND used=0 LIMIT 1");
    $st->execute([$selector]);
    if ($row = $st->fetch()) {
      if (new DateTime() <= new DateTime($row['expires_at'])) {
        $stage = 'form';
      } else {
        $error = "Lien expiré.";
      }
    } else {
      $error = "Lien invalide.";
    }
  } else {
    $error = "Lien invalide.";
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8">
  <title>Réinitialiser le mot de passe</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <link rel="stylesheet" href="assets/css/reset.css">

</head>
<body>
<?php include __DIR__ . '/../views/partials/header.php'; ?>
<div class="wrap">
  <h1>Réinitialiser le mot de passe</h1>
  <div class="card">
    <?php if ($stage === 'done'): ?>
      <div class="ok">Votre mot de passe a été mis à jour avec succès.</div>
      <div class="actions">
        <a class="btn" href="auth.php">Se connecter</a>
      </div>
    <?php elseif ($stage === 'form' && !$error): ?>
      <form method="post" action="reset.php" novalidate>
        <input type="hidden" name="selector" value="<?= htmlspecialchars($selector) ?>">
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <label for="password">Nouveau mot de passe</label>
        <input id="password" name="password" type="password" required minlength="6" placeholder="••••••••">

        <label for="password2">Confirmer le mot de passe</label>
        <input id="password2" name="password2" type="password" required minlength="6" placeholder="••••••••">

        <?php if ($error): ?><div class="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <div class="actions">
          <button class="btn" type="submit">Mettre à jour</button>
          <a class="btn secondary" href="auth.php">Annuler</a>
        </div>
      </form>
    <?php else: ?>
      <div class="alert"><?= htmlspecialchars($error ?: 'Lien invalide.') ?></div>
      <div class="actions">
        <a class="btn" href="forgot.php">Redemander un lien</a>
      </div>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
