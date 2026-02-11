(function setActiveNav() {
  const current = window.location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('nav a').forEach((link) => {
    const href = link.getAttribute('href');
    if (href === current) {
      link.classList.add('active');
    }
  });
})();
