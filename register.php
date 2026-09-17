<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
if (current_user()) {
    redirect('index.php');
}

$errors = [];
$username = '';
$acceptedTerms = false;
$captcha = captcha_challenge();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rate_limit('register', 8, 3600);
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $password = (string)($_POST['password'] ?? '');
    $secret = normalize_secret((string)($_POST['secret_word'] ?? ''));
    $acceptedTerms = (string)($_POST['accept_terms'] ?? '') === '1';

    if (!preg_match('/^[A-Za-z0-9_]{3,32}$/', $username)) {
        $errors[] = t('err_username_format');
    }
    if (strlen($password) < 8) {
        $errors[] = t('err_password_length');
    }
    if (strlen($secret) < 3) {
        $errors[] = t('err_secret_length');
    }
    if (!$acceptedTerms) {
        $errors[] = t('err_terms_required');
    }
    if (!verify_captcha((string)($_POST['captcha_answer'] ?? ''))) {
        $errors[] = t('err_captcha');
    }
    if (!$errors) {
        $check = db()->prepare('SELECT id FROM users WHERE username = ?');
        $check->execute([$username]);
        if ($check->fetch()) {
            $errors[] = t('err_username_taken');
        } else {
            $insert = db()->prepare('INSERT INTO users (username, password_hash, secret_word_hash, terms_accepted_at) VALUES (?, ?, ?, NOW())');
            $insert->execute([$username, password_hash($password, PASSWORD_DEFAULT), password_hash($secret, PASSWORD_DEFAULT)]);
            $_SESSION['user_id'] = (int)db()->lastInsertId();
            flash('success', t('flash_account_created'));
            redirect('index.php');
        }
    }
    $captcha = captcha_challenge();
}

$pageTitle = 'Create account';
require __DIR__ . '/includes/header.php';
?>
<div class="auth-layout">
  <section class="form-card">
    <span class="eyebrow"><?= e(t('reg_eyebrow')) ?></span>
    <h2><?= e(t('reg_headline')) ?></h2>
    <p class="form-intro"><?= e(t('reg_intro')) ?></p>
    <?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?>
    <form method="post" novalidate>
      <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
      <label><?= e(t('username_label')) ?><input type="text" name="username" value="<?= e($username) ?>" maxlength="32" autocomplete="username" required></label>
      <label><?= e(t('password_label')) ?><input type="password" name="password" minlength="8" autocomplete="new-password" required><small><?= e(t('password_hint')) ?></small></label>
      <label><?= e(t('secret_word_label')) ?><input type="password" name="secret_word" minlength="3" autocomplete="off" required><small><?= e(t('secret_word_hint')) ?></small></label>
      <label><?= e(t('human_check')) ?> <span class="captcha-question"><?= e($captcha['question']) ?></span><input type="text" name="captcha_answer" inputmode="numeric" autocomplete="off" required></label>
      <label class="checkbox-label"><input type="checkbox" name="accept_terms" value="1" <?= $acceptedTerms ? 'checked' : '' ?> required><?= e(t('accept_terms_prefix')) ?><a href="<?= e(url('terms.php')) ?>" target="_blank" rel="noopener"><?= e(t('terms_of_use')) ?></a><?= e(t('accept_terms_suffix')) ?></label>
      <button class="button button-primary button-wide" type="submit"><?= e(t('create_account_btn')) ?></button>
    </form>
    <p class="form-footnote"><?= e(t('already_registered')) ?><a href="<?= e(url('login.php')) ?>"><?= e(t('login_here')) ?></a></p>
  </section>
  <aside class="form-aside">
    <span class="tiny-stamp"><?= e(t('reg_aside_stamp')) ?></span>
    <h3><?= e(t('reg_aside_headline')) ?></h3>
    <p><?= e(t('reg_aside_body')) ?></p>
  </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>