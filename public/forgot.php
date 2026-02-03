<?php
// public/forgot.php
session_start();
require_once __DIR__ . '/../app/db.php';
require_once __DIR__ . '/../app/helpers.php';

$sent = false;
$error = null;

// Ne jamais révéler si l'email existe ou non.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Protection anti-spam (honeypot)
  if (honeypot_check()) {
    error_log('Bot détecté via honeypot sur forgot.php');
    $sent = true; // Faire croire au bot que ça a marché
  }
  // Protection rate limiting
  elseif (!rate_limit_check('forgot_password', 3, 300)) {
    $error = "Trop de tentatives. Veuillez patienter 5 minutes.";
  }
  else {
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

      // ---- Envoi avec PHPMailer + SendGrid ----
      try {
        require __DIR__.'/../app/email-config.php';
        require __DIR__.'/../vendor/phpmailer/src/PHPMailer.php';
        require __DIR__.'/../vendor/phpmailer/src/SMTP.php';
        require __DIR__.'/../vendor/phpmailer/src/Exception.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($email);
        $mail->addReplyTo(SMTP_FROM_EMAIL, 'Support Le Muscle Sympa');
        
        $mail->Subject = 'Réinitialisation de votre mot de passe - Le Muscle Sympa';
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);
        
        $mail->Body = "
        <html>
        <head>
          <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: #ff8000; color: white; padding: 20px; text-align: center; }
            .content { background: #f9f9f9; padding: 30px; border: 1px solid #ddd; }
            .button { display: inline-block; padding: 12px 30px; background: #ff8000; color: white; text-decoration: none; border-radius: 5px; margin: 20px 0; }
            .footer { text-align: center; padding: 20px; font-size: 12px; color: #666; }
          </style>
        </head>
        <body>
          <div class='container'>
            <div class='header'>
              <h1>Le Muscle Sympa</h1>
            </div>
            <div class='content'>
              <h2>Réinitialisation de votre mot de passe</h2>
              <p>Bonjour,</p>
              <p>Vous avez demandé à réinitialiser votre mot de passe. Cliquez sur le bouton ci-dessous pour créer un nouveau mot de passe :</p>
              <p style='text-align: center;'>
                <a href='$resetLink' class='button'>Réinitialiser mon mot de passe</a>
              </p>
              <p><strong>Ce lien est valide pendant 30 minutes.</strong></p>
              <p>Si le bouton ne fonctionne pas, copiez et collez ce lien dans votre navigateur :</p>
              <p style='word-break: break-all; font-size: 12px; color: #666;'>$resetLink</p>
              <hr style='margin: 30px 0; border: none; border-top: 1px solid #ddd;'>
              <p style='font-size: 14px; color: #666;'>
                <strong>Vous n'avez pas demandé cette réinitialisation ?</strong><br>
                Ignorez simplement cet email. Votre mot de passe actuel reste inchangé.
              </p>
            </div>
            <div class='footer'>
              <p>© 2026 Le Muscle Sympa - Tous droits réservés</p>
              <p>Cet email a été envoyé automatiquement, merci de ne pas y répondre.</p>
            </div>
          </div>
        </body>
        </html>";
        
        // Version texte brut (fallback)
        $mail->AltBody = "Bonjour,\n\nVous avez demandé à réinitialiser votre mot de passe sur Le Muscle Sympa.\n\nCliquez sur ce lien pour créer un nouveau mot de passe (valide 30 minutes) :\n$resetLink\n\nSi vous n'avez pas demandé cette réinitialisation, ignorez simplement cet email.\n\nCordialement,\nL'équipe Le Muscle Sympa";
        
        $mail->send();
      } catch (Exception $e) {
        error_log("Erreur envoi email: " . $mail->ErrorInfo);
      }
    }
    // Toujours “succès” côté UI
    $sent = true;
  } else {
    $sent = true; // même traitement pour ne pas divulguer
  }  }}
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
    <?php if ($error): ?>
      <div class="alert"><?php echo htmlspecialchars($error); ?></div>
      <div class="actions">
        <a class="btn" href="auth.php">Retour à la connexion</a>
      </div>
    <?php elseif ($sent): ?>
      <div class="alert">Si un compte existe pour cette adresse, un email vient d’être envoyé avec les instructions.</div>
      <div class="actions">
        <a class="btn" href="auth.php">Retour à la connexion</a>
      </div>
    <?php else: ?>
      <form method="post" action="forgot.php" novalidate>
        <?php echo honeypot_field(); ?>
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
