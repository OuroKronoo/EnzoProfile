<?php
declare(strict_types=1);
require __DIR__ . '/inc/lib.php';

$content  = load_content();
$projects = array_values(array_filter($content['projects'], fn($p) => !empty($p['published'])));
$reviews  = array_values(array_filter($content['reviews'], fn($r) => !empty($r['published'])));
usort($projects, fn($a, $b) => ($a['createdAt'] ?? 0) <=> ($b['createdAt'] ?? 0));

$avg = $reviews ? array_sum(array_map(fn($r) => (int)($r['rating'] ?? 0), $reviews)) / count($reviews) : 0;
$email = 'kimralfonzodiwa@gmail.com';

/** First image of a project, or ''. */
function first_img(array $p): string
{
    foreach ((array)($p['images'] ?? []) as $i) if (is_string($i) && $i !== '') return $i;
    return '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kim Ralfonzo Diwa | Freelance Web Developer</title>
<meta name="description" content="Portfolio of Kim Ralfonzo Diwa, freelance web developer: data platforms, ticketing systems, and clean responsive websites.">
<meta property="og:title" content="Kim Ralfonzo Diwa | Freelance Web Developer">
<meta property="og:image" content="assets/profile-photo.webp">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@600;700;800&family=Space+Grotesk:wght@400;500;600&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="loader" id="loader" aria-hidden="true">
  <span class="loader-word">KRD<i>.</i></span>
  <span class="loader-count" id="loader-count">000</span>
</div>

<nav class="topbar" aria-label="Main">
  <a href="#top" class="brand">KRD<span>.</span></a>
  <button class="menu-btn" id="menu-btn" type="button" aria-expanded="false" aria-controls="menu">
    <span>MENU</span><i class="bars" aria-hidden="true"></i>
  </button>
  <div class="cta-wrap">
    <a class="cta" href="mailto:<?= e($email) ?>">LET'S WORK TOGETHER</a>
    <a class="cta cta-plus" href="mailto:<?= e($email) ?>" aria-label="Email me">+</a>
  </div>
</nav>

<div class="menu" id="menu" hidden>
  <ul>
    <li><a href="#work"><small>01</small>Work</a></li>
    <?php if ($reviews): ?><li><a href="#reviews"><small>02</small>Reviews</a></li><?php endif; ?>
    <li><a href="#skills"><small>03</small>Skills</a></li>
    <li><a href="#experience"><small>04</small>Experience</a></li>
    <li><a href="#contact"><small>05</small>Contact</a></li>
  </ul>
</div>

<header class="hero" id="top">
  <p class="eyebrow reveal"><span class="dash"></span>Freelance Web Developer</p>
  <h1 class="hero-title">
    <span class="line"><span class="word">Kim Ralfonzo</span></span>
    <span class="line"><span class="word">Diwa<em>.</em></span></span>
  </h1>
  <div class="hero-foot">
    <p class="lede reveal">I design and build clean, working websites, from environmental data platforms to ticketing systems. If you can sketch it, I can ship it.</p>
    <div class="hero-photo reveal" id="hero-photo"><img src="assets/profile-photo.webp" alt="Kim Ralfonzo Diwa"></div>
    <a href="#work" class="scroll-cue reveal">Scroll <span>↓</span></a>
  </div>
</header>

<section id="work" class="block wrap work">
  <div class="work-head reveal">
    <h2>Selected work.</h2>
    <p class="mono-label">// WORK</p>
  </div>

  <div class="work-grid">
    <ol class="work-list">
      <?php foreach ($projects as $i => $p):
        $imgs = array_values(array_filter((array)($p['images'] ?? []), 'is_string'));
        $link = safe_url($p['link'] ?? '');
      ?>
      <li class="work-item<?= $i === 0 ? ' active' : '' ?>" data-i="<?= $i ?>">
        <div class="work-row">
          <span class="work-mark" aria-hidden="true"></span>
          <span class="work-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <div class="work-body">
            <h3><?= e($p['title'] ?? '') ?></h3>
            <p class="desc"><?= e($p['description'] ?? '') ?></p>
            <?php if (!empty($p['tags'])): ?>
              <div class="tags"><?php foreach ((array)$p['tags'] as $t): ?><span class="tag"><?= e((string)$t) ?></span><?php endforeach; ?></div>
            <?php endif; ?>
            <?php if ($imgs): ?>
              <div class="work-inline"><img src="<?= e($imgs[0]) ?>" alt="<?= e($p['title'] ?? '') ?> screenshot" data-inline></div>
            <?php endif; ?>
            <?php if (count($imgs) > 1): ?>
              <div class="thumbs">
                <?php foreach ($imgs as $k => $src): ?>
                  <button class="thumb" type="button" data-src="<?= e($src) ?>" aria-pressed="<?= $k === 0 ? 'true' : 'false' ?>" aria-label="Show image <?= $k + 1 ?> of <?= e($p['title'] ?? 'project') ?>"><img src="<?= e($src) ?>" alt=""></button>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
            <?php if ($link): ?><a class="proj-link" href="<?= e($link) ?>" target="_blank" rel="noopener">Visit project ↗</a><?php endif; ?>
          </div>
        </div>
      </li>
      <?php endforeach; ?>
    </ol>

    <div class="work-media" aria-hidden="true">
      <div class="work-sticky">
        <?php foreach ($projects as $i => $p): $src = first_img($p); ?>
          <figure class="work-shot<?= $i === 0 ? ' active' : '' ?>" data-i="<?= $i ?>">
            <?php if ($src): ?><img src="<?= e($src) ?>" alt="" data-main><?php endif; ?>
          </figure>
        <?php endforeach; ?>
      </div>
    </div>
  </div>
</section>

<?php if ($reviews): ?>
<section id="reviews" class="block wrap">
  <h2 class="sec-title reveal">Kind Words</h2>
  <div class="reviews">
    <aside class="rating-summary reveal">
      <p class="avg"><span class="avg-num"><?= e(number_format($avg, 1)) ?></span><span class="avg-of">/ 5</span></p>
      <p class="review-stars big" aria-label="Average rating <?= e(number_format($avg, 1)) ?> out of 5"><?= stars((int)round($avg)) ?></p>
      <p class="avg-count"><?= count($reviews) ?> review<?= count($reviews) === 1 ? '' : 's' ?></p>
    </aside>
    <div>
      <?php foreach ($reviews as $r): ?>
      <figure class="review reveal">
        <p class="review-stars" aria-label="<?= (int)$r['rating'] ?> out of 5"><?= stars((int)($r['rating'] ?? 0)) ?></p>
        <blockquote><?= e($r['text'] ?? '') ?></blockquote>
        <figcaption>
          <?php if (!empty($r['photo'])): ?>
            <img class="face" src="<?= e($r['photo']) ?>" alt="">
          <?php else: ?>
            <span class="face"><?= e(mb_strtoupper(mb_substr((string)($r['name'] ?? '?'), 0, 1))) ?></span>
          <?php endif; ?>
          <span>
            <b><?= e($r['name'] ?? '') ?></b>
            <span><?= e(trim(($r['service'] ?? '') . (!empty($r['role']) ? ' · ' . $r['role'] : '') . (!empty($r['date']) ? ' · ' . $r['date'] : ''), ' ·')) ?></span>
          </span>
        </figcaption>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<section id="skills" class="block wrap">
  <h2 class="sec-title reveal">Skills &amp; Stack</h2>
  <div class="skills-grid">
    <div class="skill-card reveal">
      <p class="kicker">DEVELOPMENT</p>
      <div class="chips"><?php foreach (['HTML5','CSS3','JavaScript','PHP','Vue.js','Node.js','MySQL','Git'] as $s): ?><span class="chip"><?= e($s) ?></span><?php endforeach; ?></div>
    </div>
    <div class="skill-card reveal">
      <p class="kicker">DESIGN</p>
      <div class="chips"><?php foreach (['Figma','UI Design','Responsive Layouts','Prototyping'] as $s): ?><span class="chip"><?= e($s) ?></span><?php endforeach; ?></div>
    </div>
  </div>
</section>

<section id="experience" class="block wrap">
  <h2 class="sec-title reveal">Experience &amp; Education</h2>
  <div>
    <div class="xp-row reveal"><p class="when">2024 to Present</p><div><h3>Freelance Web Developer</h3><p class="what">Designing and building websites and web apps for clients, from landing pages to full systems with databases and dashboards.</p></div></div>
    <div class="xp-row reveal"><p class="when">2023 to 2024</p><div><h3>Lead Developer, MAPS</h3><p class="what">Led the build of the Mangrove Analytics &amp; Prediction System, from the prediction logic to the full front end.</p></div></div>
    <div class="xp-row reveal"><p class="when">Education</p><div><h3>B.S. Information Technology</h3><p class="what">Your university and graduation year. Edit this line in index.php.</p></div></div>
  </div>
</section>

<footer id="contact">
  <p class="eyebrow2 reveal">Have a project in mind?</p>
  <h2 class="reveal">Let's build it together.</h2>
  <a class="btn btn-solid reveal" href="mailto:<?= e($email) ?>"><?= e($email) ?></a>
  <p class="fine">© <?= date('Y') ?> Kim Ralfonzo Diwa</p>
</footer>

<script src="assets/app.js"></script>
</body>
</html>
