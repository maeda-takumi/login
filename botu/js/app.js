document.querySelectorAll('a[href^="#"]').forEach((a) => {
  a.addEventListener('click', (event) => {
    const target = document.querySelector(a.getAttribute('href'));
    if (!target) return;
    event.preventDefault();
    target.scrollIntoView({ behavior: 'smooth', block: 'start' });
  });
});
