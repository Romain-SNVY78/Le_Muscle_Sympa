/**
 * Mise à jour du questionnaire.js
 * Cette version stocke les données en session serveur avant le paiement
 */

(function () {
  // --- Type de programme depuis l'URL ---
  const params = new URLSearchParams(location.search);
  const type = (params.get('type') || '').toLowerCase();
  const title = document.getElementById('title');
  const hiddenProg = document.getElementById('programme');

  const map = {
    renforcement: "Renforcement musculaire",
    endurance: "Endurance",
    esthetique: "Esthétique",
    entretien: "Entretien"
  };

  if (map[type]) {
    if (title) title.textContent = "Questionnaire — " + map[type] + " (10 €)";
    if (hiddenProg) hiddenProg.value = map[type];
    document.title = map[type] + " – Questionnaire | Le Muscle Sympa";
  } else {
    if (title) title.textContent = "Votre questionnaire (10 €)";
    if (hiddenProg) hiddenProg.value = "Non précisé";
  }

  // --- Soumission locale (validation + stockage en session) ---
  const form = document.getElementById('form');
  const success = document.getElementById('success');
  const successType = document.getElementById('success-type');
  const goPay = document.getElementById('goPay');

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      // Validation minimale
      const required = ['prenom','email','age','poids','taille','objectif','experience','frequence','equip'];
      for (const id of required) {
        const el = document.getElementById(id);
        if (!el || !el.value) { el.reportValidity?.(); el.focus(); return; }
      }

      // Collecte des données
      const formData = new FormData(form);
      const data = Object.fromEntries(formData.entries());

      // Sauvegarde locale (pour historique client)
      const all = JSON.parse(localStorage.getItem('lms_questionnaires') || '[]');
      all.push({ date: new Date().toISOString(), ...data });
      localStorage.setItem('lms_questionnaires', JSON.stringify(all));

      // Stockage des données en attribut du formulaire de paiement pour envoi au serveur
      const payForm = document.getElementById('payForm');
      if (payForm) {
        // Créer un input caché pour passer les données au serveur
        const dataInput = document.createElement('input');
        dataInput.type = 'hidden';
        dataInput.name = 'program_data';
        dataInput.value = JSON.stringify(data);
        payForm.appendChild(dataInput);
      }

      // Feedback visuel
      if (successType) successType.textContent = data.programme || 'Programme';
      if (success) success.style.display = 'block';
      success?.scrollIntoView({behavior:'smooth', block:'start'});
    });
  }

  // --- Validation avant paiement ---
  if (goPay) {
    goPay.addEventListener('click', function (e) {
      const form = document.getElementById('form');
      // reportValidity() affiche les messages natifs HTML5 + renvoie true/false
      if (!form.reportValidity()) {
        e.preventDefault();
        // focus sur le premier champ invalide
        const firstInvalid = form.querySelector(':invalid');
        if (firstInvalid) firstInvalid.focus();
      }
    });
  }
})();
