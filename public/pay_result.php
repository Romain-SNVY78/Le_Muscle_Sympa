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
  
  // RÉCUPÉRER LES SÉANCES POUR AFFICHER LES VRAIS NOMS DANS LE PLANNING
  $sessions_data = getSessions($split, $exercises, $experience, $type, $objectif);
  
  // PLANNING PAR JOURS (Jour 1, Jour 2, etc.) - Avec les vrais noms de séances
  $schedule_html .= "<div style='background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0;'>";
  $schedule_html .= "<p style='margin: 0 0 10px; color: #666;'><strong>💡 Structure flexible :</strong> Répartissez ces séances selon vos disponibilités, en respectant au moins 48h de repos entre deux séances du même groupe musculaire.</p>";
  $schedule_html .= "<ul style='list-style: none; padding: 0; margin: 0;'>";
  
  // Afficher les séances en fonction de la fréquence demandée
  for ($i = 1; $i <= $frequence; $i++) {
    $session_index = ($i - 1) % count($sessions_data);
    $session_name = $sessions_data[$session_index]['name'];
    $schedule_html .= "<li style='padding: 8px; margin: 5px 0; background: #fff3e0; border-left: 4px solid #ff8000; border-radius: 4px;'><strong>Jour $i :</strong> $session_name</li>";
  }
  
  $schedule_html .= "</ul></div>";
  
  // SÉANCES DÉTAILLÉES
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
 * Adapte les exercices selon le TYPE de programme (endurance, renforcement, etc.)
 */
