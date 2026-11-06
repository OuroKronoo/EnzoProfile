(function () {
  var reduce = matchMedia('(prefers-reduced-motion: reduce)').matches;
  var loader = document.getElementById('loader');
  var count = document.getElementById('loader-count');

  function start() {
    document.body.classList.add('ready');
    reveal();
    onScroll();
  }

  // Loading intro: counts 0 -> 100 once per visit, then lifts away.
  var seen = false;
  try { seen = sessionStorage.getItem('krd-intro') === '1'; } catch (e) {}
  if (reduce || seen) {
    loader.remove();
    setTimeout(start, 0); // after the rest of this script has set up the sections
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

  function all(sel) { return Array.prototype.slice.call(document.querySelectorAll(sel)); }

  /* Work: the section pins to the screen; scroll progress through it picks the project. */
  var work = document.getElementById('work');
  var stage = document.querySelector('.work-stage');
  var shots = all('.work-shot');
  var details = all('.work-detail');
  var wthumbs = all('.wthumb');
  var curLabel = document.getElementById('work-cur');
  var pinMQ = matchMedia('(min-width: 901px) and (min-height: 561px)');
  var current = 0;
  var leaveTimer;

  function setWork(i) {
    if (i === current) return;
    var prev = current;
    current = i;
    if (stage) {
      stage.dataset.dir = i > prev ? 'fwd' : 'back';
      void stage.offsetWidth; // apply the start edge of the wipe before it runs
    }
    shots.forEach(function (el, k) {
      el.classList.toggle('leaving', k === prev);
      el.classList.toggle('active', k === i);
    });
    clearTimeout(leaveTimer);
    leaveTimer = setTimeout(function () {
      shots.forEach(function (el) { el.classList.remove('leaving'); });
    }, reduce ? 0 : 1000);
    details.forEach(function (el, k) { el.classList.toggle('active', k === i); });
    wthumbs.forEach(function (el, k) {
      el.classList.toggle('active', k === i);
      el.setAttribute('aria-current', k === i ? 'true' : 'false');
    });
    if (curLabel) curLabel.textContent = String(i + 1).padStart(2, '0');
  }

  function workProgress() {
    var r = work.getBoundingClientRect();
    var run = r.height - innerHeight;
    return run > 0 ? Math.min(Math.max(-r.top / run, 0), 0.9999) : 0;
  }

  function updateWork() {
    if (!work || !shots.length || !pinMQ.matches) return;
    setWork(Math.floor(workProgress() * shots.length));
  }

  // Thumbnail click scrolls to that project's step, so scrolling and clicking stay in sync.
  wthumbs.forEach(function (b, k) {
    b.addEventListener('click', function () {
      var top = work.getBoundingClientRect().top + scrollY;
      var run = work.offsetHeight - innerHeight;
      scrollTo({ top: top + run * ((k + 0.15) / shots.length), behavior: reduce ? 'auto' : 'smooth' });
    });
  });

  /* Skills: the item nearest the middle of the screen highlights and its picture shows. */
  var skillItems = all('.skill-item');
  var skillShots = all('.skill-shot');
  var skillCur = 0;
  function updateSkills() {
    if (!skillItems.length) return;
    var mid = innerHeight * 0.5, best = 0, bestD = Infinity;
    skillItems.forEach(function (el, k) {
      var r = el.querySelector('.skill-body').getBoundingClientRect();
      var d = Math.abs(r.top + r.height / 2 - mid);
      if (d < bestD) { bestD = d; best = k; }
    });
    if (best === skillCur) return;
    skillCur = best;
    skillItems.forEach(function (el, k) { el.classList.toggle('active', k === best); });
    skillShots.forEach(function (el, k) { el.classList.toggle('active', k === best); });
  }

  var ticking = false;
  function onScroll() {
    ticking = false;
    updateWork();
    updateSkills();
  }
  addEventListener('scroll', function () {
    if (!ticking) { ticking = true; requestAnimationFrame(onScroll); }
  }, { passive: true });
  addEventListener('resize', onScroll);

  // Hero photo drifts slightly on scroll.
  var photo = document.getElementById('hero-photo');
  if (photo && !reduce) {
    addEventListener('scroll', function () {
      photo.style.setProperty('--drift', Math.min(scrollY * 0.08, 40) + 'px');
    }, { passive: true });
  }
})();
