(function () {
  var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var loader = document.getElementById('loader');
  var count = document.getElementById('loader-count');

  function start() {
    document.body.classList.add('ready');
    reveal();
    updateWork();
  }

  // Loading intro: counts 0 -> 100 once per visit, then lifts away.
  var seen = false;
  try { seen = sessionStorage.getItem('krd-intro') === '1'; } catch (e) {}
  if (reduce || seen) {
    loader.remove();
    start();
  } else {
    var n = 0;
    var t = setInterval(function () {
      n += Math.ceil(Math.random() * 9);
      if (n >= 100) {
        n = 100;
        clearInterval(t);
        count.textContent = '100';
        setTimeout(function () {
          loader.classList.add('done');
          start();
          setTimeout(function () { loader.remove(); }, 900);
        }, 250);
        try { sessionStorage.setItem('krd-intro', '1'); } catch (e) {}
      }
      count.textContent = String(n).padStart(3, '0');
    }, 55);
  }

  function reveal() {
    var els = document.querySelectorAll('.reveal');
    if (!('IntersectionObserver' in window)) {
      els.forEach(function (el) { el.classList.add('in'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -8% 0px' });
    els.forEach(function (el) { io.observe(el); });
  }

  /* Menu overlay */
  var btn = document.getElementById('menu-btn');
  var menu = document.getElementById('menu');
  function setMenu(open) {
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    menu.hidden = !open;
    document.body.classList.toggle('menu-open', open);
  }
  btn.addEventListener('click', function () { setMenu(btn.getAttribute('aria-expanded') !== 'true'); });
  menu.addEventListener('click', function (e) { if (e.target.closest('a')) setMenu(false); });
  addEventListener('keydown', function (e) { if (e.key === 'Escape' && !menu.hidden) { setMenu(false); btn.focus(); } });

  /* Work: the item nearest the middle of the screen is highlighted and its image shows on the right. */
  var items = Array.prototype.slice.call(document.querySelectorAll('.work-item'));
  var shots = Array.prototype.slice.call(document.querySelectorAll('.work-shot'));
  var current = 0;
  function setActive(i) {
    current = i;
    items.forEach(function (el, k) { el.classList.toggle('active', k === i); });
    shots.forEach(function (el, k) { el.classList.toggle('active', k === i); });
  }
  var ticking = false;
  function updateWork() {
    ticking = false;
    if (!items.length) return;
    var mid = innerHeight * 0.45, best = 0, bestD = Infinity;
    items.forEach(function (el, k) {
      var r = el.getBoundingClientRect();
      var d = Math.abs(r.top + r.height / 2 - mid);
      if (d < bestD) { bestD = d; best = k; }
    });
    if (best !== current) setActive(best);
  }
  addEventListener('scroll', function () {
    if (!ticking) { ticking = true; requestAnimationFrame(updateWork); }
  }, { passive: true });
  addEventListener('resize', updateWork);

  // Clicking a project jumps it into focus.
  items.forEach(function (el, k) {
    el.addEventListener('click', function (e) {
      if (e.target.closest('a,button')) return;
      setActive(k);
    });
  });

  // Thumbnails swap that project's image (desktop panel and mobile inline copy).
  items.forEach(function (item, k) {
    var panel = shots[k] && shots[k].querySelector('[data-main]');
    var inline = item.querySelector('[data-inline]');
    item.querySelectorAll('.thumb').forEach(function (b) {
      b.addEventListener('click', function () {
        if (panel) panel.src = b.dataset.src;
        if (inline) inline.src = b.dataset.src;
        item.querySelectorAll('.thumb').forEach(function (x) { x.setAttribute('aria-pressed', x === b ? 'true' : 'false'); });
        setActive(k);
      });
    });
  });

  // Hero photo drifts slightly on scroll.
  var photo = document.getElementById('hero-photo');
  if (photo && !reduce) {
    addEventListener('scroll', function () {
      photo.style.setProperty('--drift', Math.min(scrollY * 0.08, 40) + 'px');
    }, { passive: true });
  }
})();
