<?php
declare(strict_types=1);
require dirname(__DIR__) . '/inc/lib.php';
start_session();
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

$msg = '';
$err = '';
$setup = !password_is_set();

/* ---- Setup / login / logout ---- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $do = $_POST['do'] ?? '';

    if ($do === 'setup' && $setup) {
        $pw = (string)($_POST['password'] ?? '');
        if (strlen($pw) < 10) $err = 'Use at least 10 characters.';
        elseif ($pw !== ($_POST['confirm'] ?? '')) $err = 'Passwords do not match.';
        else { set_password($pw); session_regenerate_id(true); $_SESSION['admin'] = true; header('Location: index.php'); exit; }
    } elseif ($do === 'login' && !$setup) {
        if (login_locked()) $err = 'Too many attempts. Try again in a few minutes.';
        elseif (check_password((string)($_POST['password'] ?? ''))) {
            session_regenerate_id(true); $_SESSION['admin'] = true; $_SESSION['fails'] = 0; header('Location: index.php'); exit;
        } else { login_failed(); $err = 'Wrong password.'; }
    } elseif (is_admin()) {
        if (!csrf_ok()) { http_response_code(400); exit('Bad request token. Reload the page.'); }
        $content = load_content();

        if ($do === 'logout') { session_destroy(); header('Location: index.php'); exit; }

        if ($do === 'save_project') {
            $id = (string)($_POST['id'] ?? '');
            $idx = null;
            foreach ($content['projects'] as $k => $p) if ($p['id'] === $id) $idx = $k;
            $p = $idx !== null ? $content['projects'][$idx] : ['id' => new_id(), 'createdAt' => (int)(microtime(true) * 1000), 'images' => []];
            $p['title'] = trim((string)($_POST['title'] ?? ''));
            $p['label'] = trim((string)($_POST['label'] ?? ''));
            $p['description'] = trim((string)($_POST['description'] ?? ''));
            $p['link'] = safe_url($_POST['link'] ?? '');
            $p['tags'] = array_values(array_filter(array_map('trim', explode(',', (string)($_POST['tags'] ?? '')))));
            $p['published'] = !empty($_POST['published']);
            foreach ((array)($_POST['remove_img'] ?? []) as $rm) {
                if (($k = array_search($rm, $p['images'], true)) !== false) { array_splice($p['images'], $k, 1); delete_upload((string)$rm); }
            }
            if (!empty($_FILES['images']['name'][0])) {
                foreach ($_FILES['images']['name'] as $i => $_) {
                    $one = ['name' => $_FILES['images']['name'][$i], 'tmp_name' => $_FILES['images']['tmp_name'][$i], 'error' => $_FILES['images']['error'][$i], 'size' => $_FILES['images']['size'][$i]];
                    $path = save_image($one, $err);
                    if ($path) $p['images'][] = $path;
                }
            }
            if ($p['title'] === '') $err = $err ?: 'A project needs a title.';
            if (!$err) {
                if ($idx !== null) $content['projects'][$idx] = $p; else $content['projects'][] = $p;
                write_json(CONTENT_FILE, $content);
                $msg = 'Project saved.';
            }
        }

        if ($do === 'delete_project') {
            foreach ($content['projects'] as $k => $p) {
                if ($p['id'] === ($_POST['id'] ?? '')) {
                    foreach ((array)$p['images'] as $img) delete_upload((string)$img);
                    array_splice($content['projects'], $k, 1);
                    write_json(CONTENT_FILE, $content);
                    $msg = 'Project deleted.';
                    break;
                }
            }
        }

        if ($do === 'save_review') {
            $id = (string)($_POST['id'] ?? '');
            $idx = null;
            foreach ($content['reviews'] as $k => $r) if ($r['id'] === $id) $idx = $k;
            $r = $idx !== null ? $content['reviews'][$idx] : ['id' => new_id(), 'createdAt' => (int)(microtime(true) * 1000)];
            $r['name'] = trim((string)($_POST['name'] ?? ''));
            $r['role'] = trim((string)($_POST['role'] ?? ''));
            $r['service'] = trim((string)($_POST['service'] ?? ''));
            $r['date'] = trim((string)($_POST['date'] ?? ''));
            $r['text'] = trim((string)($_POST['text'] ?? ''));
            $r['rating'] = max(1, min(5, (int)($_POST['rating'] ?? 5)));
            $r['published'] = !empty($_POST['published']);
            $path = save_image($_FILES['photo'] ?? ['error' => UPLOAD_ERR_NO_FILE], $err);
            if ($path) { if (!empty($r['photo'])) delete_upload($r['photo']); $r['photo'] = $path; }
            if ($r['name'] === '' || $r['text'] === '') $err = $err ?: 'A review needs a name and text.';
            if (!$err) {
                if ($idx !== null) $content['reviews'][$idx] = $r; else $content['reviews'][] = $r;
                write_json(CONTENT_FILE, $content);
                $msg = 'Review saved.';
            }
        }

        if ($do === 'delete_review') {
            foreach ($content['reviews'] as $k => $r) {
                if ($r['id'] === ($_POST['id'] ?? '')) {
                    if (!empty($r['photo'])) delete_upload($r['photo']);
                    array_splice($content['reviews'], $k, 1);
                    write_json(CONTENT_FILE, $content);
                    $msg = 'Review deleted.';
                    break;
                }
            }
        }
    }
}

$admin = is_admin();
$content = $admin ? load_content() : ['projects' => [], 'reviews' => []];
$edit_p = null; $edit_r = null;
if ($admin && isset($_GET['project'])) foreach ($content['projects'] as $p) if ($p['id'] === $_GET['project']) $edit_p = $p;
if ($admin && isset($_GET['review'])) foreach ($content['reviews'] as $r) if ($r['id'] === $_GET['review']) $edit_r = $r;
$tok = $admin ? csrf_token() : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="robots" content="noindex,nofollow">
<title>Admin | Portfolio</title>
<link rel="stylesheet" href="admin.css">
</head>
<body>
<?php if ($setup || !$admin): ?>
  <main class="login">
    <h1><?= $setup ? 'Set your admin password' : 'Admin login' ?></h1>
    <?php if ($err): ?><p class="alert err"><?= e($err) ?></p><?php endif; ?>
    <form method="post">
      <input type="hidden" name="do" value="<?= $setup ? 'setup' : 'login' ?>">
      <label>Password<input type="password" name="password" required autocomplete="<?= $setup ? 'new-password' : 'current-password' ?>"></label>
      <?php if ($setup): ?><label>Confirm password<input type="password" name="confirm" required autocomplete="new-password"></label><?php endif; ?>
      <button class="btn">Continue</button>
    </form>
  </main>
<?php else: ?>
  <header class="bar">
    <strong>Portfolio admin</strong>
    <span><a href="../index.php" target="_blank">View site ↗</a>
    <form method="post" class="inline"><input type="hidden" name="csrf" value="<?= e($tok) ?>"><input type="hidden" name="do" value="logout"><button class="link">Log out</button></form></span>
  </header>
  <main class="dash">
    <?php if ($msg): ?><p class="alert ok"><?= e($msg) ?></p><?php endif; ?>
    <?php if ($err): ?><p class="alert err"><?= e($err) ?></p><?php endif; ?>

    <section>
      <h2>Projects</h2>
      <ul class="list">
        <?php foreach ($content['projects'] as $p): ?>
          <li>
            <span><b><?= e($p['title']) ?></b> <small><?= !empty($p['published']) ? 'Published' : 'Hidden' ?></small></span>
            <span>
              <a href="?project=<?= e($p['id']) ?>#pform">Edit</a>
              <form method="post" class="inline" onsubmit="return confirm('Delete this project?')">
                <input type="hidden" name="csrf" value="<?= e($tok) ?>"><input type="hidden" name="do" value="delete_project"><input type="hidden" name="id" value="<?= e($p['id']) ?>"><button class="link danger">Delete</button>
              </form>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>

      <form method="post" enctype="multipart/form-data" class="card" id="pform">
        <h3><?= $edit_p ? 'Edit project' : 'Add project' ?></h3>
        <input type="hidden" name="csrf" value="<?= e($tok) ?>"><input type="hidden" name="do" value="save_project"><input type="hidden" name="id" value="<?= e($edit_p['id'] ?? '') ?>">
        <label>Title<input name="title" required value="<?= e($edit_p['title'] ?? '') ?>"></label>
        <label>Short label<input name="label" value="<?= e($edit_p['label'] ?? '') ?>"></label>
        <label>Description<textarea name="description" rows="4"><?= e($edit_p['description'] ?? '') ?></textarea></label>
        <label>Tags (comma separated)<input name="tags" value="<?= e(implode(', ', $edit_p['tags'] ?? [])) ?>"></label>
        <label>Link (https://...)<input name="link" value="<?= e($edit_p['link'] ?? '') ?>"></label>
        <label>Add images<input type="file" name="images[]" accept="image/*" multiple></label>
        <?php foreach ((array)($edit_p['images'] ?? []) as $img): ?>
          <label class="check"><input type="checkbox" name="remove_img[]" value="<?= e($img) ?>"> Remove <img src="../<?= e($img) ?>" alt="" height="28"></label>
        <?php endforeach; ?>
        <label class="check"><input type="checkbox" name="published" <?= !$edit_p || !empty($edit_p['published']) ? 'checked' : '' ?>> Published</label>
        <button class="btn">Save project</button>
        <?php if ($edit_p): ?><a href="index.php">Cancel</a><?php endif; ?>
      </form>
    </section>

    <section>
      <h2>Reviews</h2>
      <ul class="list">
        <?php foreach ($content['reviews'] as $r): ?>
          <li>
            <span><b><?= e($r['name']) ?></b> <small><?= (int)$r['rating'] ?>/5 · <?= !empty($r['published']) ? 'Published' : 'Hidden' ?></small></span>
            <span>
              <a href="?review=<?= e($r['id']) ?>#rform">Edit</a>
              <form method="post" class="inline" onsubmit="return confirm('Delete this review?')">
                <input type="hidden" name="csrf" value="<?= e($tok) ?>"><input type="hidden" name="do" value="delete_review"><input type="hidden" name="id" value="<?= e($r['id']) ?>"><button class="link danger">Delete</button>
              </form>
            </span>
          </li>
        <?php endforeach; ?>
      </ul>

      <form method="post" enctype="multipart/form-data" class="card" id="rform">
        <h3><?= $edit_r ? 'Edit review' : 'Add review' ?></h3>
        <input type="hidden" name="csrf" value="<?= e($tok) ?>"><input type="hidden" name="do" value="save_review"><input type="hidden" name="id" value="<?= e($edit_r['id'] ?? '') ?>">
        <label>Name<input name="name" required value="<?= e($edit_r['name'] ?? '') ?>"></label>
        <label>Role<input name="role" value="<?= e($edit_r['role'] ?? '') ?>"></label>
        <label>Service<input name="service" value="<?= e($edit_r['service'] ?? '') ?>"></label>
        <label>Date (e.g. 2026-08)<input name="date" value="<?= e($edit_r['date'] ?? '') ?>"></label>
        <label>Rating
          <select name="rating"><?php for ($i = 5; $i >= 1; $i--): ?><option value="<?= $i ?>" <?= (int)($edit_r['rating'] ?? 5) === $i ? 'selected' : '' ?>><?= $i ?></option><?php endfor; ?></select>
        </label>
        <label>Review<textarea name="text" rows="4" required><?= e($edit_r['text'] ?? '') ?></textarea></label>
        <label>Photo<input type="file" name="photo" accept="image/*"></label>
        <label class="check"><input type="checkbox" name="published" <?= !$edit_r || !empty($edit_r['published']) ? 'checked' : '' ?>> Published</label>
        <button class="btn">Save review</button>
        <?php if ($edit_r): ?><a href="index.php">Cancel</a><?php endif; ?>
      </form>
    </section>
  </main>
<?php endif; ?>
</body>
</html>
