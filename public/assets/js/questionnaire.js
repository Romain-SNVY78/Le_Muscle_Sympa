// questionnaire.js — extrait de public/questionnaire.php
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

  // --- Soumission locale (simulation sans backend) ---
  const form = document.getElementById('form');
  const success = document.getElementById('success');
  const successType = document.getElementById('success-type');

  if (form) {
    form.addEventListener('submit', (e) => {
      e.preventDefault();

      // Validation minimale
      const required = ['prenom','email','age','poids','taille','objectif','experience','frequence','equip'];
      for (const id of required) {
        const el = document.getElementById(id);
        if (!el || !el.value) { el.reportValidity?.(); el.focus(); return; }
      }

      // Sauvegarde locale (simulation)
      const data = Object.fromEntries(new FormData(form).entries());
      const all = JSON.parse(localStorage.getItem('lms_questionnaires') || '[]');
      all.push({ date: new Date().toISOString(), ...data });
      localStorage.setItem('lms_questionnaires', JSON.stringify(all));

      // Feedback
      if (successType) successType.textContent = data.programme || 'Programme';
      if (success) success.style.display = 'block';
      const paybar = document.getElementById('paybar');
      if (paybar) paybar.style.display = 'block';
      success?.scrollIntoView({behavior:'smooth', block:'start'});
    });
  }

  // --- Paiement (simulation locale) ---
  const paylink = document.getElementById('paylink');
  const paid = document.getElementById('paid');
  if (paylink) {
    paylink.addEventListener('click', (e) => {
      e.preventDefault();
      const hidden = document.getElementById('programme');
      localStorage.setItem('lms_last_payment', JSON.stringify({
        programme: hidden ? hidden.value : '',
        amount: 10,
        at: new Date().toISOString()
      }));
      if (paid) paid.style.display = 'block';
      paid?.scrollIntoView({behavior:'smooth', block:'nearest'});
    });
  }
})();
