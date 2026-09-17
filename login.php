<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
if (current_user()) {
    redirect('index.php');
}

$error = '';
$username = '';
$captcha = captcha_challenge();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rate_limit('login', 8, 900);
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $captchaValid = verify_captcha((string)($_POST['captcha_answer'] ?? ''));
    $statement = db()->prepare('SELECT id, password_hash FROM users WHERE username = ?');
    $statement->execute([$username]);
    $user = $statement->fetch();
    if (!$captchaValid) {
        $error = t('err_captcha');
    } elseif (!$user || !password_verify($password, $user['password_hash'])) {
        $error = t('err_login');
    } else {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        flash('success', t('flash_logged_in'));
        redirect('index.php');
    }
    $captcha = captcha_challenge();
}

$pageTitle = 'Log in';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-layout">
  <section class="form-card">
    <span class="eyebrow"><?= e(t('returning_user')) ?></span>
    <h2><?= e(t('login_headline')) ?></h2>
    <p class="form-intro"><?= e(t('login_intro')) ?></p>
    <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <label><?= e(t('username_label')) ?><input type="text" name="username" value="<?= e($username) ?>" autocomplete="username" required></label>
      <label><?= e(t('password_label')) ?><input type="password" name="password" autocomplete="current-password" required></label>
      <label><?= e(t('human_check')) ?> <span class="captcha-question"><?= e($captcha['question']) ?></span><input type="text" name="captcha_answer" inputmode="numeric" autocomplete="off" required></label>
      <button class="button button-primary button-wide" type="submit"><?= e(t('login_button')) ?></button>
    </form>
    <div class="form-links"><a href="<?= e(url('forgot-password.php')) ?>"><?= e(t('forgot_password')) ?></a><a href="<?= e(url('register.php')) ?>"><?= e(t('need_account')) ?></a></div>
  </section>
  <aside class="form-aside">
    <span class="tiny-stamp"><?= e(t('login_aside_stamp')) ?></span>
    <h3><?= e(t('login_aside_headline')) ?></h3>
    <p><?= e(t('login_aside_body')) ?></p>
  </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>