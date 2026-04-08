-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : mer. 08 avr. 2026 à 09:21
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET FOREIGN_KEY_CHECKS=0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `lms`
--

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE `password_resets` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `selector` char(16) NOT NULL,
  `verifier_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `selector`, `verifier_hash`, `expires_at`, `used`, `created_at`) VALUES
(9, 1, 'a8064205fbba2179', 'ed2a4cdc551f3de6bcee2502d50e26b36f437e1c78e41ca8cd3e83e772d05d4f', '2026-02-03 10:23:44', 1, '2026-02-03 09:53:44'),
(15, 1, '29ab82f51d7dda88', 'a2dee9d2848c675e56ec0797e5ea72f88d438f3b056e793d7b179dbcec58770e', '2026-02-16 16:26:36', 1, '2026-02-16 15:56:36'),
(18, 1, 'b260e24129bf7557', 'a29e585fdfb0fbe1deae5975c9d8904fa187f9d1c632bddad69f46a1bd37773b', '2026-02-18 11:01:37', 1, '2026-02-18 10:31:37');

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `email` varchar(190) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL,
  `plan` varchar(40) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `phone`, `birthdate`, `password_hash`, `created_at`, `plan`) VALUES
(1, 'Romain', 'Sanjivy', 'sanjroro@gmail.com', '0706050403', '2006-04-21', '$2y$10$7Me54JVuN6doxXM1W6tRZuv4tuMa0mHPFXeG8fSP2RgPvawhu1HuS', '2025-11-25 09:31:23', 'duo+'),
(2, 'Samuel', 'Tardy', 'samuel.tardy@gmail.com', '0102030405', '1998-09-18', '$2y$10$NSoB/8oPeug.t6o7bJVQcuklgZFxTjdESKqNhs1oohd93PewdZ0NO', '2025-11-27 12:22:35', NULL),
(3, 'Dylan', 'Lernout', 'dylan_legoat@gmail.com', '0102030406', '2005-10-09', '$2y$10$FHUSsLQF1EwpKWoBViMFguTKBPDIAmbR3W0ngyMqP7yVqrQ1vTkvG', '2025-11-27 14:47:18', 'duo+'),
(4, 'Amory', 'Danvy', 'amory@danvy.tv', '0695813175', '2007-05-01', '$2y$10$5KGV.z04MMLvTzMq7eWd3.76bgSWHNGzEaUTzrEyIjkqkYhRRHYAS', '2025-11-27 15:36:55', 'solo'),
(5, 'Kylian', 'Allienne', 'kylian.allienne@gmail.com', '0102030406', '2006-07-22', '$2y$10$UYTTLIuIHQolweMsBbiqu.bEdos21Eka94qV4NYXnfQtqJ0sEFzca', '2025-11-28 13:50:07', 'duo+'),
(6, 'Jurys', 'TEST', 'jurys.test@gmail.com', '0102030405', '2000-01-01', '$2y$10$IPbgZ04Tjo0bNO3STj1Gt.znmKWYxN1XlBvqznKWxsAPWTRNc8Dym', '2026-04-08 08:50:08', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `user_programs`
--

DROP TABLE IF EXISTS `user_programs`;
CREATE TABLE `user_programs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `program_type` varchar(50) NOT NULL,
  `program_name` varchar(255) NOT NULL,
  `first_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `poids` decimal(5,1) DEFAULT NULL,
  `taille` int(11) DEFAULT NULL,
  `objectif` varchar(50) DEFAULT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `frequence` int(11) DEFAULT NULL,
  `jours` varchar(255) DEFAULT NULL,
  `equip` varchar(100) DEFAULT NULL,
  `contraintes` text DEFAULT NULL,
  `duree` int(11) DEFAULT NULL,
  `preferences` text DEFAULT NULL,
  `program_content` longtext DEFAULT NULL,
  `purchased_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `user_programs`
--

