<?php
// public/forgot.php
session_start();
require_once __DIR__ . '/../app/db.php';

$sent = false;

// Ne jamais révéler si l'email existe ou non.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = strtolower(trim($_POST['email'] ?? ''));

  if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // 1) Chercher l'utilisateur (silencieusement)
    $st = $pdo->prepare("SELECT id, first_name FROM users WHERE email = ? LIMIT 1");
    $st->execute([$email]);
    if ($u = $st->fetch()) {
      $userId = (int)$u['id'];

      // 2) Générer un jeton selector + verifier (double-part token)
      $selector = bin2hex(random_bytes(8));           // 16 chars
      $verifier = bin2hex(random_bytes(32));          // 64 chars
      $verifierHash = hash('sha256', $verifier);
      $expires = (new DateTimeImmutable('+30 minutes'))->format('Y-m-d H:i:s');

      // Optionnel : invalider anciens jetons non utilisés
      $pdo->prepare("DELETE FROM password_resets WHERE user_id=? AND used=0")->execute([$userId]);

      // 3) Sauver en DB
      $ins = $pdo->prepare("INSERT INTO password_resets(user_id, selector, verifier_hash, expires_at) VALUES(?,?,?,?)");
      $ins->execute([$userId, $selector, $verifierHash, $expires]);

      // 4) Envoyer l'email (lien de réinit)
      $resetLink = sprintf(
        '%s://%s%s/reset.php?s=%s&t=%s',
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http',
        $_SERVER['HTTP_HOST'],
        rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'),
        $selector,
        $verifier
      );

      // ---- Option A : mail() simple (OK si serveur SMTP local est configuré) ----
      $subject = 'Réinitialisation de votre mot de passe';
      $message = "Bonjour,\n\nVoici le lien pour réinitialiser votre mot de passe (valide 30 minutes) :\n$resetLink\n\nSi vous n'êtes pas à l'origine de cette demande, ignorez cet email.";
      $headers = "From: no-reply@lemusclesympa.local\r\nContent-Type: text/plain; charset=UTF-8\r\n";
      @mail($email, $subject, $message, $headers);

      // ---- Option B : PHPMailer (recommandé) ----
      // (décommente et configure si besoin)
      /*
      require __DIR__.'/../vendor/autoload.php';
      $mail = new PHPMailer\PHPMailer\PHPMailer(true);
      $mail->isSMTP();
      $mail->Host = 'smtp.votreserveur.com';
      $mail->SMTPAuth = true;
      $mail->Username = 'utilisateur';
      $mail->Password = 'motdepasse';
      $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
      $mail->Port = 587;

      $mail->setFrom('no-reply@lemusclesympa.fr', 'Le Muscle Sympa');
      $mail->addAddress($email);
      $mail->Subject = $subject;
      $mail->Body = $message;
      $mail->send();
      */
    }
    // Toujours “succès” côté UI
    $sent = true;
  } else {
    $sent = true; // même traitement pour ne pas divulguer
  }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8">
  <title>Mot de passe oublié</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  
  <link rel="stylesheet" href="assets/css/forgot.css">

</head>
<body>
<?php include __DIR__ . '/../views/partials/header.php'; ?>
<div class="wrap">
  <h1>Mot de passe oublié</h1>
  <div class="card">
    <?php if ($sent): ?>
      <div class="alert">Si un compte existe pour cette adresse, un email vient d’être envoyé avec les instructions.</div>
      <div class="actions">
        <a class="btn" href="auth.php">Retour à la connexion</a>
      </div>
    <?php else: ?>
      <form method="post" action="forgot.php" novalidate>
        <label for="email">Votre adresse e-mail</label>
        <input id="email" name="email" type="email" required placeholder="vous@example.com">
        <div class="actions">
          <button class="btn" type="submit">Envoyer le lien</button>
          <a class="btn secondary" href="auth.php">Annuler</a>
        </div>
      </form>
    <?php endif; ?>
  </div>
</div>
</body>
</html>
