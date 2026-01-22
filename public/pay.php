<?php
session_start();

/* Le traitement unifié (plan ou programme) commence plus bas. */

/* ------- 1) Récupération du contexte (plan ou programme) ------- */
$LABELS_PLAN = ['solo'=>'SOLO','solo+'=>'SOLO +','duo'=>'DUO','duo+'=>'DUO +'];
$PRICES_PLAN = ['solo'=>20,'solo+'=>50,'duo'=>30,'duo+'=>80];

$LABELS_PROG = [
  'renforcement' => 'Renforcement musculaire',
  'endurance'    => 'Endurance',
  'esthetique'   => 'Esthétique',
  'entretien'    => 'Entretien'
];
$PRICE_PROG = 10;

/* A) Données postées */
$kind    = $_POST['kind']    ?? null;
$plan    = $_POST['plan']    ?? null;
$program = $_POST['program'] ?? null;
$programData = $_POST['program_data'] ?? null;

// Stocker les données du questionnaire en session si fourni
if ($programData && $kind === 'program') {
  try {
    $_SESSION['program_data'] = json_decode($programData, true) ?: [];
  } catch (Throwable $e) {
    $_SESSION['program_data'] = [];
  }
}

/* B) Si on revient de auth.php, on peut récupérer la sélection mise en session */
if (!$kind && !empty($_SESSION['pending_checkout'])) {
  $kind    = $_SESSION['pending_checkout']['kind']    ?? null;
  $plan    = $_SESSION['pending_checkout']['plan']    ?? null;
  $program = $_SESSION['pending_checkout']['program'] ?? null;
  unset($_SESSION['pending_checkout']);
}

/* C) Normalisation depuis confirm_plan.php (qui envoie juste 'plan') */
if (!$kind && isset($_POST['plan'])) {
  $kind = 'plan';
}

/* via GET (ex: pay.php?plan=solo) */
if (!$kind && isset($_GET['plan'])) { $kind = 'plan'; $plan = $_GET['plan']; }
if (!$kind && isset($_GET['program'])) { $kind = 'program'; $program = $_GET['program']; }

/* ------- 2) Validation / mapping label + prix ------- */
if ($kind === 'plan') {
  if (!$plan || !isset($LABELS_PLAN[$plan])) { header('Location: abonnements.php'); exit; }
  $label = $LABELS_PLAN[$plan];
  $price = $PRICES_PLAN[$plan];
  $recapLabel = 'Formule';
} elseif ($kind === 'program') {
  if (!$program || !isset($LABELS_PROG[$program])) { header('Location: programmes.php'); exit; }
  $label = $LABELS_PROG[$program];
  $price = $PRICE_PROG;
  $recapLabel = 'Programme';
} else {
  header('Location: index.php'); exit;
}

