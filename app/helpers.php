<?php
function plan_label(?string $code): string {
  $m = ['solo'=>'SOLO','solo+'=>'SOLO +','duo'=>'DUO','duo+'=>'DUO +'];
  return $m[$code] ?? '—';
}
function is_logged(): bool { return !empty($_SESSION['user_id']); }

/**
 * Génère un champ honeypot (anti-spam)
 * Les bots rempliront ce champ, les humains non
 */
function honeypot_field(): string {
  return '<input type="text" name="website" style="position:absolute;left:-9999px;width:1px;height:1px" tabindex="-1" autocomplete="off" aria-hidden="true">';
}

/**
 * Vérifie si le honeypot a été rempli (= bot détecté)
 */
function honeypot_check(): bool {
  return !empty($_POST['website']);
}

/**
 * Rate limiting simple basé sur session
 * Limite le nombre de soumissions par minute
 */
function rate_limit_check(string $key, int $max_attempts = 5, int $window_seconds = 60): bool {
  if (session_status() === PHP_SESSION_NONE) session_start();
  
  $now = time();
  $session_key = "rate_limit_{$key}";
  
  if (!isset($_SESSION[$session_key])) {
    $_SESSION[$session_key] = [];
  }
  
  // Nettoyer les anciennes tentatives
  $_SESSION[$session_key] = array_filter($_SESSION[$session_key], function($timestamp) use ($now, $window_seconds) {
    return ($now - $timestamp) < $window_seconds;
  });
  
  // Vérifier si limite atteinte
  if (count($_SESSION[$session_key]) >= $max_attempts) {
    return false; // Limite atteinte
  }
  
  // Enregistrer cette tentative
  $_SESSION[$session_key][] = $now;
  return true; // OK
}
