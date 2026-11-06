<?php
declare(strict_types=1);
require __DIR__ . '/inc/lib.php';

$content  = load_content();
$projects = array_values(array_filter($content['projects'], fn($p) => !empty($p['published'])));
$reviews  = array_values(array_filter($content['reviews'], fn($r) => !empty($r['published'])));
usort($projects, fn($a, $b) => ($a['createdAt'] ?? 0) <=> ($b['createdAt'] ?? 0));

$avg = $reviews ? array_sum(array_map(fn($r) => (int)($r['rating'] ?? 0), $reviews)) / count($reviews) : 0;
$email = 'kimralfonzodiwa@gmail.com';

/* Skills list. Drop a picture per item at assets/skills/01.webp, 02.webp, ... to replace the placeholder panels. */
$skills = [
    ['title' => 'Front end.',      'text' => 'Responsive interfaces that hold up on every screen, from landing pages to full single-page apps.', 'chips' => ['HTML5', 'CSS3', 'JavaScript', 'Vue.js']],
    ['title' => 'Back end & data.', 'text' => 'Databases, dashboards and the logic behind them: logins, records, reports and predictions.',       'chips' => ['PHP', 'Node.js', 'MySQL']],
    ['title' => 'Design.',          'text' => 'Layouts worked out in Figma first, so you see the site before a line of code is written.',          'chips' => ['Figma', 'UI Design', 'Prototyping', 'Responsive Layouts']],
    ['title' => 'Ship.',            'text' => 'Versioned with Git, checked on real devices, and handed over ready to run.',                        'chips' => ['Git']],
];

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
<link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
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
  <a href="#top" class="brand" aria-label="Kim Ralfonzo Diwa, back to top"><img src="assets/logo.svg" alt="" width="34" height="34"></a>
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
  <div class="hero-meta reveal">
    <p class="eyebrow"><span class="dash"></span>Freelance Web Developer</p>
    <p class="mono-label hero-index">PORTFOLIO — <?= date('Y') ?></p>
  </div>
  <div class="hero-name">
    <h1 class="hero-title">
      <span class="line"><span class="word">Kim</span></span>
      <span class="line"><span class="word">Ralfonzo</span></span>
      <span class="line"><span class="word">Diwa<em>.</em></span></span>
    </h1>
    <div class="hero-aside reveal">
      <div class="hero-photo" id="hero-photo"><img src="assets/profile-photo.webp" alt="Kim Ralfonzo Diwa"></div>
      <p class="lede">I design and build clean, working websites, from environmental data platforms to ticketing systems. If you can sketch it, I can ship it.</p>
    </div>
    <a href="#work" class="scroll-cue reveal">Scroll <span>↓</span></a>
  </div>
</header>

<section id="work" class="work" style="--n:<?= max(1, count($projects)) ?>">
  <div class="work-pin">
    <div class="work-inner">
      <aside class="work-side">
        <h2>Selected work.</h2>
        <p class="work-intro">Data platforms, internal tools and web apps, built end to end. Scroll to move through the projects.</p>
        <nav class="work-thumbs" aria-label="Projects">
          <?php foreach ($projects as $i => $p): $src = first_img($p); ?>
            <button class="wthumb<?= $i === 0 ? ' active' : '' ?>" type="button" data-i="<?= $i ?>" aria-current="<?= $i === 0 ? 'true' : 'false' ?>" aria-label="<?= e($p['title'] ?? 'Project') ?>">
              <span class="wthumb-img"><?php if ($src): ?><img src="<?= e($src) ?>" alt=""><?php endif; ?></span>
              <span class="wthumb-mark" aria-hidden="true"></span>
            </button>
          <?php endforeach; ?>
        </nav>
        <p class="work-count mono-label" aria-hidden="true"><span id="work-cur">01</span> / <?= str_pad((string)count($projects), 2, '0', STR_PAD_LEFT) ?></p>
      </aside>

      <div class="work-main">
        <div class="work-stage" aria-hidden="true">
          <?php foreach ($projects as $i => $p): $src = first_img($p); ?>
            <figure class="work-shot<?= $i === 0 ? ' active' : '' ?>" data-i="<?= $i ?>">
              <?php if ($src): ?><img src="<?= e($src) ?>" alt=""><?php endif; ?>
            </figure>
          <?php endforeach; ?>
        </div>

        <div class="work-details">
          <?php foreach ($projects as $i => $p):
            $src = first_img($p);
            $link = safe_url($p['link'] ?? '');
            $tags = array_filter(array_map('strval', (array)($p['tags'] ?? [])));
          ?>
          <article class="work-detail<?= $i === 0 ? ' active' : '' ?>" data-i="<?= $i ?>">
            <?php if ($src): ?><div class="work-inline"><img src="<?= e($src) ?>" alt="<?= e($p['title'] ?? '') ?> screenshot" loading="lazy"></div><?php endif; ?>
            <div class="wd-row">
              <h3 class="wd-title"><span><?= e($p['title'] ?? '') ?></span></h3>
              <?php if ($tags): ?><p class="wd-tags"><?= implode(' — ', array_map(fn($t) => '[' . e(mb_strtoupper($t)) . ']', $tags)) ?></p><?php endif; ?>
            </div>
            <div class="wd-row wd-sub">
              <p class="wd-desc"><?= e($p['description'] ?? '') ?></p>
              <?php if ($link): ?><a class="proj-link" href="<?= e($link) ?>" target="_blank" rel="noopener">Visit project ↗</a><?php endif; ?>
            </div>
          </article>
          <?php endforeach; ?>
        </div>
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

<section id="skills" class="skills">
  <div class="wrap">
    <div class="skills-head reveal">
      <h2>Skills &amp; stack.</h2>
      <p class="mono-label">// SKILLS</p>
    </div>
    <div class="skills-layout">
      <ol class="skill-list">
        <?php foreach ($skills as $i => $s): ?>
        <li class="skill-item<?= $i === 0 ? ' active' : '' ?>" data-i="<?= $i ?>">
          <span class="skill-mark" aria-hidden="true"></span>
          <span class="skill-num"><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <div class="skill-body">
            <h3><?= e($s['title']) ?></h3>
            <p><?= e($s['text']) ?></p>
            <p class="skill-chips"><?= e(implode(' · ', $s['chips'])) ?></p>
          </div>
        </li>
        <?php endforeach; ?>
      </ol>
      <div class="skill-media" aria-hidden="true">
        <?php foreach ($skills as $i => $s): $img = 'assets/skills/' . str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) . '.webp'; ?>
          <figure class="skill-shot<?= $i === 0 ? ' active' : '' ?>" data-i="<?= $i ?>">
            <?php if (is_file(__DIR__ . '/' . $img)): ?>
              <img src="<?= e($img) ?>" alt="">
            <?php else: ?>
              <div class="skill-ph"><span><?= str_pad((string)($i + 1), 2, '0', STR_PAD_LEFT) ?></span><b><?= e($s['title']) ?></b></div>
            <?php endif; ?>
          </figure>
        <?php endforeach; ?>
      </div>
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
