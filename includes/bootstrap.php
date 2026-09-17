<?php

declare(strict_types=1);

$configPath = __DIR__ . '/../config.php';
if (!is_file($configPath)) {
    http_response_code(500);
    exit('RaspGrasp is not configured yet. Copy config.example.php to config.php and add your MySQL settings.');
}

$config = require $configPath;
$basePath = rtrim((string)($config['base_path'] ?? ''), '/');

ini_set('session.use_strict_mode', '1');
session_name('raspgrasp_session');
session_set_cookie_params([
    'httponly' => true,
    'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    'samesite' => 'Lax',
]);
session_start();

require_once __DIR__ . '/lang.php';
$lang = resolve_lang();

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
header("Content-Security-Policy: default-src 'self'; img-src 'self' data:; style-src 'self' 'unsafe-inline'; script-src 'self'; base-uri 'self'; form-action 'self'; frame-ancestors 'self'; object-src 'none'");

$dbConfig = $config['db'];
$dsn = sprintf(
    'mysql:host=%s;dbname=%s;charset=%s',
    $dbConfig['host'],
    $dbConfig['name'],
    $dbConfig['charset'] ?? 'utf8mb4'
);

try {
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $exception) {
    http_response_code(500);
    exit('RaspGrasp could not connect to MySQL. Check config.php and confirm that the database exists.');
}

function db(): PDO
{
    global $pdo;
    return $pdo;
}

function site_name(): string
{
    global $config;
    return (string)($config['site_name'] ?? 'RaspGrasp');
}

function url(string $path = ''): string
{
    global $basePath;
    if ($path === '') {
        return $basePath . '/';
    }
    return $basePath . '/' . ltrim($path, '/');
}

function e(?string $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals((string)($_SESSION['csrf_token'] ?? ''), $token)) {
        http_response_code(419);
        exit('The form expired. Please go back and try again.');
    }
}

function captcha_challenge(): array
{
    if (
        empty($_SESSION['captcha']['question'])
        || !isset($_SESSION['captcha']['answer'], $_SESSION['captcha']['expires'])
        || (int)$_SESSION['captcha']['expires'] < time()
    ) {
        $left = random_int(2, 9);
        $right = random_int(1, 9);
        $_SESSION['captcha'] = [
            'question' => "What is {$left} + {$right}?",
            'answer' => (string)($left + $right),
            'expires' => time() + 600,
        ];
    }
    return [
        'question' => (string)$_SESSION['captcha']['question'],
        'expires' => (int)$_SESSION['captcha']['expires'],
    ];
}

function verify_captcha(string $answer): bool
{
    $challenge = $_SESSION['captcha'] ?? null;
    unset($_SESSION['captcha']);
    if (!$challenge || (int)($challenge['expires'] ?? 0) < time()) {
        return false;
    }
    return hash_equals((string)$challenge['answer'], trim($answer));
}

