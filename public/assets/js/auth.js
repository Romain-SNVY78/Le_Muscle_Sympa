// auth.js — extrait de public/auth.php
// Fournit la fonction globale switchTab utilisée par les handlers inline
window.switchTab = function(name){
  const paneLogin = document.getElementById('pane-login');
  const paneRegister = document.getElementById('pane-register');
  if (paneLogin) paneLogin.style.display = (name === 'login') ? 'block' : 'none';
  if (paneRegister) paneRegister.style.display = (name === 'register') ? 'block' : 'none';
};