function getSessions($split, $exercises, $experience, $type, $objectif) {
  $sessions = [];
  
  // ENDURANCE : Focus cardio et circuits
  if ($type === 'endurance') {
    $sessions[] = [
      'name' => 'Séance Cardio & Circuits 1',
      'duration' => 45,
      'exercises' => [
        ['name' => 'Échauffement cardio léger', 'sets' => 1, 'reps' => '5-10 min', 'rest' => 0, 'notes' => 'Montée progressive en intensité'],
        ['name' => $exercises['cardio_principal'][0] ?? 'Course à pied', 'sets' => 1, 'reps' => '20-30 min', 'rest' => 0, 'notes' => 'Zone 65-75% FCmax'],
        ['name' => $exercises['circuit_musculaire'][0] ?? 'Squat poids du corps', 'sets' => 3, 'reps' => '20-25', 'rest' => 30, 'notes' => 'Circuit léger'],
        ['name' => $exercises['circuit_musculaire'][1] ?? 'Push-ups', 'sets' => 3, 'reps' => '15-20', 'rest' => 30, 'notes' => 'Circuit léger'],
        ['name' => $exercises['gainage'][0] ?? 'Planche frontale', 'sets' => 3, 'reps' => '30-60s', 'rest' => 45, 'notes' => 'Gainage'],
        ['name' => 'Retour au calme cardio', 'sets' => 1, 'reps' => '5-10 min', 'rest' => 0, 'notes' => 'Zone récupération'],
      ]
    ];
    $sessions[] = [
      'name' => 'Séance HIIT Intense',
      'duration' => 35,
      'exercises' => [
        ['name' => 'Échauffement dynamique', 'sets' => 1, 'reps' => '5-8 min', 'rest' => 0, 'notes' => 'Mobilisations articulaires'],
        ['name' => $exercises['hiit'][0] ?? 'Burpees', 'sets' => 6, 'reps' => '30s work / 30s repos', 'rest' => 30, 'notes' => 'Haute intensité'],
        ['name' => $exercises['hiit'][1] ?? 'Mountain climbers', 'sets' => 6, 'reps' => '30s work / 30s repos', 'rest' => 30, 'notes' => 'Cardio explosif'],
        ['name' => $exercises['hiit'][2] ?? 'Jump squats', 'sets' => 5, 'reps' => '15-20', 'rest' => 45, 'notes' => 'Puissance jambes'],
        ['name' => $exercises['gainage'][1] ?? 'Planche latérale', 'sets' => 2, 'reps' => '30s/côté', 'rest' => 30, 'notes' => 'Stabilité'],
        ['name' => 'Stretching', 'sets' => 1, 'reps' => '5-10 min', 'rest' => 0, 'notes' => 'Récupération'],
      ]
    ];
    if ($split === 'full_body' || $split === 'upper_lower') {
      $sessions[] = [
        'name' => 'Séance Cardio Modéré',
        'duration' => 40,
        'exercises' => [
          ['name' => $exercises['cardio_faible_impact'][0] ?? 'Marche rapide inclinée', 'sets' => 1, 'reps' => '30-40 min', 'rest' => 0, 'notes' => 'Zone 60-70% FCmax'],
          ['name' => $exercises['circuit_musculaire'][3] ?? 'Rowing léger', 'sets' => 3, 'reps' => '15-20', 'rest' => 45, 'notes' => 'Haut du corps'],
          ['name' => $exercises['gainage'][0] ?? 'Planche', 'sets' => 3, 'reps' => '40-60s', 'rest' => 60, 'notes' => 'Core'],
        ]
      ];
    }
  }
  
  // RENFORCEMENT / FORCE : Charges lourdes, faible volume
  elseif ($type === 'renforcement' || $type === 'force') {
    if ($split === 'full_body') {
      $sessions[] = [
        'name' => 'Full Body Force A',
        'duration' => 60,
        'exercises' => [
          ['name' => $exercises['composes_principaux'][0] ?? 'Squat lourd', 'sets' => 5, 'reps' => '3-5', 'rest' => 180, 'notes' => 'Mouvement principal - 80-90% 1RM'],
          ['name' => $exercises['force_haut_corps'][0] ?? 'Bench press', 'sets' => 5, 'reps' => '4-6', 'rest' => 180, 'notes' => 'Force poitrine'],
          ['name' => $exercises['force_dos'][1] ?? 'Weighted pull-ups', 'sets' => 4, 'reps' => '4-6', 'rest' => 150, 'notes' => 'Force dos'],
          ['name' => $exercises['force_haut_corps'][1] ?? 'Overhead press', 'sets' => 4, 'reps' => '5-8', 'rest' => 120, 'notes' => 'Épaules'],
          ['name' => $exercises['assistance_force'][2] ?? 'Romanian deadlift', 'sets' => 3, 'reps' => '6-8', 'rest' => 120, 'notes' => 'Assistance'],
        ]
      ];
    } elseif ($split === 'upper_lower') {
      $sessions[] = [
        'name' => 'Upper Force',
        'duration' => 60,
        'exercises' => [
          ['name' => $exercises['force_haut_corps'][0] ?? 'Bench press', 'sets' => 5, 'reps' => '3-5', 'rest' => 180, 'notes' => 'Force maximale'],
          ['name' => $exercises['force_haut_corps'][1] ?? 'Overhead press', 'sets' => 4, 'reps' => '5-8', 'rest' => 150, 'notes' => 'Force épaules'],
          ['name' => $exercises['force_dos'][2] ?? 'Barbell row lourd', 'sets' => 4, 'reps' => '5-8', 'rest' => 150, 'notes' => 'Dos'],
          ['name' => $exercises['force_dos'][1] ?? 'Weighted pull-ups', 'sets' => 4, 'reps' => '4-6', 'rest' => 150, 'notes' => 'Vertical'],
          ['name' => $exercises['assistance_force'][1] ?? 'Close grip bench', 'sets' => 3, 'reps' => '6-8', 'rest' => 120, 'notes' => 'Triceps'],
        ]
      ];
      $sessions[] = [
        'name' => 'Lower Force',
        'duration' => 65,
        'exercises' => [
          ['name' => $exercises['composes_principaux'][0] ?? 'Squat', 'sets' => 5, 'reps' => '3-5', 'rest' => 210, 'notes' => 'Squat principal - 85-90% 1RM'],
          ['name' => $exercises['composes_principaux'][1] ?? 'Soulevé de terre', 'sets' => 4, 'reps' => '3-5', 'rest' => 210, 'notes' => 'Deadlift - force maximale'],
          ['name' => $exercises['force_jambes'][3] ?? 'Bulgarian split squat lesté', 'sets' => 3, 'reps' => '6-8/jambe', 'rest' => 120, 'notes' => 'Unilatéral'],
          ['name' => $exercises['force_jambes'][4] ?? 'Hip thrust lourd', 'sets' => 4, 'reps' => '6-8', 'rest' => 120, 'notes' => 'Fessiers'],
          ['name' => $exercises['force_dos'][4] ?? 'Farmer walks', 'sets' => 4, 'reps' => '40-60m', 'rest' => 90, 'notes' => 'Grip & core'],
        ]
      ];
    } elseif ($split === 'ppl') {
      $sessions[] = [
        'name' => 'Push Force',
        'duration' => 55,
        'exercises' => [
          ['name' => $exercises['force_haut_corps'][0] ?? 'Bench press', 'sets' => 5, 'reps' => '4-6', 'rest' => 180, 'notes' => 'Force maximale'],
          ['name' => $exercises['force_haut_corps'][1] ?? 'Overhead press', 'sets' => 4, 'reps' => '5-8', 'rest' => 150, 'notes' => 'Épaules'],
          ['name' => $exercises['force_haut_corps'][4] ?? 'Dips lestés', 'sets' => 4, 'reps' => '5-8', 'rest' => 120, 'notes' => 'Composé poussée'],
          ['name' => $exercises['assistance_force'][1] ?? 'Close grip bench', 'sets' => 3, 'reps' => '6-8', 'rest' => 120, 'notes' => 'Triceps'],
        ]
      ];
      $sessions[] = [
        'name' => 'Pull Force',
        'duration' => 55,
        'exercises' => [
          ['name' => $exercises['composes_principaux'][1] ?? 'Soulevé de terre', 'sets' => 5, 'reps' => '3-5', 'rest' => 210, 'notes' => 'Deadlift principal'],
          ['name' => $exercises['force_dos'][1] ?? 'Weighted pull-ups', 'sets' => 4, 'reps' => '4-6', 'rest' => 150, 'notes' => 'Vertical'],
          ['name' => $exercises['force_dos'][2] ?? 'Barbell row lourd', 'sets' => 4, 'reps' => '5-8', 'rest' => 150, 'notes' => 'Horizontal'],
          ['name' => $exercises['force_dos'][3] ?? 'Rack pulls', 'sets' => 3, 'reps' => '4-6', 'rest' => 150, 'notes' => 'Surcharge'],
        ]
      ];
      $sessions[] = [
        'name' => 'Legs Force',
        'duration' => 65,
        'exercises' => [
          ['name' => $exercises['force_jambes'][0] ?? 'Back squat lourd', 'sets' => 5, 'reps' => '3-5', 'rest' => 210, 'notes' => 'Principal'],
          ['name' => $exercises['force_jambes'][2] ?? 'Leg press lourd', 'sets' => 4, 'reps' => '6-8', 'rest' => 150, 'notes' => 'Volume jambes'],
          ['name' => $exercises['assistance_force'][2] ?? 'Romanian deadlift', 'sets' => 4, 'reps' => '6-8', 'rest' => 120, 'notes' => 'Ischio'],
          ['name' => $exercises['force_jambes'][4] ?? 'Hip thrust lourd', 'sets' => 4, 'reps' => '6-8', 'rest' => 120, 'notes' => 'Fessiers'],
        ]
      ];
    }
  }
  
  // ESTHÉTIQUE / PRISE DE MASSE : Volume élevé, hypertrophie
  elseif ($type === 'esthetique' || $objectif === 'prise_masse') {
    if ($split === 'full_body') {
      $sessions[] = [
        'name' => 'Full Body Hypertrophie',
        'duration' => 60,
        'exercises' => [
          ['name' => $exercises['jambes_quad'][0] ?? 'Squat', 'sets' => 4, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Tempo contrôlé'],
          ['name' => $exercises['poitrine_hypertrophie'][0] ?? 'Développé couché', 'sets' => 4, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Hypertrophie poitrine'],
          ['name' => $exercises['dos_largeur'][0] ?? 'Tirage vertical', 'sets' => 4, 'reps' => '8-12', 'rest' => 75, 'notes' => 'Largeur dos'],
          ['name' => $exercises['epaules_hypertrophie'][0] ?? 'Développé militaire', 'sets' => 3, 'reps' => '10-12', 'rest' => 75, 'notes' => 'Épaules'],
          ['name' => $exercises['bras_biceps'][0] ?? 'Curls barre', 'sets' => 3, 'reps' => '10-12', 'rest' => 60, 'notes' => 'Biceps'],
          ['name' => $exercises['bras_triceps'][1] ?? 'Triceps pushdown', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Triceps'],
        ]
      ];
    } elseif ($split === 'upper_lower') {
      $sessions[] = [
        'name' => 'Upper Hypertrophie',
        'duration' => 60,
        'exercises' => [
          ['name' => $exercises['poitrine_hypertrophie'][0] ?? 'Développé couché', 'sets' => 4, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Poitrine'],
          ['name' => $exercises['poitrine_hypertrophie'][1] ?? 'Développé incliné', 'sets' => 3, 'reps' => '10-12', 'rest' => 75, 'notes' => 'Haut pectoraux'],
          ['name' => $exercises['dos_largeur'][0] ?? 'Tirage vertical', 'sets' => 4, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Largeur'],
          ['name' => $exercises['dos_epaisseur'][0] ?? 'Rowing barre', 'sets' => 4, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Épaisseur'],
          ['name' => $exercises['epaules_hypertrophie'][1] ?? 'Élévations latérales', 'sets' => 4, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Deltoïdes latéraux'],
          ['name' => $exercises['bras_biceps'][1] ?? 'Curl haltères', 'sets' => 3, 'reps' => '10-12', 'rest' => 60, 'notes' => 'Biceps'],
          ['name' => $exercises['bras_triceps'][1] ?? 'Triceps pushdown', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Triceps'],
        ]
      ];
      $sessions[] = [
        'name' => 'Lower Hypertrophie',
        'duration' => 60,
        'exercises' => [
          ['name' => $exercises['jambes_quad'][0] ?? 'Squat', 'sets' => 4, 'reps' => '8-12', 'rest' => 120, 'notes' => 'Quadriceps'],
          ['name' => $exercises['jambes_quad'][1] ?? 'Leg press', 'sets' => 4, 'reps' => '10-15', 'rest' => 90, 'notes' => 'Volume jambes'],
          ['name' => $exercises['jambes_ischio'][0] ?? 'Romanian deadlift', 'sets' => 4, 'reps' => '10-12', 'rest' => 90, 'notes' => 'Ischio'],
          ['name' => $exercises['jambes_ischio'][1] ?? 'Leg curl', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Isolation ischio'],
          ['name' => $exercises['jambes_quad'][3] ?? 'Extensions jambes', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Finition quad'],
          ['name' => 'Mollets debout', 'sets' => 4, 'reps' => '15-20', 'rest' => 45, 'notes' => 'Mollets'],
        ]
      ];
    } elseif ($split === 'ppl') {
      $sessions[] = [
        'name' => 'Push (Poitrine / Épaules / Triceps)',
        'duration' => 60,
        'exercises' => [
          ['name' => $exercises['poitrine_hypertrophie'][0] ?? 'Développé couché', 'sets' => 4, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Principal'],
          ['name' => $exercises['poitrine_hypertrophie'][1] ?? 'Développé incliné', 'sets' => 3, 'reps' => '10-12', 'rest' => 75, 'notes' => 'Haut pecs'],
          ['name' => $exercises['poitrine_hypertrophie'][3] ?? 'Cable fly', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Isolation'],
          ['name' => $exercises['epaules_hypertrophie'][0] ?? 'Développé militaire', 'sets' => 4, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Épaules'],
          ['name' => $exercises['epaules_hypertrophie'][1] ?? 'Élévations latérales', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Deltoïdes'],
          ['name' => $exercises['bras_triceps'][0] ?? 'Dips', 'sets' => 3, 'reps' => '8-12', 'rest' => 75, 'notes' => 'Triceps'],
          ['name' => $exercises['bras_triceps'][1] ?? 'Triceps pushdown', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Isolation triceps'],
        ]
      ];
      $sessions[] = [
        'name' => 'Pull (Dos / Biceps)',
        'duration' => 60,
        'exercises' => [
          ['name' => $exercises['dos_largeur'][2] ?? 'Pull-ups', 'sets' => 4, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Largeur'],
          ['name' => $exercises['dos_epaisseur'][0] ?? 'Rowing barre', 'sets' => 4, 'reps' => '8-12', 'rest' => 90, 'notes' => 'Épaisseur'],
          ['name' => $exercises['dos_largeur'][1] ?? 'Lat pulldown', 'sets' => 3, 'reps' => '10-12', 'rest' => 75, 'notes' => 'Volume dos'],
          ['name' => $exercises['dos_epaisseur'][3] ?? 'Dumbbell row', 'sets' => 3, 'reps' => '10-12', 'rest' => 75, 'notes' => 'Unilatéral'],
          ['name' => $exercises['epaules_hypertrophie'][3] ?? 'Face pull', 'sets' => 3, 'reps' => '15-20', 'rest' => 60, 'notes' => 'Deltoïdes postérieurs'],
          ['name' => $exercises['bras_biceps'][0] ?? 'Curls barre', 'sets' => 3, 'reps' => '10-12', 'rest' => 60, 'notes' => 'Biceps'],
          ['name' => $exercises['bras_biceps'][2] ?? 'Curl marteau', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Brachial'],
        ]
      ];
      $sessions[] = [
        'name' => 'Legs (Jambes)',
        'duration' => 60,
        'exercises' => [
          ['name' => $exercises['jambes_quad'][0] ?? 'Squat', 'sets' => 4, 'reps' => '8-12', 'rest' => 120, 'notes' => 'Principal'],
          ['name' => $exercises['jambes_quad'][1] ?? 'Leg press', 'sets' => 4, 'reps' => '10-15', 'rest' => 90, 'notes' => 'Volume'],
          ['name' => $exercises['jambes_ischio'][0] ?? 'Romanian deadlift', 'sets' => 4, 'reps' => '10-12', 'rest' => 90, 'notes' => 'Ischio'],
          ['name' => $exercises['jambes_ischio'][1] ?? 'Leg curl', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Isolation'],
          ['name' => $exercises['jambes_quad'][3] ?? 'Extensions jambes', 'sets' => 3, 'reps' => '12-15', 'rest' => 60, 'notes' => 'Finition quad'],
          ['name' => 'Mollets', 'sets' => 4, 'reps' => '15-20', 'rest' => 45, 'notes' => 'Mollets'],
        ]
      ];
    }
  }
  
  // ENTRETIEN / FORME : Mix équilibré
  else {
    if ($split === 'full_body') {
      $sessions[] = [
        'name' => 'Full Body Équilibré',
        'duration' => 50,
        'exercises' => [
          ['name' => $exercises['composes_moderés'][0] ?? 'Squat goblet', 'sets' => 3, 'reps' => '10-15', 'rest' => 75, 'notes' => 'Jambes'],
          ['name' => $exercises['composes_moderés'][1] ?? 'Push-ups', 'sets' => 3, 'reps' => '12-20', 'rest' => 60, 'notes' => 'Poitrine'],
          ['name' => $exercises['composes_moderés'][2] ?? 'Rowing haltère', 'sets' => 3, 'reps' => '10-15', 'rest' => 60, 'notes' => 'Dos'],
          ['name' => $exercises['fonctionnel'][0] ?? 'Kettlebell swings', 'sets' => 3, 'reps' => '15-20', 'rest' => 60, 'notes' => 'Fonctionnel'],
          ['name' => $exercises['gainage_equilibre'][0] ?? 'Planche', 'sets' => 3, 'reps' => '30-60s', 'rest' => 45, 'notes' => 'Core'],
          ['name' => $exercises['cardio_modere'][0] ?? 'Course légère', 'sets' => 1, 'reps' => '10-15 min', 'rest' => 0, 'notes' => 'Cardio'],
        ]
      ];
    } elseif ($split === 'upper_lower') {
      $sessions[] = [
        'name' => 'Upper Body Entretien',
        'duration' => 50,
        'exercises' => [
          ['name' => $exercises['composes_moderés'][1] ?? 'Push-ups', 'sets' => 3, 'reps' => '15-20', 'rest' => 60, 'notes' => 'Poussée'],
          ['name' => $exercises['composes_moderés'][2] ?? 'Rowing', 'sets' => 3, 'reps' => '10-15', 'rest' => 60, 'notes' => 'Tirage'],
          ['name' => $exercises['composes_moderés'][4] ?? 'Développé épaules', 'sets' => 3, 'reps' => '10-12', 'rest' => 60, 'notes' => 'Épaules'],
          ['name' => $exercises['fonctionnel'][3] ?? 'TRX rows', 'sets' => 3, 'reps' => '12-15', 'rest' => 45, 'notes' => 'Fonctionnel'],
          ['name' => $exercises['gainage_equilibre'][0] ?? 'Planche', 'sets' => 3, 'reps' => '30-45s', 'rest' => 45, 'notes' => 'Gainage'],
        ]
      ];
      $sessions[] = [
        'name' => 'Lower Body Entretien',
        'duration' => 50,
        'exercises' => [
          ['name' => $exercises['composes_moderés'][0] ?? 'Squat goblet', 'sets' => 3, 'reps' => '12-15', 'rest' => 75, 'notes' => 'Jambes'],
          ['name' => $exercises['composes_moderés'][3] ?? 'Fentes', 'sets' => 3, 'reps' => '12-15/jambe', 'rest' => 60, 'notes' => 'Unilatéral'],
          ['name' => $exercises['fonctionnel'][0] ?? 'Kettlebell swings', 'sets' => 3, 'reps' => '15-20', 'rest' => 60, 'notes' => 'Chaîne postérieure'],
          ['name' => $exercises['gainage_equilibre'][3] ?? 'Single leg deadlift', 'sets' => 3, 'reps' => '10-12/jambe', 'rest' => 60, 'notes' => 'Équilibre'],
          ['name' => $exercises['cardio_modere'][1] ?? 'Vélo', 'sets' => 1, 'reps' => '15-20 min', 'rest' => 0, 'notes' => 'Cardio modéré'],
        ]
      ];
    } elseif ($split === 'ppl') {
      $sessions[] = [
        'name' => 'Push Entretien',
        'duration' => 45,
        'exercises' => [
          ['name' => $exercises['composes_moderés'][1] ?? 'Push-ups', 'sets' => 4, 'reps' => '15-20', 'rest' => 60, 'notes' => 'Poitrine'],
          ['name' => $exercises['composes_moderés'][4] ?? 'Développé épaules', 'sets' => 3, 'reps' => '10-12', 'rest' => 60, 'notes' => 'Épaules'],
          ['name' => 'Dips assistés', 'sets' => 3, 'reps' => '10-15', 'rest' => 60, 'notes' => 'Triceps'],
          ['name' => $exercises['gainage_equilibre'][0] ?? 'Planche', 'sets' => 3, 'reps' => '30-45s', 'rest' => 45, 'notes' => 'Core'],
        ]
      ];
      $sessions[] = [
        'name' => 'Pull Entretien',
        'duration' => 45,
        'exercises' => [
          ['name' => $exercises['composes_moderés'][2] ?? 'Rowing', 'sets' => 4, 'reps' => '10-15', 'rest' => 60, 'notes' => 'Dos'],
          ['name' => 'Tirage vertical léger', 'sets' => 3, 'reps' => '10-12', 'rest' => 60, 'notes' => 'Largeur'],
          ['name' => 'Curls modérés', 'sets' => 3, 'reps' => '12-15', 'rest' => 45, 'notes' => 'Biceps'],
          ['name' => $exercises['fonctionnel'][3] ?? 'TRX rows', 'sets' => 3, 'reps' => '12-15', 'rest' => 45, 'notes' => 'Fonctionnel'],
        ]
      ];
      $sessions[] = [
        'name' => 'Legs Entretien',
        'duration' => 45,
        'exercises' => [
          ['name' => $exercises['composes_moderés'][0] ?? 'Squat goblet', 'sets' => 3, 'reps' => '12-15', 'rest' => 75, 'notes' => 'Jambes'],
          ['name' => $exercises['composes_moderés'][3] ?? 'Fentes', 'sets' => 3, 'reps' => '12-15/jambe', 'rest' => 60, 'notes' => 'Unilatéral'],
          ['name' => $exercises['fonctionnel'][0] ?? 'Kettlebell swings', 'sets' => 3, 'reps' => '15-20', 'rest' => 60, 'notes' => 'Fonctionnel'],
          ['name' => $exercises['cardio_modere'][2] ?? 'Rameur', 'sets' => 1, 'reps' => '10-15 min', 'rest' => 0, 'notes' => 'Cardio']
        ]
      ];
    }
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
 * Banque d'exercices personnalisée selon le TYPE de programme
 */
function getExerciseBank($experience, $type, $objectif, $contraintes) {
  // ENDURANCE : Focus cardio et circuits
  if ($type === 'endurance') {
    return [
      'cardio_principal' => ['Course à pied (20-40 min)', 'Vélo elliptique (25-35 min)', 'Rameur (15-30 min)', 'Corde à sauter (10-20 min)', 'Vélo stationnaire (30-45 min)'],
      'hiit' => ['Burpees (30s work / 15s repos x8)', 'Mountain climbers (30s/15s x8)', 'Jump squats (20 reps x5)', 'Sprint intervals (30s sprint / 90s repos x6)', 'Kettlebell swings (20 reps x5)'],
      'circuit_musculaire' => ['Squat poids du corps (20-30 reps)', 'Push-ups (15-25 reps)', 'Fentes alternées (20-30 reps)', 'Rowing léger (15-20 reps)', 'Jumping jacks (30-60s)'],
      'cardio_faible_impact' => ['Marche rapide inclinée (30-45 min)', 'Natation (20-40 min)', 'Aquagym (30-45 min)', 'Vélo allongé (30-40 min)', 'Step machine (20-30 min)'],
      'gainage' => ['Planche frontale (30-90s x3)', 'Planche latérale (20-60s x3)', 'Dead bug (15 reps x3)', 'Bird dog (12 reps/côté x3)', 'Hollow body hold (20-45s x3)']
    ];
  }
  
  // RENFORCEMENT : Focus force pure et charges lourdes
  if ($type === 'renforcement' || $type === 'force') {
    return [
      'composes_principaux' => ['Squat (4-6 reps)', 'Soulevé de terre (3-5 reps)', 'Développé couché (4-6 reps)', 'Développé militaire (5-8 reps)', 'Front squat (5-8 reps)'],
      'force_jambes' => ['Back squat lourd (3-5 reps)', 'Deadlift conventionnel (3-5 reps)', 'Leg press charges lourdes (6-8 reps)', 'Bulgarian split squat lesté (6-8 reps)', 'Hip thrust lourd (6-8 reps)'],
      'force_haut_corps' => ['Bench press (4-6 reps)', 'Overhead press (5-8 reps)', 'Weighted pull-ups (4-6 reps)', 'Barbell rowing (5-8 reps)', 'Dips lestés (5-8 reps)'],
      'force_dos' => ['Deadlift (3-5 reps)', 'Weighted pull-ups (4-6 reps)', 'Barbell row lourd (5-8 reps)', 'Rack pulls (4-6 reps)', 'Farmer walks (40-60m x4)'],
      'assistance_force' => ['Pause squat (5-8 reps)', 'Close grip bench (6-8 reps)', 'Romanian deadlift (6-8 reps)', 'Floor press (6-8 reps)', 'Safety bar squat (6-8 reps)']
    ];
  }
  
  // ESTHÉTIQUE : Focus hypertrophie et volume
  if ($type === 'esthetique' || $objectif === 'prise_masse') {
    return [
      'poitrine_hypertrophie' => ['Développé couché (8-12 reps)', 'Développé incliné (8-12 reps)', 'Écartés haltères (12-15 reps)', 'Cable fly (12-15 reps)', 'Push-ups (15-20 reps)'],
      'dos_largeur' => ['Tirage vertical (8-12 reps)', 'Lat pulldown (10-12 reps)', 'Pull-ups (8-15 reps)', 'Straight arm pulldown (12-15 reps)', 'Machine row (10-12 reps)'],
      'dos_epaisseur' => ['Rowing barre (8-12 reps)', 'T-bar row (10-12 reps)', 'Seal row (10-12 reps)', 'Dumbbell row (10-12 reps)', 'Cable row (12-15 reps)'],
      'jambes_quad' => ['Squat (8-12 reps)', 'Leg press (10-15 reps)', 'Hack squat (10-12 reps)', 'Extensions jambes (12-15 reps)', 'Fentes marchées (12-15/jambe)'],
      'jambes_ischio' => ['Romanian deadlift (10-12 reps)', 'Leg curl (12-15 reps)', 'Glute ham raise (8-12 reps)', 'Nordic curls (6-10 reps)', 'Hip thrust (12-15 reps)'],
      'epaules_hypertrophie' => ['Développé militaire (8-12 reps)', 'Élévations latérales (12-15 reps)', 'Oiseau (12-15 reps)', 'Face pull (15-20 reps)', 'Arnold press (10-12 reps)'],
      'bras_biceps' => ['Curls barre (10-12 reps)', 'Curl haltères (10-12 reps)', 'Curl marteau (12-15 reps)', 'Curl pupitre (10-12 reps)', 'Cable curl (12-15 reps)'],
      'bras_triceps' => ['Dips (8-12 reps)', 'Triceps pushdown (12-15 reps)', 'Overhead extension (10-12 reps)', 'Close grip bench (8-10 reps)', 'Kickbacks (12-15 reps)']
    ];
  }
  
  // ENTRETIEN : Équilibre force/cardio
  if ($type === 'entretien' || $type === 'forme') {
    return [
      'composes_moderés' => ['Squat goblet (10-15 reps)', 'Push-ups (12-20 reps)', 'Rowing haltère (10-15 reps)', 'Fentes (12-15/jambe)', 'Développé épaules (10-12 reps)'],
      'cardio_modere' => ['Course légère (15-25 min)', 'Vélo (20-30 min)', 'Rameur (10-20 min)', 'Marche rapide (25-35 min)', 'Natation (15-25 min)'],
      'fonctionnel' => ['Kettlebell swings (15-20 reps)', 'Farmer walks (30-50m)', 'Medicine ball slams (12-15 reps)', 'TRX rows (12-15 reps)', 'Box step-ups (12-15/jambe)'],
      'mobilite' => ['Étirements dynamiques (5-10 min)', 'Yoga flow (15-20 min)', 'Foam rolling (5-10 min)', 'Cat-cow (15 reps)', 'Hip circles (10-15/direction)'],
      'gainage_equilibre' => ['Planche (30-60s x3)', 'Side plank (20-40s/côté)', 'Superman (12-15 reps x3)', 'Single leg deadlift (10-12/jambe)', 'Pallof press (12-15 reps)']
    ];
  }
  
  // Par défaut : mix équilibré
  return [
    'poitrine' => ['Développé couché (8-12 reps)', 'Push-ups (12-20 reps)', 'Développé incliné (10-12 reps)', 'Écartés (12-15 reps)'],
    'dos' => ['Tirage vertical (10-12 reps)', 'Rowing (10-12 reps)', 'Pull-ups (6-12 reps)', 'Face pull (15-20 reps)'],
    'jambes' => ['Squat (8-12 reps)', 'Leg press (10-15 reps)', 'Leg curl (12-15 reps)', 'Extensions (12-15 reps)'],
    'epaules' => ['Développé militaire (8-12 reps)', 'Élévations latérales (12-15 reps)', 'Shrugs (12-15 reps)'],
    'cardio' => ['Course (20 min)', 'Vélo (20 min)', 'Rameur (15 min)', 'Corde à sauter (10 min)']
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
