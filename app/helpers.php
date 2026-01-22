<?php
function plan_label(?string $code): string {
  $m = ['solo'=>'SOLO','solo+'=>'SOLO +','duo'=>'DUO','duo+'=>'DUO +'];
  return $m[$code] ?? '—';
}
function is_logged(): bool { return !empty($_SESSION['user_id']); }
