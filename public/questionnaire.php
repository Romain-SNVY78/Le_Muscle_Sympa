<?php
// Déterminer le type de programme depuis l'URL ?type=
$type = $_GET['type'] ?? 'renforcement';
$TYPE_LABELS = [
  'renforcement' => 'Renforcement musculaire',
  'endurance'    => 'Endurance',
  'esthetique'   => 'Esthétique',
  'entretien'    => 'Entretien',
];
$label = $TYPE_LABELS[$type] ?? $TYPE_LABELS['renforcement'];

if (session_status() === PHP_SESSION_NONE) { session_start(); }
$active = 'programs';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <meta charset="UTF-8" />
  <title>Questionnaire programme - Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link rel="stylesheet" href="assets/css/questionnaire.css">
</head>
<body>
  <?php include __DIR__ . '/../views/partials/header.php'; ?>

  <div class="wrap">
    <h1 id="title">Votre questionnaire</h1>
    <p class="subtitle">
      Quelques questions pour personnaliser votre programme <strong>(10 €)</strong>
      — <span class="pill"><?= htmlspecialchars($label) ?></span>.
    </p>

    <!-- FORMULAIRE PRINCIPAL : on garde novalidate off pour laisser HTML5 faire son job -->
    <form id="form" class="card">
      <div class="grid">
        <!-- Identité -->
        <div>
          <label for="prenom">Prénom</label>
          <input id="prenom" name="prenom" type="text" placeholder="Alex" required />
        </div>
        <div>
          <label for="email">Email</label>
          <input id="email" name="email" type="email" placeholder="vous@example.com" required />
        </div>

        <div class="row">
          <div>
            <label for="age">Âge</label>
            <input id="age" name="age" type="number" min="12" max="100" placeholder="24" required />
          </div>
          <div>
            <label for="poids">Poids (kg)</label>
            <input id="poids" name="poids" type="number" step="0.1" min="25" max="300" placeholder="72" required />
          </div>
          <div>
            <label for="taille">Taille (cm)</label>
            <input id="taille" name="taille" type="number" min="120" max="230" placeholder="178" required />
          </div>
        </div>

        <!-- Objectif & expérience -->
        <div>
          <label for="objectif">Objectif principal</label>
          <select id="objectif" name="objectif" required>
            <option value="">— Sélectionner —</option>
            <option value="prise_masse">Prise de masse</option>
            <option value="perte_poids">Perte de poids</option>
            <option value="force">Force</option>
            <option value="endurance">Endurance</option>
            <option value="forme">Remise/Entretien</option>
            <option value="esthetique">Esthétique / Tonus</option>
          </select>
          <div class="help">Nous adapterons les plages de répétitions, le volume et le cardio.</div>
        </div>

        <div>
          <label for="experience">Expérience en musculation</label>
          <select id="experience" name="experience" required>
            <option value="">— Sélectionner —</option>
            <option value="debutant">Débutant</option>
            <option value="intermediaire">Intermédiaire</option>
            <option value="avance">Avancé</option>
          </select>
        </div>

        <!-- Fréquence & planning -->
        <div>
          <label for="frequence">Séances / semaine souhaitées</label>
          <select id="frequence" name="frequence" required>
            <option value="">— Sélectionner —</option>
            <option>2</option><option>3</option><option>4</option><option>5</option><option>6</option>
          </select>
        </div>

        <div>
          <label for="jours">Jours disponibles (ex : Lun, Mer, Ven)</label>
          <input id="jours" name="jours" type="text" placeholder="Lun, Mer, Ven" />
        </div>

        <!-- Matériel & contraintes -->
        <div>
          <label for="equip">Équipements à disposition</label>
          <select id="equip" name="equip" required>
            <option value="">— Sélectionner —</option>
            <option value="salle_complete">Salle complète</option>
            <option value="home_basic">Maison (haltères, élastiques)</option>
            <option value="aucun">Aucun équipement</option>
          </select>
        </div>

        <div>
          <label for="contraintes">Blessures / contraintes</label>
          <textarea id="contraintes" name="contraintes" placeholder="Épaule fragile, lombaires, etc."></textarea>
        </div>

        <!-- Préférences -->
        <div>
          <label for="duree">Durée moyenne d'une séance (min)</label>
          <select id="duree" name="duree">
            <option value="">— Sélectionner —</option>
            <option>30</option><option>45</option><option>60</option><option>75</option><option>90</option>
          </select>
        </div>

        <div>
          <label for="preferences">Préférences (exos, styles, cardio...)</label>
          <textarea id="preferences" name="preferences" placeholder="J'aime le full-body / push-pull-legs, HIIT léger, etc."></textarea>
        </div>

        <!-- Champ caché programme -->
        <input type="hidden" id="programme" name="programme" value="<?= htmlspecialchars($type) ?>">
      </div>

      <div class="small">
        Vos informations ne sont utilisées que pour générer votre proposition de programme personnalisé (projet – simulation).
      </div>

      <div id="success" class="success" style="display:none">
        <h3>Merci ! ✅</h3>
        <p>Nous avons bien enregistré vos réponses pour le programme <strong id="success-type"></strong>.</p>
      </div>

      <div id="paybar" class="paybar" style="display:none">
        <p>Le programme personnalisé coûte <strong>10 €</strong>.</p>
        <a id="paylink" class="btn" href="#" role="button">Payer 10 € (simulation)</a>
        <div id="paid" class="paid">✅ Paiement confirmé (simulation). Vous recevrez votre programme par email.</div>
      </div>
    </form>

    <!-- BOUTON VERS PAY.PHP : on valide le questionnaire AVANT d'envoyer -->
    <form id="payForm" action="pay.php" method="post" class="card" style="margin-top:16px">
      <input type="hidden" name="kind" value="program">
      <input type="hidden" name="program" value="<?= htmlspecialchars($type) ?>">
      <button id="goPay" class="btn" type="submit">Recevoir mon programme</button>
    </form>
  </div>

  <script>
    // Bloque l'envoi si le questionnaire n'est pas valide ET stocke les données
    document.getElementById('goPay').addEventListener('click', function (e) {
      const form = document.getElementById('form');
      
      // Validation HTML5
      if (!form.reportValidity()) {
        e.preventDefault();
        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
        return;
      }
      
      // Avant envoi vers pay.php, stocker les données du questionnaire
      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());
      
      // Créer un input caché pour passer les données au serveur
      const payForm = document.getElementById('payForm');
      const dataInput = document.createElement('input');
      dataInput.type = 'hidden';
      dataInput.name = 'program_data';
      dataInput.value = JSON.stringify(data);
      payForm.appendChild(dataInput);
      
      // Stocker aussi en localStorage pour backup client
      const all = JSON.parse(localStorage.getItem('lms_questionnaires') || '[]');
      all.push({ date: new Date().toISOString(), ...data });
      localStorage.setItem('lms_questionnaires', JSON.stringify(all));
    });
  </script>

  <script src="assets/js/questionnaire.js"></script>
</body>
</html>
