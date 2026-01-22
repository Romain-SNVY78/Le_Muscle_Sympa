<?php
session_start();

/* Libellés / prix */
$LABELS_PLAN = ['solo'=>'SOLO','solo+'=>'SOLO +','duo'=>'DUO','duo+'=>'DUO +'];
$PRICES_PLAN = ['solo'=>20,'solo+'=>50,'duo'=>30,'duo+'=>80];

$LABELS_PROG = [
  'renforcement' => 'Renforcement musculaire',
  'endurance'    => 'Endurance',
  'esthetique'   => 'Esthétique',
  'entretien'    => 'Entretien'
];
$PRICE_PROG = 10;

/* Récupération contexte + champs paiement */
$kind       = $_POST['kind']    ?? null; // "plan" | "program"
$plan       = $_POST['plan']    ?? null;
$program    = $_POST['program'] ?? null;

$card_name   = trim($_POST['holder'] ?? '');
$card_number = trim($_POST['number'] ?? '');
$exp         = trim($_POST['exp'] ?? '');
$cvc         = trim($_POST['cvc'] ?? '');
$addr1       = trim($_POST['addr1'] ?? '');
$addr2       = trim($_POST['addr2'] ?? '');
$zip         = trim($_POST['zip']   ?? '');
$city        = trim($_POST['city']  ?? '');
$country     = trim($_POST['country'] ?? 'France');

/* Validation simplifiée + label/price */
if ($kind==='plan') {
  if (!$plan || !isset($LABELS_PLAN[$plan])) { header('Location: abonnements.php'); exit; }
  $label = $LABELS_PLAN[$plan]; $price = $PRICES_PLAN[$plan];
  $recapLabel = 'Formule';
} elseif ($kind==='program') {
  if (!$program || !isset($LABELS_PROG[$program])) { header('Location: programmes.php'); exit; }
  $label = $LABELS_PROG[$program]; $price = $PRICE_PROG;
  $recapLabel = 'Programme';
} else {
  header('Location: index.php'); exit;
}

/* Masquage carte */
$last4  = substr(preg_replace('/\D/','', $card_number), -4);
$masked = $last4 ? '**** **** **** '.$last4 : '**** **** **** ****';

