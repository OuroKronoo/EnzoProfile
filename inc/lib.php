<?php
declare(strict_types=1);

define('ROOT', dirname(__DIR__));
define('DATA_DIR', ROOT . '/data');
define('UPLOAD_DIR', ROOT . '/uploads');
define('CONTENT_FILE', DATA_DIR . '/content.json');
define('CONFIG_FILE', DATA_DIR . '/config.json');

function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function read_json(string $file, array $fallback): array
{
    if (!is_file($file)) return $fallback;
    $data = json_decode((string)file_get_contents($file), true);
    return is_array($data) ? $data : $fallback;
}

function write_json(string $file, array $data): void
{
    if (!is_dir(dirname($file))) mkdir(dirname($file), 0755, true);
    $tmp = $file . '.tmp';
    file_put_contents($tmp, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX);
    rename($tmp, $file);
}

function load_content(): array
{
    $c = read_json(CONTENT_FILE, ['projects' => [], 'reviews' => []]);
    $c['projects'] = $c['projects'] ?? [];
    $c['reviews'] = $c['reviews'] ?? [];
    return $c;
}

function new_id(): string
{
    return bin2hex(random_bytes(6));
}

/* ---------- sessions, auth, CSRF ---------- */

function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) return;
    $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $https,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('portfolio_admin');
    session_start();
}

function password_is_set(): bool
{
    $cfg = read_json(CONFIG_FILE, []);
    return !empty($cfg['password_hash']);
}

function set_password(string $password): void
{
    write_json(CONFIG_FILE, ['password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
}

function check_password(string $password): bool
{
    $cfg = read_json(CONFIG_FILE, []);
    return !empty($cfg['password_hash']) && password_verify($password, $cfg['password_hash']);
}

function is_admin(): bool
{
    start_session();
    return !empty($_SESSION['admin']);
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));
    return $_SESSION['csrf'];
}

function csrf_ok(): bool
{
    start_session();
    $sent = $_POST['csrf'] ?? '';
    return is_string($sent) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $sent);
}

/* Simple login throttle: 5 failures, then 5 minutes locked. */
function login_locked(): bool
{
    start_session();
    return ($_SESSION['fails'] ?? 0) >= 5 && (time() - ($_SESSION['fail_at'] ?? 0)) < 300;
}

function login_failed(): void
{
    start_session();
    $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
    $_SESSION['fail_at'] = time();
}

/* ---------- image upload ---------- */

/** Returns a site-relative path like "uploads/abc.webp", or null if no file / invalid. */
function save_image(array $file, ?string &$error = null): ?string
{
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) return null;
    if ($file['error'] !== UPLOAD_ERR_OK) { $error = 'Upload failed.'; return null; }
    if ($file['size'] > 5 * 1024 * 1024) { $error = 'Image is over 5 MB.'; return null; }

    $info = @getimagesize($file['tmp_name']);
    $types = [IMAGETYPE_JPEG => 'jpg', IMAGETYPE_PNG => 'png', IMAGETYPE_WEBP => 'webp', IMAGETYPE_GIF => 'gif'];
    if (!$info || !isset($types[$info[2]])) { $error = 'Only JPG, PNG, WEBP or GIF images.'; return null; }

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);
    $name = bin2hex(random_bytes(12)) . '.' . $types[$info[2]];
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . '/' . $name)) { $error = 'Could not save the image.'; return null; }
    return 'uploads/' . $name;
}

function delete_upload(string $path): void
{
    if (strpos($path, 'uploads/') !== 0) return;
    $full = UPLOAD_DIR . '/' . basename($path);
    if (is_file($full)) @unlink($full);
}

/* ---------- display helpers ---------- */

function stars(int $n): string
{
    $n = max(0, min(5, $n));
    return '<span class="on">' . str_repeat('★', $n) . '</span><span class="off">' . str_repeat('★', 5 - $n) . '</span>';
}

/** Only allow http(s) links so a saved value can never be a javascript: URL. */
function safe_url(?string $u): string
{
    $u = trim((string)$u);
    return preg_match('#^https?://#i', $u) ? $u : '';
}
