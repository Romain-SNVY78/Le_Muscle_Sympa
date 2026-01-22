// header.js — extrait de views/partials/header.php
(function () {
  const header = document.querySelector('.site-header');
  if (!header) return;

  let last = window.scrollY;
  let ticking = false;

  function onScroll() {
    const y = window.scrollY;
    const delta = y - last;

    // Affiner un peu pour éviter les micro-soucis sur petit delta
    if (y > 80 && delta > 6) {
      // on descend : cacher
      header.classList.add('is-hidden');
    } else if (delta < -6) {
      // on remonte : montrer
      header.classList.remove('is-hidden');
    }
    last = y;
    ticking = false;
  }

  window.addEventListener('scroll', () => {
    if (!ticking) {
      window.requestAnimationFrame(onScroll);
      ticking = true;
    }
  }, { passive: true });
})();

(function () {
  const btn = document.getElementById('toTop');
  if (!btn) return;

  // Affiche/masque le bouton après 400px de scroll
  const toggle = () => {
    const y = window.scrollY || document.documentElement.scrollTop;
    if (y > 400) btn.classList.add('show'); else btn.classList.remove('show');
  };
  window.addEventListener('scroll', toggle, { passive: true });
  toggle();

  // Remonter en haut (respecte "prefers-reduced-motion")
  btn.addEventListener('click', (e) => {
    e.preventDefault();
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    if (reduce) window.scrollTo(0, 0);
    else window.scrollTo({ top: 0, behavior: 'smooth' });
  });
})();
