<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
if (current_user()) {
    redirect('index.php');
}

$error = '';
$success = '';
$captcha = captcha_challenge();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rate_limit('password-recovery', 5, 3600);
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $secret = normalize_secret((string)($_POST['secret_word'] ?? ''));
    $newPassword = (string)($_POST['new_password'] ?? '');
    $captchaValid = verify_captcha((string)($_POST['captcha_answer'] ?? ''));
    $statement = db()->prepare('SELECT id, secret_word_hash FROM users WHERE username = ?');
    $statement->execute([$username]);
    $user = $statement->fetch();
    if (!$captchaValid) {
        $error = t('err_captcha');
    } elseif (!$user || !password_verify($secret, $user['secret_word_hash'])) {
        $error = t('err_recovery_mismatch');
    } elseif (strlen($newPassword) < 8) {
        $error = t('err_new_password_length');
    } else {
        $update = db()->prepare('UPDATE users SET password_hash = ? WHERE id = ?');
        $update->execute([password_hash($newPassword, PASSWORD_DEFAULT), $user['id']]);
        $success = t('success_password_updated');
    }
    $captcha = captcha_challenge();
}

$pageTitle = 'Recover password';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-layout">
  <section class="form-card">
    <span class="eyebrow"><?= e(t('account_recovery')) ?></span>
    <h2><?= e(t('forgot_headline')) ?></h2>
    <p class="form-intro"><?= e(t('forgot_intro')) ?></p>
    <?php if ($error): ?><div class="form-error"><?= e($error) ?></div><?php endif; ?>
    <?php if ($success): ?><div class="flash flash-success"><?= e($success) ?></div><?php endif; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <label><?= e(t('username_label')) ?><input type="text" name="username" autocomplete="username" required></label>
      <label><?= e(t('secret_word_label')) ?><input type="password" name="secret_word" autocomplete="off" required></label>
      <label><?= e(t('new_password_label')) ?><input type="password" name="new_password" minlength="8" autocomplete="new-password" required></label>
      <label><?= e(t('human_check')) ?> <span class="captcha-question"><?= e($captcha['question']) ?></span><input type="text" name="captcha_answer" inputmode="numeric" autocomplete="off" required></label>
      <button class="button button-primary button-wide" type="submit"><?= e(t('set_new_password_btn')) ?></button>
    </form>
    <p class="form-footnote"><a href="<?= e(url('login.php')) ?>"><?= e(t('return_to_login')) ?></a></p>
  </section>
  <aside class="form-aside">
    <span class="tiny-stamp"><?= e(t('no_email_required')) ?></span>
    <h3><?= e(t('old_school_recovery')) ?></h3>
    <p><?= e(t('recovery_aside_body')) ?></p>
  </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>