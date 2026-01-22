// a_propos.js — extrait de public/a_propos.php
document.addEventListener('DOMContentLoaded', function () {
  const y = document.getElementById('y');
  if (y) y.textContent = new Date().getFullYear();
});