function rate_limit(string $action, int $maxAttempts, int $windowSeconds): void
{
    $rateKey = $action . ':' . hash('sha256', (string)($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
    $statement = db()->prepare('SELECT attempts, window_started FROM security_rate_limits WHERE rate_key = ?');
    $statement->execute([$rateKey]);
    $record = $statement->fetch();
    $now = time();

    if (!$record || strtotime((string)$record['window_started']) + $windowSeconds <= $now) {
        $reset = db()->prepare(
            'INSERT INTO security_rate_limits (rate_key, attempts, window_started) VALUES (?, 1, NOW())
             ON DUPLICATE KEY UPDATE attempts = 1, window_started = NOW()'
        );
        $reset->execute([$rateKey]);
        return;
    }

    if ((int)$record['attempts'] >= $maxAttempts) {
        header('Retry-After: ' . max(1, strtotime((string)$record['window_started']) + $windowSeconds - $now));
        http_response_code(429);
        exit('Too many attempts. Please wait and try again.');
    }

    $increment = db()->prepare('UPDATE security_rate_limits SET attempts = attempts + 1 WHERE rate_key = ?');
    $increment->execute([$rateKey]);
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function current_user(): ?array
{
    static $user;
    static $loaded = false;
    if ($loaded) {
        return $user;
    }
    $loaded = true;
    if (empty($_SESSION['user_id'])) {
        return null;
    }
    $statement = db()->prepare('SELECT id, username, role, bio, username_change_count, avatar_path, created_at FROM users WHERE id = ?');
    $statement->execute([(int)$_SESSION['user_id']]);
    $user = $statement->fetch() ?: null;
    if (!$user) {
        unset($_SESSION['user_id']);
    }
    return $user;
}

function require_auth(): array
{
    $user = current_user();
    if (!$user) {
        flash('notice', 'You need to log in before doing that.');
        redirect('login.php');
    }
    return $user;
}

function normalize_secret(string $secret): string
{
    return strtolower(trim($secret));
}

function time_ago(string $timestamp): string
{
    $seconds = max(0, time() - strtotime($timestamp));
    if ($seconds < 60) return $seconds . 's ago';
    if ($seconds < 3600) return floor($seconds / 60) . 'm ago';
    if ($seconds < 86400) return floor($seconds / 3600) . 'h ago';
    if ($seconds < 604800) return floor($seconds / 86400) . 'd ago';
    return date('M j, Y', strtotime($timestamp));
}

function text_preview(string $text, int $length = 180): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text) ?? '');
    return mb_strlen($text) > $length ? mb_substr($text, 0, $length - 1) . '…' : $text;
}


function is_owner(?array $user): bool
{
    return $user !== null && ($user['role'] ?? 'member') === 'owner';
}

function is_staff(?array $user): bool
{
    return $user !== null && ($user['role'] ?? 'member') === 'staff';
}

function can_manage_spread(?array $user): bool
{
    return is_owner($user) || is_staff($user);
}

function weekly_spreads_used(int $userId): int
{
    $statement = db()->prepare(
        'SELECT COUNT(*) AS total FROM topic_spreads WHERE user_id = ? AND started_at >= NOW() - INTERVAL 7 DAY'
    );
    $statement->execute([$userId]);
    return (int)($statement->fetch()['total'] ?? 0);
}

function active_spread(): ?array
{
    $statement = db()->query(
        "SELECT ts.expires_at, p.id AS post_id, p.title, p.body, c.accent, c.name AS category_name, u.username
         FROM topic_spreads ts
         JOIN posts p ON p.id = ts.post_id
         JOIN categories c ON c.id = p.category_id
         JOIN users u ON u.id = ts.user_id
         WHERE ts.expires_at > NOW()
         ORDER BY ts.started_at DESC
         LIMIT 1"
    );
    return $statement->fetch() ?: null;
}

function excerpt(string $text, int $length = 140): string
{
    $text = trim(preg_replace('/\s+/', ' ', $text));
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '…';
}



function create_spread(array $user, int $postId): array
{
    if (!can_manage_spread($user)) {
        return ['ok' => false, 'error' => 'You do not have permission to spread topics.'];
    }
    if (!is_owner($user) && weekly_spreads_used((int)$user['id']) >= 2) {
        return ['ok' => false, 'error' => 'You have used both weekly spreads. More available next week.'];
    }
    $postCheck = db()->prepare('SELECT id FROM posts WHERE id = ?');
    $postCheck->execute([$postId]);
    if (!$postCheck->fetch()) {
        return ['ok' => false, 'error' => 'That topic no longer exists.'];
    }
    $insert = db()->prepare(
        'INSERT INTO topic_spreads (post_id, user_id, started_at, expires_at) VALUES (?, ?, NOW(), NOW() + INTERVAL 24 HOUR)'
    );
    $insert->execute([$postId, (int)$user['id']]);
    return ['ok' => true];
}

function role_badge(?string $role): string
{
    if ($role === 'owner') return '<span class="role-badge role-badge-owner">Official</span>';
    if ($role === 'staff') return '<span class="role-badge role-badge-staff">Staff</span>';
    return '';
}