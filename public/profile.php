<?php
session_start();
$active = 'account';

if (empty($_SESSION['user_id'])) {
  header('Location: auth.php');
  exit;
}

/* Libellés */
$PLAN_LABELS = [
  'solo'  => 'SOLO',
  'solo+' => 'SOLO +',
  'duo'   => 'DUO',
  'duo+'  => 'DUO +',
];

$PROGRAM_LABELS = [
  'renforcement' => 'Renforcement musculaire',
  'endurance'    => 'Endurance',
  'esthetique'   => 'Esthétique',
  'entretien'    => 'Entretien'
];

$firstName  = $_SESSION['first_name'] ?? '';
$lastName   = $_SESSION['last_name']  ?? '';
$email      = $_SESSION['email']      ?? '';
$planCode   = $_SESSION['active_plan_code']  ?? null;
$planLabel  = $_SESSION['active_plan_label'] ?? ($planCode && isset($PLAN_LABELS[$planCode]) ? $PLAN_LABELS[$planCode] : '—');

$tab = $_GET['tab'] ?? 'overview';
$userPrograms = [];

try {
  require_once __DIR__ . '/../app/db.php';
  if (isset($pdo) && $pdo instanceof PDO) {
    $stmt = $pdo->prepare("SELECT first_name, last_name, email, plan FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
      $firstName = $row['first_name'] ?? $firstName;
      $lastName  = $row['last_name']  ?? $lastName;
      $email     = $row['email']      ?? $email;
      $planCode  = $row['plan']       ?? $planCode;
      $planLabel = isset($PLAN_LABELS[$planCode]) ? $PLAN_LABELS[$planCode] : ($planLabel ?: '—');

      $_SESSION['first_name'] = $firstName;
      $_SESSION['last_name']  = $lastName;
      $_SESSION['email']      = $email;
      $_SESSION['active_plan_code']  = $planCode;
      $_SESSION['active_plan_label'] = $planLabel;
    }

    $stmt = $pdo->prepare("
      SELECT id, program_type, program_name, purchased_at, price, program_content
      FROM user_programs
      WHERE user_id = ?
      ORDER BY purchased_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $userPrograms = $stmt->fetchAll(PDO::FETCH_ASSOC);
  }
} catch (Throwable $e) {
  error_log('Erreur: ' . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <title>Mon compte – Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <link rel="stylesheet" href="assets/css/profile.css">

  <style>
    .tabs {
      display: flex;
      gap: 0;
      margin-bottom: 20px;
      border-bottom: 1px solid rgba(255,255,255,.12);
    }
    .tab-btn {
      padding: 12px 20px;
      background: transparent;
      border: none;
      color: #ccc;
      cursor: pointer;
      font-weight: 600;
      border-bottom: 3px solid transparent;
      transition: all 0.2s;
    }
    .tab-btn:hover {
      color: #fff;
    }
    .tab-btn.active {
      color: #ff8000;
      border-bottom-color: #ff8000;
    }
    .tab-content {
      display: none;
    }
    .tab-content.active {
      display: block;
    }
    .program-card {
      background: #1b1b1b;
      border: 1px solid rgba(255,255,255,.12);
      border-radius: 10px;
      padding: 16px;
      margin-bottom: 12px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    .program-info h3 {
      margin: 0 0 6px;
      color: #ff8000;
    }
    .program-info p {
      margin: 4px 0;
      color: #ccc;
      font-size: 14px;
    }
    .program-actions {
      display: flex;
      gap: 8px;
    }
    .btn-small {
      padding: 8px 14px;
      background: #ff8000;
      color: #111;
      border: none;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 600;
      font-size: 12px;
    }
    .btn-small:hover {
      background: #cc6600;
    }
  </style>
</head>
<body>

<?php include __DIR__ . '/../views/partials/header.php'; ?>

<div class="wrap">
  <h1>Mon compte</h1>

  <div class="tabs">
    <button class="tab-btn <?= $tab === 'overview' ? 'active' : '' ?>" onclick="switchTab('overview')">
      Vue d'ensemble
    </button>
    <button class="tab-btn <?= $tab === 'programs' ? 'active' : '' ?>" onclick="switchTab('programs')">
      Mes programmes (<?= count($userPrograms) ?>)
    </button>
  </div>

  <div id="tab-overview" class="tab-content <?= $tab === 'overview' ? 'active' : '' ?>">
    <div class="card">
      <div class="grid">
        <div class="field">
          <span class="label">Nom</span>
          <div class="value"><?= htmlspecialchars($lastName ?: '—') ?></div>
        </div>

        <div class="field">
          <span class="label">Prénom</span>
          <div class="value"><?= htmlspecialchars($firstName ?: '—') ?></div>
        </div>

        <div class="field" style="grid-column: span 2">
          <span class="label">Adresse e-mail</span>
          <div class="value"><?= htmlspecialchars($email ?: '—') ?></div>
        </div>

        <div class="field" style="grid-column: span 2">
          <span class="label">Abonnement actif</span>
          <div class="value"><span class="pill orange"><?= htmlspecialchars($planLabel ?: '—') ?></span></div>
        </div>
      </div>

      <div class="actions">
        <a class="btn" href="abonnements.php">Changer d'abonnement</a>
        <a class="btn secondary" href="programmes.php">Acheter un programme</a>
      </div>
    </div>
  </div>

  <div id="tab-programs" class="tab-content <?= $tab === 'programs' ? 'active' : '' ?>">
    <div class="card">
      <?php if (count($userPrograms) === 0): ?>
        <p>Vous n'avez pas encore acheté de programme personnalisé.</p>
        <a class="btn" href="programmes.php">Découvrir nos programmes</a>
      <?php else: ?>
        <h2 style="margin-top: 0">Vos programmes personnalisés</h2>
        <?php foreach ($userPrograms as $prog): ?>
          <div class="program-card">
            <div class="program-info">
              <h3><?= htmlspecialchars($prog['program_name']) ?></h3>
              <p>Type: <strong><?= htmlspecialchars($PROGRAM_LABELS[$prog['program_type']] ?? $prog['program_type']) ?></strong></p>
              <p>Acheté le: <strong><?= date('d/m/Y à H:i', strtotime($prog['purchased_at'])) ?></strong></p>
              <p>Montant: <strong><?= number_format($prog['price'], 2, ',', ' ') ?> €</strong></p>
            </div>
            <div class="program-actions">
              <button class="btn-small" onclick="viewProgram(<?= $prog['id'] ?>)">Consulter</button>
              <button class="btn-small" onclick="downloadProgram(<?= $prog['id'] ?>)">Télécharger</button>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="card" style="margin-top: 20px">
    <div class="actions">
      <a class="btn secondary" href="index.php">Retour à l'accueil</a>
      <a class="btn secondary" href="logout.php">Se déconnecter</a>
    </div>
  </div>
</div>

<div id="programModal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,.8); z-index: 10000; overflow-y: auto; padding: 20px; padding-top: 80px;">
  <div style="background: #222; max-width: 800px; margin: 0 auto 20px; border-radius: 10px; padding: 20px; color: #fff;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
      <h3 style="margin: 0;">Programme personnalisé</h3>
      <button onclick="closeProgram()" style="background: #ff8000; border: none; padding: 8px 14px; cursor: pointer; border-radius: 6px; font-weight: 600; color: #111;">Fermer</button>
    </div>
    <div id="programContent"></div>
  </div>
</div>

<script>
function switchTab(tabName) {
  document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
  document.querySelectorAll('.tab-btn').forEach(el => el.classList.remove('active'));
  
  document.getElementById('tab-' + tabName).classList.add('active');
  event.target.classList.add('active');
  
  window.history.replaceState(null, '', '?tab=' + tabName);
}

function viewProgram(programId) {
  fetch('api/get-program.php?id=' + programId)
    .then(r => r.text())
    .then(html => {
      document.getElementById('programContent').innerHTML = html;
      document.getElementById('programModal').style.display = 'block';
    })
    .catch(e => alert('Erreur: ' + e.message));
}

function closeProgram() {
  document.getElementById('programModal').style.display = 'none';
}

function downloadProgram(programId) {
  window.location.href = 'api/download-program.php?id=' + programId;
}
</script>

</body>
</html>