/* 1) Sauvegarde BDD pour abonnements + programmes */
$save_ok = false;
if (!empty($_SESSION['user_id'])) {
  try {
    require_once __DIR__ . '/../app/db.php';
    if (isset($pdo) && $pdo instanceof PDO) {
      
      /* A) Mise à jour abonnement si plan */
      if ($kind==='plan') {
        $st = $pdo->prepare("UPDATE users SET plan = ? WHERE id = ?");
        $st->execute([$plan, $_SESSION['user_id']]);
        
        // Maj session
        $_SESSION['active_plan_code']  = $plan;
        $_SESSION['active_plan_label'] = $label;
      }
      
      /* B) Enregistrement du programme personnalisé si program */
      if ($kind==='program') {
        // Récupération des données du questionnaire depuis la session (stockées depuis questionnaire.php)
        $programData = $_SESSION['program_data'] ?? [];
        
        // Génération du contenu du programme (simulation)
        $programContent = generateProgram($program, $programData);
        
        // Enregistrement en BDD
        $st = $pdo->prepare("
          INSERT INTO user_programs (
            user_id, program_type, program_name,
            first_name, email, age, poids, taille,
            objectif, experience, frequence, jours,
            equip, contraintes, duree, preferences,
            program_content, price
          ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $st->execute([
          $_SESSION['user_id'],
          $program,
          $label,
          $programData['prenom'] ?? '',
          $programData['email'] ?? $_SESSION['email'] ?? '',
          $programData['age'] ?? null,
          $programData['poids'] ?? null,
          $programData['taille'] ?? null,
          $programData['objectif'] ?? '',
          $programData['experience'] ?? '',
          $programData['frequence'] ?? null,
          $programData['jours'] ?? '',
          $programData['equip'] ?? '',
          $programData['contraintes'] ?? '',
          $programData['duree'] ?? null,
          $programData['preferences'] ?? '',
          $programContent,
          $price
        ]);
        
        $save_ok = true;
        unset($_SESSION['program_data']);
      } else {
        $save_ok = true;
      }
    }
  } catch(Throwable $e) {
    error_log('Erreur enregistrement programme: ' . $e->getMessage());
  }
}

/**
 * Génère un programme personnalisé intelligent et fonctionnel
 * Adapté au type, objectif, niveau d'expérience et contraintes
 */
function generateProgram($type, $data) {
  $types = [
    'renforcement' => 'Renforcement musculaire',
    'endurance'    => 'Endurance cardiovasculaire',
    'esthetique'   => 'Programme esthétique',
    'entretien'    => 'Programme d\'entretien'
  ];
  
  $experience = $data['experience'] ?? 'intermediaire';
  $objectif = $data['objectif'] ?? 'forme';
  $frequence = (int)($data['frequence'] ?? 3);
  $contraintes = $data['contraintes'] ?? '';
  $equip = $data['equip'] ?? 'haltères, barres';
  
  $html = "<div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>";
  
  // HEADER
  $html .= "<h2 style='border-bottom: 2px solid #ff8000; padding-bottom: 10px; color: #111;'>";
  $html .= "📋 Programme personnalisé - " . htmlspecialchars($types[$type] ?? $type);
  $html .= "</h2>";
  
  // INFOS CLIENT
  $html .= "<div style='background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
  $html .= "<p style='margin: 5px 0;'><strong>👤 Client:</strong> " . htmlspecialchars($data['prenom'] ?? 'N/A') . " (" . (int)($data['age'] ?? 0) . " ans)</p>";
  $html .= "<p style='margin: 5px 0;'><strong>🎯 Objectif:</strong> " . htmlspecialchars(formatObjectif($objectif)) . "</p>";
  $html .= "<p style='margin: 5px 0;'><strong>💪 Niveau:</strong> " . htmlspecialchars(ucfirst($experience)) . "</p>";
  $html .= "<p style='margin: 5px 0;'><strong>📅 Fréquence:</strong> " . $frequence . " séances/semaine</p>";
  if ($equip) $html .= "<p style='margin: 5px 0;'><strong>🏋️ Équipements:</strong> " . htmlspecialchars($equip) . "</p>";
  if ($contraintes) $html .= "<p style='margin: 5px 0; color: #c33;'><strong>⚠️ Contraintes:</strong> " . htmlspecialchars($contraintes) . "</p>";
  $html .= "</div>";
  
  // STRUCTURE DE PROGRAMME ADAPTÉE
  $programs = generateProgramStructure($type, $experience, $frequence, $objectif, $contraintes);
  
  $html .= "<h3 style='color: #ff8000; margin-top: 25px;'>📅 Structure d'entraînement</h3>";
  $html .= $programs['schedule_html'];
  
  // DÉTAIL DES SÉANCES
  $html .= "<h3 style='color: #ff8000; margin-top: 25px;'>🏃 Séances détaillées</h3>";
  $html .= $programs['sessions_html'];
  
  // CONSEILS D'ALIMENTATION
  $html .= "<h3 style='color: #ff8000; margin-top: 25px;'>🍗 Nutrition et Récupération</h3>";
  $html .= generateNutritionAdvice($objectif, $data);
  
  // NOTES IMPORTANTES
  $html .= "<h3 style='color: #ff8000; margin-top: 25px;'>⚡ Conseils importants</h3>";
  $html .= "<ul style='background: #fff9e6; padding: 15px 30px; border-radius: 8px; border-left: 4px solid #ff8000;'>";
  $html .= "<li>Commencez par une semaine de familiarisation avec les mouvements</li>";
  $html .= "<li>Augmentez progressivement le poids de 2-5% quand vous maîtrisez le mouvement</li>";
  $html .= "<li>Repos minimal entre les séries: " . getRestTime($experience) . "s pour la force, " . getRestTime($experience, false) . "s pour l'hypertrophie</li>";
  $html .= "<li>Échauffement: 5-10 min cardio léger + étirements dynamiques</li>";
  $html .= "<li>Fraîcheur musculaire: 48h minimum entre deux séances des mêmes groupes musculaires</li>";
  if ($contraintes) $html .= "<li>⚠️ Respectez votre contrainte: " . htmlspecialchars($contraintes) . "</li>";
  $html .= "<li>Adaptez le programme si vous ressentirez douleur anormale</li>";
  $html .= "</ul>";
  
  // PROGRESSION
  $html .= "<h3 style='color: #ff8000; margin-top: 25px;'>📈 Suivi de progression</h3>";
  $html .= "<p style='background: #e6f2ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0078d4;'>";
  $html .= "Notez vos poids et répétitions à chaque séance pour suivre votre progression. ";
  $html .= "Objectif: +1 à 2 kg de charge ou +1 à 2 reps chaque semaine sur les mouvements principaux.";
  $html .= "</p>";
  
  $html .= "</div>";
  
  return $html;
}

/**
 * Génère la structure et les séances d'entraînement
 */
function generateProgramStructure($type, $experience, $frequence, $objectif, $contraintes) {
  $schedule_html = '';
  $sessions_html = '';
  
  // Sélection du split en fonction de la fréquence et du niveau
  if ($frequence <= 2) {
    $split = 'full_body';
  } elseif ($frequence <= 3) {
    $split = 'upper_lower';
  } elseif ($frequence <= 4) {
    $split = 'ppl';
  } else {
    $split = 'upper_lower_x2';
  }
  
  // Exercices de base par groupe musculaire
  $exercises = getExerciseBank($experience, $type, $objectif, $contraintes);
  
  // PLANNING HEBDOMADAIRE
  $schedule_html .= "<table style='width: 100%; border-collapse: collapse; margin: 15px 0;'>";
  $schedule_html .= "<thead><tr style='background: #ff8000; color: white;'>";
  
  if ($split === 'full_body') {
    $days = ['Lun', 'Mer', 'Ven'];
    for ($i = 0; $i < 7; $i++) {
      $schedule_html .= "<td style='padding: 10px; text-align: center; border: 1px solid #ddd;'>";
      $schedule_html .= $dayNames = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'][$i];
      $schedule_html .= "</td>";
    }
    $schedule_html .= "</tr></thead><tbody><tr>";
    for ($i = 0; $i < 7; $i++) {
      $dayName = ['Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam', 'Dim'][$i];
      if (in_array($dayName, $days)) {
        $schedule_html .= "<td style='padding: 10px; background: #fff3e0; text-align: center; border: 1px solid #ddd;'><strong>Full Body</strong></td>";
      } else {
        $schedule_html .= "<td style='padding: 10px; text-align: center; border: 1px solid #ddd; color: #999;'>Repos</td>";
      }
    }
  } elseif ($split === 'upper_lower') {
    $schedule = ['Lun' => 'Upper', 'Mar' => 'Repos', 'Mer' => 'Lower', 'Jeu' => 'Repos', 'Ven' => 'Upper', 'Sam' => 'Repos', 'Dim' => 'Repos'];
    foreach ($schedule as $day => $session) {
      $bg = $session === 'Repos' ? '#f5f5f5' : '#fff3e0';
      $color = $session === 'Repos' ? '#999' : '#111';
      $schedule_html .= "<td style='padding: 10px; background: $bg; text-align: center; border: 1px solid #ddd; color: $color;'>";
      $schedule_html .= $session === 'Repos' ? '✓ Repos' : "<strong>$session</strong>";
      $schedule_html .= "</td>";
    }
  } elseif ($split === 'ppl') {
    $schedule = ['Lun' => 'Push', 'Mar' => 'Pull', 'Mer' => 'Legs', 'Jeu' => 'Repos', 'Ven' => 'Push', 'Sam' => 'Pull', 'Dim' => 'Legs'];
    foreach ($schedule as $day => $session) {
      $bg = $session === 'Repos' ? '#f5f5f5' : '#fff3e0';
      $color = $session === 'Repos' ? '#999' : '#111';
      $schedule_html .= "<td style='padding: 10px; background: $bg; text-align: center; border: 1px solid #ddd; color: $color;'>";
      $schedule_html .= $session === 'Repos' ? '✓ Repos' : "<strong>$session</strong>";
      $schedule_html .= "</td>";
    }
  }
  
  $schedule_html .= "</tr></tbody></table>";
  
  // SÉANCES DÉTAILLÉES
  $sessions_data = getSessions($split, $exercises, $experience, $type, $objectif);
  
  foreach ($sessions_data as $session) {
    $sessions_html .= "<div style='background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ff8000;'>";
    $sessions_html .= "<h4 style='margin: 0 0 10px; color: #111;'>" . $session['name'] . " (" . $session['duration'] . "min)</h4>";
    $sessions_html .= "<table style='width: 100%; border-collapse: collapse;'>";
    $sessions_html .= "<thead><tr style='background: #f0f0f0; border-bottom: 2px solid #ddd;'>";
    $sessions_html .= "<th style='padding: 8px; text-align: left; border: 1px solid #ddd;'>Exercice</th>";
    $sessions_html .= "<th style='padding: 8px; text-align: center; border: 1px solid #ddd;'>Séries</th>";
    $sessions_html .= "<th style='padding: 8px; text-align: center; border: 1px solid #ddd;'>Reps</th>";
    $sessions_html .= "<th style='padding: 8px; text-align: center; border: 1px solid #ddd;'>Repos</th>";
    $sessions_html .= "</tr></thead><tbody>";
    
    foreach ($session['exercises'] as $ex) {
      $sessions_html .= "<tr style='border-bottom: 1px solid #ddd;'>";
      $sessions_html .= "<td style='padding: 8px; border: 1px solid #ddd;'><strong>" . $ex['name'] . "</strong><br><small style='color: #666;'>" . $ex['notes'] . "</small></td>";
      $sessions_html .= "<td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . $ex['sets'] . "</td>";
      $sessions_html .= "<td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . $ex['reps'] . "</td>";
      $sessions_html .= "<td style='padding: 8px; text-align: center; border: 1px solid #ddd;'>" . $ex['rest'] . "s</td>";
      $sessions_html .= "</tr>";
    }
    
    $sessions_html .= "</tbody></table>";
    $sessions_html .= "</div>";
  }
  
  return [
    'schedule_html' => $schedule_html,
    'sessions_html' => $sessions_html
  ];
}

/**
 * Retourne les sessions d'entraînement détaillées
 */
function getSessions($split, $exercises, $experience, $type, $objectif) {
  $sessions = [];
  
  if ($split === 'full_body') {
    $sessions[] = [
      'name' => 'Séance Full Body A',
      'duration' => 60,
      'exercises' => [
        ['name' => 'Squat / Leg Press', 'sets' => 3, 'reps' => '8-12', 'rest' => 120, 'notes' => 'Mouvement principal'],
        ['name' => 'Développé couché / Push-up', 'sets' => 3, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Poitrine / Triceps'],
        ['name' => 'Tirage / Rowing', 'sets' => 3, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Dos / Biceps'],
        ['name' => 'Développé militaire', 'sets' => 2, 'reps' => '10-12', 'rest' => 75, 'notes' => 'Épaules'],
        ['name' => 'Curls / Extensions', 'sets' => 2, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Isolation'],
        ['name' => 'Cardio léger', 'sets' => 1, 'reps' => '5-10 min', 'rest' => 0, 'notes' => 'Cool-down'],
      ]
    ];
  } elseif ($split === 'upper_lower') {
    $sessions[] = [
      'name' => 'Upper Body',
      'duration' => 60,
      'exercises' => [
        ['name' => 'Développé couché / Incline', 'sets' => 4, 'reps' => '6-10', 'rest' => 120, 'notes' => 'Force / Hypertrophie'],
        ['name' => 'Tirage vertical / Horizontal', 'sets' => 4, 'reps' => '6-10', 'rest' => 120, 'notes' => 'Dos'],
        ['name' => 'Développé militaire', 'sets' => 3, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Épaules'],
        ['name' => 'Rowing barbell', 'sets' => 3, 'reps' => '8-10', 'rest' => 90, 'notes' => 'Épaisseur dos'],
        ['name' => 'Curls / Triceps pushdown', 'sets' => 3, 'reps' => '10-15', 'rest' => 60, 'notes' => 'Isolation'],
      ]
    ];
    $sessions[] = [
      'name' => 'Lower Body',
      'duration' => 60,
      'exercises' => [
        ['name' => 'Squat / Front squat', 'sets' => 4, 'reps' => '6-10', 'rest' => 150, 'notes' => 'Force / Quadriceps'],
        ['name' => 'Soulevé de terre / Leg press', 'sets' => 3, 'reps' => '6-8', 'rest' => 150, 'notes' => 'Chaîne postérieure'],
        ['name' => 'Leg curl / Extensions', 'sets' => 3, 'reps' => '10-12', 'rest' => 75, 'notes' => 'Ischio / Quad'],
        ['name' => 'Calf raises / Mollets', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Mollets'],
      ]
    ];
  } elseif ($split === 'ppl') {
    $sessions[] = [
      'name' => 'Push (Poitrine / Épaules / Triceps)',
      'duration' => 60,
      'exercises' => [
        ['name' => 'Développé couché', 'sets' => 4, 'reps' => '6-10', 'rest' => 120, 'notes' => 'Principal'],
        ['name' => 'Développé incline', 'sets' => 3, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Poitrine haute'],
        ['name' => 'Développé militaire', 'sets' => 3, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Épaules'],
        ['name' => 'Écartés / Fly', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Poitrine'],
        ['name' => 'Triceps pushdown', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Isolation'],
      ]
    ];
    $sessions[] = [
      'name' => 'Pull (Dos / Biceps)',
      'duration' => 60,
      'exercises' => [
        ['name' => 'Tirage menton / Lat pulldown', 'sets' => 4, 'reps' => '6-10', 'rest' => 120, 'notes' => 'Principal'],
        ['name' => 'Rowing barbell', 'sets' => 3, 'reps' => '8-10', 'rest' => 90, 'notes' => 'Épaisseur'],
        ['name' => 'Tirage vertical', 'sets' => 3, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Largeur'],
        ['name' => 'Curls barbell', 'sets' => 3, 'reps' => '8-12', 'rest' => 75, 'notes' => 'Biceps'],
        ['name' => 'Curl marteau', 'sets' => 2, 'reps' => '10-12', 'rest' => 60, 'notes' => 'Brachial'],
      ]
    ];
    $sessions[] = [
      'name' => 'Legs (Jambes)',
      'duration' => 65,
      'exercises' => [
        ['name' => 'Squat', 'sets' => 4, 'reps' => '6-10', 'rest' => 150, 'notes' => 'Principal'],
        ['name' => 'Leg press', 'sets' => 3, 'reps' => '8-12', 'rest' => 120, 'notes' => 'Quadriceps'],
        ['name' => 'Leg curl', 'sets' => 3, 'reps' => '10-12', 'rest' => 75, 'notes' => 'Ischio'],
        ['name' => 'Extensions jambes', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Finition'],
        ['name' => 'Calf raises', 'sets' => 4, 'reps' => '12-15', 'rest' => 45, 'notes' => 'Mollets'],
      ]
    ];
  }
  
  return $sessions;
}

/**
 * Conseils nutritionnels personnalisés
 */
function generateNutritionAdvice($objectif, $data) {
  $poids = (float)($data['poids'] ?? 70);
  $age = (int)($data['age'] ?? 30);
  
  $html = "<div style='background: #f0f7ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0078d4;'>";
  
  $calPerkg = 30; // Calories par kg de poids corporel
  $totalCal = (int)($poids * $calPerkg);
  
  if ($objectif === 'prise_masse') {
    $html .= "<p><strong>Surplus calorique:</strong> +300-500 kcal/jour</p>";
    $html .= "<p><strong>Calories cibles:</strong> " . ($totalCal + 400) . " kcal/jour</p>";
    $html .= "<p><strong>Protéines:</strong> " . (int)($poids * 2) . "-" . (int)($poids * 2.2) . "g/jour (Viande, oeufs, fromage blanc, protéine en poudre)</p>";
    $html .= "<p><strong>Glucides:</strong> 4-6g par kg de poids corporel (Riz, pâtes, patates)</p>";
    $html .= "<p><strong>Lipides:</strong> 0.8-1.2g par kg de poids corporel (Huile olive, avocado, oléagineux)</p>";
  } elseif ($objectif === 'perte_poids') {
    $html .= "<p><strong>Déficit calorique:</strong> -300-500 kcal/jour</p>";
    $html .= "<p><strong>Calories cibles:</strong> " . ($totalCal - 400) . " kcal/jour</p>";
    $html .= "<p><strong>Protéines:</strong> " . (int)($poids * 2.2) . "-" . (int)($poids * 2.5) . "g/jour (Essentiel pour préserver muscle)</p>";
    $html .= "<p><strong>Glucides:</strong> 2-3g par kg de poids corporel</p>";
    $html .= "<p><strong>Lipides:</strong> 0.6-0.8g par kg de poids corporel</p>";
  } else {
    $html .= "<p><strong>Maintenance calorique:</strong> ~" . $totalCal . " kcal/jour</p>";
    $html .= "<p><strong>Protéines:</strong> " . (int)($poids * 1.8) . "-" . (int)($poids * 2.2) . "g/jour</p>";
    $html .= "<p><strong>Glucides:</strong> 3-4g par kg de poids corporel</p>";
    $html .= "<p><strong>Lipides:</strong> 0.8-1g par kg de poids corporel</p>";
  }
  
  $html .= "<p style='margin-top: 15px;'><strong>💧 Hydratation:</strong> 30-35 ml d'eau par kg de poids corporel = " . (int)($poids * 30 / 1000) . "-" . (int)($poids * 35 / 1000) . "L/jour</p>";
  $html .= "<p><strong>🥗 Timing:</strong> Petit-déj 30min après réveil • Collation avant entraînement • Post-workout immédiatement après</p>";
  
  $html .= "</div>";
  
  return $html;
}

/**
 * Retourne le temps de repos optimal
 */
function getRestTime($experience, $isStrength = true) {
  if ($isStrength) {
    return $experience === 'debutant' ? '90-120' : ($experience === 'intermediaire' ? '120-180' : '180-240');
  } else {
    return $experience === 'debutant' ? '60-90' : ($experience === 'intermediaire' ? '60-120' : '90-150');
  }
}

/**
 * Formate le nom de l'objectif
 */
function formatObjectif($obj) {
  $labels = [
    'prise_masse' => 'Prise de masse musculaire',
    'perte_poids' => 'Perte de poids',
    'force' => 'Force maximale',
    'endurance' => 'Endurance musculaire',
    'forme' => 'Remise en forme / Entretien',
    'esthetique' => 'Esthétique / Tonus'
  ];
  return $labels[$obj] ?? $obj;
}

/**
 * Banque d'exercices personnalisée
 */
function getExerciseBank($experience, $type, $objectif, $contraintes) {
  // Exercices simples adaptés au niveau
  return [
    'poitrine' => ['Développé couché', 'Développé décliné', 'Écartés machine', 'Push-ups'],
    'dos' => ['Tirage vertical', 'Tirage horizontal', 'Rowing', 'Tirage menton'],
    'jambes' => ['Squat', 'Leg press', 'Hack squat', 'Leg curl'],
    'epaules' => ['Développé militaire', 'Développé assis', 'Élévations latérales', 'Shrugs'],
    'bras' => ['Curls barbell', 'Curls haltères', 'Triceps dips', 'Triceps pushdown']
  ];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Paiement confirmé – Le Muscle Sympa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="assets/css/base.css">
  <link rel="stylesheet" href="assets/css/header.css">
  <style>
    :root{ --orange:#ff8000; --bg:#222; --panel:#2b2b2b; --text:#fff; --muted:#cfcfcf; --bd:rgba(255,255,255,.12); }
    html,body{margin:0;padding:0;background:var(--bg);color:var(--text);font-family:Arial,Helvetica,sans-serif}
    .wrap{max-width:1000px;margin:30px auto;padding:0 18px}
    .card{background:var(--panel);border:1px solid var(--bd);border-radius:16px;padding:22px;box-shadow:0 12px 30px rgba(0,0,0,.35)}
    h1{margin:0 0 12px}
    .ok{display:inline-block;background:#143a14;border:1px solid #2f8f2f;border-radius:999px;padding:6px 10px;font-weight:800;margin-bottom:10px}
    .warn{display:inline-block;background:#3a2b14;border:1px solid #b8841f;border-radius:999px;padding:6px 10px;font-weight:800;margin-bottom:10px}
    .grid{display:grid;grid-template-columns:1fr 1fr;gap:18px}
    @media (max-width:900px){ .grid{grid-template-columns:1fr} }
    .box{background:#1b1b1b;border:1px solid var(--bd);border-radius:14px;padding:16px}
    .big{font-size:32px;font-weight:800}
    .muted{color:var(--muted)}
    .pill{display:inline-block;background:rgba(255,128,0,.15);border:1px solid rgba(255,128,0,.45);border-radius:999px;padding:8px 14px;font-weight:800}
    .actions{display:flex;gap:12px;flex-wrap:wrap;margin-top:18px}
    .btn{border:none;border-radius:10px;padding:12px 18px;font-weight:800;cursor:pointer;background:var(--orange);color:#111}
    .btn.secondary{background:transparent;color:#fff;border:1px solid var(--bd)}
  </style>
</head>
<body>

<?php include __DIR__ . '/../views/partials/header.php'; ?>

<div class="wrap">
  <div class="card">
    <span class="ok">Paiement validé ✔</span>
    <h1>Merci !</h1>
    <p><?= $kind==='plan' ? 'Votre abonnement' : 'Votre achat de programme' ?> <strong><?= htmlspecialchars($label) ?></strong> est maintenant <strong>confirmé</strong>.
       <?php if ($kind === 'program'): ?>
         Vous retrouverez votre programme personnalisé dans votre compte.
       <?php endif; ?>
    </p>

    <?php if ($kind==='plan' && !$save_ok): ?>
      <p class="warn">Vous n'étiez pas connecté : l'abonnement a été mémorisé et sera enregistré en base à votre prochaine connexion.</p>
    <?php endif; ?>

    <div class="grid" style="margin-top:10px">
      <div class="box">
        <div class="big"><?= (int)$price ?>€ <span class="muted" style="font-size:16px;font-weight:400">/ <?= $kind==='plan'?'mois':'achat' ?></span></div>
        <p class="muted" style="margin:8px 0 0"><?= htmlspecialchars($recapLabel) ?> : <span class="pill"><?= htmlspecialchars($label) ?></span></p>
      </div>
      <div class="box">
        <h3 style="margin:0 0 6px">Carte utilisée</h3>
        <div><strong><?= htmlspecialchars($card_name ?: '—') ?></strong></div>
        <div><?= htmlspecialchars($masked) ?> — Exp: <?= htmlspecialchars($exp ?: '—') ?></div>
      </div>
    </div>

    <?php if ($addr1 || $city || $zip): ?>
      <div class="box" style="margin-top:16px">
        <h3 style="margin:0 0 6px">Adresse de facturation</h3>
        <div><?= htmlspecialchars($addr1) ?></div>
        <?php if ($addr2): ?><div><?= htmlspecialchars($addr2) ?></div><?php endif; ?>
        <div><?= htmlspecialchars($zip) ?> <?= htmlspecialchars($city) ?></div>
        <div><?= htmlspecialchars($country) ?></div>
      </div>
    <?php endif; ?>

    <div class="actions">
      <?php if ($kind==='plan'): ?>
        <a class="btn" href="profile.php">Voir mon compte</a>
      <?php else: ?>
        <a class="btn" href="profile.php?tab=programs">Mes programmes</a>
      <?php endif; ?>
      <a class="btn secondary" href="index.php">Retour à l'accueil</a>
    </div>
  </div>
</div>

</body>
</html>