/* ------- 3) Connexion obligatoire ------- */
if (empty($_SESSION['user_id'])) {
  $_SESSION['pending_checkout'] = ['kind'=>$kind, 'plan'=>$plan, 'program'=>$program];
  $_SESSION['flash_error'] = "Veuillez vous connecter pour continuer le paiement.";
  if ($kind === 'plan' && $plan) {
    $next = 'pay.php?plan=' . rawurlencode($plan);
  } elseif ($kind === 'program' && $program) {
    $next = 'pay.php?program=' . rawurlencode($program);
  } else {
    $next = 'pay.php';
  }
  $_SESSION['auth_next'] = $next;
  header('Location: auth.php?need_login=1&next=' . rawurlencode($next));
  exit;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Paiement sécurisé (démo)</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <style>
    :root{ --orange:#ff8000; --orange-dark:#cc6600; --bg:#222; --panel:#1f1f1f; --text:#fff; --muted:#cfcfcf; --bd:rgba(255,255,255,.12); }
    html,body{margin:0;padding:0;background:var(--bg);color:var(--text);font-family:Arial,Helvetica,sans-serif}
    .wrap{max-width:1100px;margin:26px auto;padding:0 18px}
    h1{margin:0 0 18px;font-size:40px}
    .grid{display:grid;grid-template-columns:2fr 1.3fr;gap:18px}
    @media (max-width:980px){ .grid{grid-template-columns:1fr} }
    .card{background:var(--panel);border:1px solid var(--bd);border-radius:16px;padding:18px}
    h2{margin:0 0 12px}
    label{display:block;font-weight:700;margin:10px 0 6px}
    input,select{
      width:100%;padding:12px;border-radius:10px;border:1px solid var(--bd);
      background:#2b2b2b;color:#fff
    }
    .row{display:grid;grid-template-columns:1fr 1fr;gap:12px}
    .btn{border:none;border-radius:10px;padding:12px 18px;font-weight:800;cursor:pointer;background:var(--orange);color:#111}
    .btn:hover{background:var(--orange-dark)}
    .btn.secondary{background:transparent;color:#fff;border:1px solid var(--bd)}
    .actions{display:flex;gap:12px;margin-top:14px;flex-wrap:wrap}
    .recap p{margin:6px 0;color:#ddd}
  </style>
</head>
<body>

<?php include __DIR__ . '/../views/partials/header.php'; ?>

<div class="wrap">
  <h1>Paiement sécurisé (démo)</h1>

  <div class="grid">
    <!-- COL 1 : FORMULAIRE -->
    <div class="card">
      <h2>Vos informations de paiement</h2>

      <!-- Retrait de "novalidate" pour activer les validations HTML -->
      <form method="post" action="pay_result.php" id="payform">
        <!-- Contexte -->
        <input type="hidden" name="kind" value="<?= htmlspecialchars($kind) ?>">
        <?php if ($kind==='plan'): ?>
          <input type="hidden" name="plan" value="<?= htmlspecialchars($plan) ?>">
        <?php else: ?>
          <input type="hidden" name="program" value="<?= htmlspecialchars($program) ?>">
        <?php endif; ?>

        <!-- Carte -->
        <label for="holder">Nom sur la carte</label>
        <input id="holder" name="holder" placeholder="Ex.: Jean Dupont" required>

        <label for="number">Numéro de carte</label>
        <input
          id="number"
          name="number"
          inputmode="numeric"
          autocomplete="cc-number"
          placeholder="4242-4242-4242-4242"
          maxlength="19"
          pattern="^\d{4}-\d{4}-\d{4}-\d{4}$"
          title="16 chiffres, format xxxx-xxxx-xxxx-xxxx"
          required
        >

        <div class="row">
          <div>
            <label for="exp">Expiration (MM/AA)</label>
            <input
              id="exp"
              name="exp"
              placeholder="12/28"
              maxlength="5"
              pattern="^(0[1-9]|1[0-2])\/\d{2}$"
              title="Format MM/AA (ex: 12/28)"
              required
            >
          </div>
          <div>
            <label for="cvc">CVC</label>
            <input
              id="cvc"
              name="cvc"
              placeholder="123"
              inputmode="numeric"
              maxlength="3"
              pattern="^\d{3}$"
              title="3 chiffres"
              required
            >
          </div>
        </div>

        <!-- Adresse -->
        <h2 style="margin-top:18px">Adresse de facturation</h2>

        <label for="addr1">Adresse (ligne 1)</label>
        <input id="addr1" name="addr1" placeholder="N° et voie" required>

        <label for="addr2">Complément d’adresse (optionnel)</label>
        <input id="addr2" name="addr2" placeholder="Bâtiment, étage, etc.">

        <div class="row">
          <div>
            <label for="zip">Code postal</label>
            <input
              id="zip"
              name="zip"
              placeholder="75001"
              inputmode="numeric"
              maxlength="5"
              pattern="^\d{5}$"
              title="5 chiffres"
              required
            >
          </div>
          <div>
            <label for="city">Ville</label>
            <input
              id="city"
              name="city"
              placeholder="Paris"
              pattern="^[A-Za-zÀ-ÖØ-öø-ÿ \-']{2,}$"
              title="Lettres uniquement (accents, espaces, tiret, apostrophe autorisés)"
              required
            >
          </div>
        </div>

        <label for="country">Pays</label>
        <select id="country" name="country" required>
          <option value="FR" selected>France</option>
          <option value="BE">Belgique</option>
          <option value="CH">Suisse</option>
          <option value="LU">Luxembourg</option>
          <option value="CA">Canada</option>
        </select>

        <div class="actions">
          <button class="btn" type="submit">Payer <?= (int)$price; ?>€</button>
          <a class="btn secondary" href="<?= $kind==='plan'?'abonnements.php':'programmes.php' ?>">Retour</a>
        </div>
      </form>
    </div>

    <!-- COL 2 : RÉCAP -->
    <aside class="card recap">
      <h2>Récapitulatif</h2>
      <p><strong><?= htmlspecialchars($recapLabel) ?> :</strong> <?= htmlspecialchars($label); ?></p>
      <p><strong>Prix :</strong> <?= (int)$price; ?>€</p>
      <p style="margin-top:14px">Ce paiement est une simulation (projet BTS SIO).</p>
    </aside>
  </div>
</div>

<script>
// Utilitaires
const onlyDigits = s => s.replace(/\D+/g, '');

// Numéro de carte: auto format xxxx-xxxx-xxxx-xxxx (16 chiffres)
const number = document.getElementById('number');
number.addEventListener('input', () => {
  let v = onlyDigits(number.value).slice(0, 16);           // max 16 chiffres
  // groupe en 4 avec tirets
  let parts = [];
  for (let i = 0; i < v.length; i += 4) parts.push(v.slice(i, i + 4));
  number.value = parts.join('-');
});

// Expiration: auto / après 2 chiffres, format MM/AA
const exp = document.getElementById('exp');
exp.addEventListener('input', () => {
  let v = onlyDigits(exp.value).slice(0, 4);               // MM + AA
  if (v.length >= 3) v = v.slice(0, 2) + '/' + v.slice(2); // insère '/'
  exp.value = v;
});

// CVC: 3 chiffres
const cvc = document.getElementById('cvc');
cvc.addEventListener('input', () => {
  cvc.value = onlyDigits(cvc.value).slice(0, 3);
});

// ZIP: 5 chiffres
const zip = document.getElementById('zip');
zip.addEventListener('input', () => {
  zip.value = onlyDigits(zip.value).slice(0, 5);
});

// Ville: lettres seulement (accents, espace, tiret, apostrophe)
const city = document.getElementById('city');
city.addEventListener('input', () => {
  city.value = city.value.replace(/[^A-Za-zÀ-ÖØ-öø-ÿ \-']/g, '');
});

// Validation finale (bloque l’envoi si pattern KO)
document.getElementById('payform').addEventListener('submit', (e) => {
  // on laisse le navigateur gérer via pattern/required ; on peut renforcer si besoin
  if (!e.target.checkValidity()) {
    e.preventDefault();
    e.target.reportValidity();
  }
});
</script>

</body>
</html>
