// Mobiel menu
const toggle = document.querySelector('.nav-toggle');
const nav = document.getElementById('nav');
toggle.addEventListener('click', () => {
  const open = toggle.getAttribute('aria-expanded') === 'true';
  toggle.setAttribute('aria-expanded', String(!open));
  nav.classList.toggle('open', !open);
});
nav.addEventListener('click', (e) => {
  if (e.target.closest('a')) {
    toggle.setAttribute('aria-expanded', 'false');
    nav.classList.remove('open');
  }
});

document.getElementById('year').textContent = new Date().getFullYear();

// Afdelingszoeker: het Chirojaar start in september, de afdeling hangt af
// van de leeftijd die je kind in het startjaar van het werkjaar bereikt.
const now = new Date();
const werkjaarStart = now.getMonth() >= 8 ? now.getFullYear() : now.getFullYear() - 1;
const select = document.getElementById('geboortejaar');
const result = document.getElementById('age-result');
const cardsWrap = document.getElementById('afdeling-cards');
const cards = [...cardsWrap.querySelectorAll('.card')];

select.add(new Option('Kies een jaar', ''));
for (let y = werkjaarStart - 6; y >= werkjaarStart - 17; y--) select.add(new Option(y, y));

select.addEventListener('change', () => {
  cards.forEach((c) => c.classList.remove('match'));
  if (!select.value) {
    cardsWrap.classList.remove('filtering');
    result.textContent = '';
    return;
  }
  const age = werkjaarStart - Number(select.value);
  const match = cards.find((c) => age >= Number(c.dataset.min) && age <= Number(c.dataset.max));
  cardsWrap.classList.add('filtering');
  match.classList.add('match');
  result.textContent = `→ ${match.querySelector('h3').textContent}`;
});