INSERT INTO `user_programs` (`id`, `user_id`, `program_type`, `program_name`, `first_name`, `email`, `age`, `poids`, `taille`, `objectif`, `experience`, `frequence`, `jours`, `equip`, `contraintes`, `duree`, `preferences`, `program_content`, `purchased_at`, `price`) VALUES
(4, 2, 'endurance', 'Endurance', 'Samuel', 'samuel.tardy@gmail.com', 27, 93.0, 193, 'endurance', 'avance', 2, 'Lun, Jeu', 'salle_complete', '', 60, '', '<div style=\'font-family: Arial, sans-serif; line-height: 1.6; color: #333;\'><h2 style=\'border-bottom: 2px solid #ff8000; padding-bottom: 10px; color: #111;\'>📋 Programme personnalisé - Endurance cardiovasculaire</h2><div style=\'background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0;\'><p style=\'margin: 5px 0;\'><strong>👤 Client:</strong> Samuel (27 ans)</p><p style=\'margin: 5px 0;\'><strong>🎯 Objectif:</strong> Endurance musculaire</p><p style=\'margin: 5px 0;\'><strong>💪 Niveau:</strong> Avance</p><p style=\'margin: 5px 0;\'><strong>📅 Fréquence:</strong> 2 séances/semaine</p><p style=\'margin: 5px 0;\'><strong>🏋️ Équipements:</strong> salle_complete</p></div><h3 style=\'color: #ff8000; margin-top: 25px;\'>📅 Structure d\'entraînement</h3><table style=\'width: 100%; border-collapse: collapse; margin: 15px 0;\'><thead><tr style=\'background: #ff8000; color: white;\'><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd;\'>Lun</td><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd;\'>Mar</td><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd;\'>Mer</td><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd;\'>Jeu</td><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd;\'>Ven</td><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd;\'>Sam</td><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd;\'>Dim</td></tr></thead><tbody><tr><td style=\'padding: 10px; background: #fff3e0; text-align: center; border: 1px solid #ddd;\'><strong>Full Body</strong></td><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd; color: #999;\'>Repos</td><td style=\'padding: 10px; background: #fff3e0; text-align: center; border: 1px solid #ddd;\'><strong>Full Body</strong></td><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd; color: #999;\'>Repos</td><td style=\'padding: 10px; background: #fff3e0; text-align: center; border: 1px solid #ddd;\'><strong>Full Body</strong></td><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd; color: #999;\'>Repos</td><td style=\'padding: 10px; text-align: center; border: 1px solid #ddd; color: #999;\'>Repos</td></tr></tbody></table><h3 style=\'color: #ff8000; margin-top: 25px;\'>🏃 Séances détaillées</h3><div style=\'background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ff8000;\'><h4 style=\'margin: 0 0 10px; color: #111;\'>Séance Full Body A (60min)</h4><table style=\'width: 100%; border-collapse: collapse;\'><thead><tr style=\'background: #f0f0f0; border-bottom: 2px solid #ddd;\'><th style=\'padding: 8px; text-align: left; border: 1px solid #ddd;\'>Exercice</th><th style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>Séries</th><th style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>Reps</th><th style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>Repos</th></tr></thead><tbody><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd;\'><strong>Squat / Leg Press</strong><br><small style=\'color: #666;\'>Mouvement principal</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>3</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>8-12</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>120s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd;\'><strong>Développé couché / Push-up</strong><br><small style=\'color: #666;\'>Poitrine / Triceps</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>3</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>8-12</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>90s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd;\'><strong>Tirage / Rowing</strong><br><small style=\'color: #666;\'>Dos / Biceps</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>3</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>8-12</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>90s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd;\'><strong>Développé militaire</strong><br><small style=\'color: #666;\'>Épaules</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>2</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>10-12</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>75s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd;\'><strong>Curls / Extensions</strong><br><small style=\'color: #666;\'>Isolation</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>2</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>12-15</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>60s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd;\'><strong>Cardio léger</strong><br><small style=\'color: #666;\'>Cool-down</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>1</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>5-10 min</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd;\'>0s</td></tr></tbody></table></div><h3 style=\'color: #ff8000; margin-top: 25px;\'>🍗 Nutrition et Récupération</h3><div style=\'background: #f0f7ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0078d4;\'><p><strong>Maintenance calorique:</strong> ~2790 kcal/jour</p><p><strong>Protéines:</strong> 167-204g/jour</p><p><strong>Glucides:</strong> 3-4g par kg de poids corporel</p><p><strong>Lipides:</strong> 0.8-1g par kg de poids corporel</p><p style=\'margin-top: 15px;\'><strong>💧 Hydratation:</strong> 30-35 ml d\'eau par kg de poids corporel = 2-3L/jour</p><p><strong>🥗 Timing:</strong> Petit-déj 30min après réveil • Collation avant entraînement • Post-workout immédiatement après</p></div><h3 style=\'color: #ff8000; margin-top: 25px;\'>⚡ Conseils importants</h3><ul style=\'background: #fff9e6; padding: 15px 30px; border-radius: 8px; border-left: 4px solid #ff8000;\'><li>Commencez par une semaine de familiarisation avec les mouvements</li><li>Augmentez progressivement le poids de 2-5% quand vous maîtrisez le mouvement</li><li>Repos minimal entre les séries: 180-240s pour la force, 90-150s pour l\'hypertrophie</li><li>Échauffement: 5-10 min cardio léger + étirements dynamiques</li><li>Fraîcheur musculaire: 48h minimum entre deux séances des mêmes groupes musculaires</li><li>Adaptez le programme si vous ressentirez douleur anormale</li></ul><h3 style=\'color: #ff8000; margin-top: 25px;\'>📈 Suivi de progression</h3><p style=\'background: #e6f2ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0078d4;\'>Notez vos poids et répétitions à chaque séance pour suivre votre progression. Objectif: +1 à 2 kg de charge ou +1 à 2 reps chaque semaine sur les mouvements principaux.</p></div>', '2026-01-22 10:07:19', 10.00),
(13, 1, 'endurance', 'Endurance', 'Romain', 'sanjroro@gmail.com', 20, 70.0, 170, 'endurance', 'avance', 4, '', 'home_basic', '', 45, '', '<div style=\'font-family: Arial, sans-serif; line-height: 1.6; color: #333; background: #ffffff; padding: 20px; border-radius: 8px;\'><h2 style=\'border-bottom: 2px solid #ff8000; padding-bottom: 10px; color: #ff8000;\'>📋 Programme personnalisé - Endurance cardiovasculaire</h2><div style=\'background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0; color: #333;\'><p style=\'margin: 5px 0; color: #333;\'><strong>👤 Client:</strong> Romain (20 ans)</p><p style=\'margin: 5px 0; color: #333;\'><strong>🎯 Objectif:</strong> Endurance musculaire</p><p style=\'margin: 5px 0; color: #333;\'><strong>💪 Niveau:</strong> Avance</p><p style=\'margin: 5px 0; color: #333;\'><strong>📅 Fréquence:</strong> 4 séances/semaine</p><p style=\'margin: 5px 0; color: #333;\'><strong>🏋️ Équipements:</strong> home_basic</p></div><h3 style=\'color: #ff8000; margin-top: 25px;\'>📅 Structure d\'entraînement</h3><div style=\'background: #f5f5f5; padding: 15px; border-radius: 8px; margin: 15px 0;\'><p style=\'margin: 0 0 10px; color: #333;\'><strong>💡 Structure flexible :</strong> Répartissez ces séances selon vos disponibilités, en respectant au moins 48h de repos entre deux séances du même groupe musculaire.</p><ul style=\'list-style: none; padding: 0; margin: 0;\'><li style=\'padding: 8px; margin: 5px 0; background: #fff3e0; border-left: 4px solid #ff8000; border-radius: 4px; color: #333;\'><strong>Jour 1 :</strong> Séance Cardio & Circuits 1</li><li style=\'padding: 8px; margin: 5px 0; background: #fff3e0; border-left: 4px solid #ff8000; border-radius: 4px; color: #333;\'><strong>Jour 2 :</strong> Séance HIIT Intense</li><li style=\'padding: 8px; margin: 5px 0; background: #fff3e0; border-left: 4px solid #ff8000; border-radius: 4px; color: #333;\'><strong>Jour 3 :</strong> Séance Cardio & Circuits 1</li><li style=\'padding: 8px; margin: 5px 0; background: #fff3e0; border-left: 4px solid #ff8000; border-radius: 4px; color: #333;\'><strong>Jour 4 :</strong> Séance HIIT Intense</li></ul></div><h3 style=\'color: #ff8000; margin-top: 25px;\'>🏃 Séances détaillées</h3><div style=\'background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ff8000;\'><h4 style=\'margin: 0 0 10px; color: #111;\'>Séance Cardio & Circuits 1 (45min)</h4><table style=\'width: 100%; border-collapse: collapse;\'><thead><tr style=\'background: #f0f0f0; border-bottom: 2px solid #ddd;\'><th style=\'padding: 8px; text-align: left; border: 1px solid #ddd; color: #333;\'>Exercice</th><th style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>Séries</th><th style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>Reps</th><th style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>Repos</th></tr></thead><tbody><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Échauffement cardio léger</strong><br><small style=\'color: #666;\'>Montée progressive en intensité</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>1</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>5-10 min</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>0s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Course à pied (20-40 min)</strong><br><small style=\'color: #666;\'>Zone 65-75% FCmax</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>1</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>20-30 min</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>0s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Squat poids du corps (20-30 reps)</strong><br><small style=\'color: #666;\'>Circuit léger</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>3</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>20-25</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>30s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Push-ups (15-25 reps)</strong><br><small style=\'color: #666;\'>Circuit léger</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>3</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>15-20</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>30s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Planche frontale (30-90s x3)</strong><br><small style=\'color: #666;\'>Gainage</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>3</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>30-60s</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>45s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Retour au calme cardio</strong><br><small style=\'color: #666;\'>Zone récupération</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>1</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>5-10 min</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>0s</td></tr></tbody></table></div><div style=\'background: #f9f9f9; padding: 15px; border-radius: 8px; margin: 15px 0; border-left: 4px solid #ff8000;\'><h4 style=\'margin: 0 0 10px; color: #111;\'>Séance HIIT Intense (35min)</h4><table style=\'width: 100%; border-collapse: collapse;\'><thead><tr style=\'background: #f0f0f0; border-bottom: 2px solid #ddd;\'><th style=\'padding: 8px; text-align: left; border: 1px solid #ddd; color: #333;\'>Exercice</th><th style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>Séries</th><th style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>Reps</th><th style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>Repos</th></tr></thead><tbody><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Échauffement dynamique</strong><br><small style=\'color: #666;\'>Mobilisations articulaires</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>1</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>5-8 min</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>0s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Burpees (30s work / 15s repos x8)</strong><br><small style=\'color: #666;\'>Haute intensité</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>6</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>30s work / 30s repos</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>30s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Mountain climbers (30s/15s x8)</strong><br><small style=\'color: #666;\'>Cardio explosif</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>6</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>30s work / 30s repos</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>30s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Jump squats (20 reps x5)</strong><br><small style=\'color: #666;\'>Puissance jambes</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>5</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>15-20</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>45s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Planche latérale (20-60s x3)</strong><br><small style=\'color: #666;\'>Stabilité</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>2</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>30s/côté</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>30s</td></tr><tr style=\'border-bottom: 1px solid #ddd;\'><td style=\'padding: 8px; border: 1px solid #ddd; color: #333;\'><strong>Stretching</strong><br><small style=\'color: #666;\'>Récupération</small></td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>1</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>5-10 min</td><td style=\'padding: 8px; text-align: center; border: 1px solid #ddd; color: #333;\'>0s</td></tr></tbody></table></div><h3 style=\'color: #ff8000; margin-top: 25px;\'>🍗 Nutrition et Récupération</h3><div style=\'background: #f0f7ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0078d4; color: #333;\'><p style=\'color: #333;\'><strong>Maintenance calorique:</strong> ~2100 kcal/jour</p><p style=\'color: #333;\'><strong>Protéines:</strong> 126-154g/jour</p><p style=\'color: #333;\'><strong>Glucides:</strong> 3-4g par kg de poids corporel</p><p style=\'color: #333;\'><strong>Lipides:</strong> 0.8-1g par kg de poids corporel</p><p style=\'margin-top: 15px; color: #333;\'><strong>💧 Hydratation:</strong> 30-35 ml d\'eau par kg de poids corporel = 2-2L/jour</p><p style=\'color: #333;\'><strong>🥗 Timing:</strong> Petit-déj 30min après réveil • Collation avant entraînement • Post-workout immédiatement après</p></div><h3 style=\'color: #ff8000; margin-top: 25px;\'>⚡ Conseils importants</h3><ul style=\'background: #fff9e6; padding: 15px 30px; border-radius: 8px; border-left: 4px solid #ff8000; color: #333;\'><li>Commencez par une semaine de familiarisation avec les mouvements</li><li>Augmentez progressivement le poids de 2-5% quand vous maîtrisez le mouvement</li><li>Repos minimal entre les séries: 180-240s pour la force, 90-150s pour l\'hypertrophie</li><li>Échauffement: 5-10 min cardio léger + étirements dynamiques</li><li>Fraîcheur musculaire: 48h minimum entre deux séances des mêmes groupes musculaires</li><li>Adaptez le programme si vous ressentirez douleur anormale</li></ul><h3 style=\'color: #ff8000; margin-top: 25px;\'>📈 Suivi de progression</h3><p style=\'background: #e6f2ff; padding: 15px; border-radius: 8px; border-left: 4px solid #0078d4; color: #333;\'>Notez vos poids et répétitions à chaque séance pour suivre votre progression. Objectif: +1 à 2 kg de charge ou +1 à 2 reps chaque semaine sur les mouvements principaux.</p></div>', '2026-02-16 09:03:12', 10.00);

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `selector` (`selector`),
  ADD KEY `user_id` (`user_id`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `email_2` (`email`);

--
-- Index pour la table `user_programs`
--
ALTER TABLE `user_programs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `purchased_at` (`purchased_at`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT pour la table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `user_programs`
--
ALTER TABLE `user_programs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `fk_resets_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `user_programs`
--
ALTER TABLE `user_programs`
  ADD CONSTRAINT `fk_user_programs_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;
SET FOREIGN_KEY_CHECKS=1;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
