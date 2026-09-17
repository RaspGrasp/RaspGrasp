<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
$viewer = require_auth();

$errors = [];
$title = '';
$body = '';
$categoryId = '';
$postCategories = db()->query('SELECT id AS category_id, slug, name, description FROM categories ORDER BY id')->fetchAll();
$captcha = captcha_challenge();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    rate_limit('create-topic', 10, 3600);
    verify_csrf();
    $title = trim((string)($_POST['title'] ?? ''));
    $body = trim((string)($_POST['body'] ?? ''));
    $categoryId = (string)($_POST['category_id'] ?? '');
    if (!verify_captcha((string)($_POST['captcha_answer'] ?? ''))) $errors[] = t('err_captcha');
    if (mb_strlen($title) < 4 || mb_strlen($title) > 140) $errors[] = t('err_title_length');
    if (mb_strlen($body) < 8) $errors[] = t('err_body_length');
    $categoryCheck = db()->prepare('SELECT id FROM categories WHERE id = ?');
    $categoryCheck->execute([(int)$categoryId]);
    if (!$categoryCheck->fetch()) $errors[] = t('err_invalid_category');
    if (!$errors) {
        $insert = db()->prepare('INSERT INTO posts (user_id, category_id, title, body) VALUES (?, ?, ?, ?)');
        $insert->execute([$viewer['id'], (int)$categoryId, $title, $body]);
        flash('success', t('flash_topic_posted'));
        redirect('post.php?id=' . (int)db()->lastInsertId());
    }
    $captcha = captcha_challenge();
}

$pageTitle = 'Start a topic';
require __DIR__ . '/includes/header.php';
?>
<section class="form-card post-editor">
  <span class="eyebrow"><?= e(t('new_thread')) ?></span>
  <h2><?= e(t('create_post_headline')) ?></h2>
  <p class="form-intro"><?= e(t('create_post_intro')) ?></p>
  <?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?>
  <form method="post">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label><?= e(t('section_label')) ?>
      <select name="category_id" required>
        <option value=""><?= e(t('choose_section')) ?></option>
        <?php foreach ($postCategories as $category): $categoryValue = (int)($category['category_id'] ?? $category['id'] ?? 0); ?>
          <option value="<?= $categoryValue ?>" <?= (string)$categoryValue === $categoryId ? 'selected' : '' ?>><?= e(category_label($lang, $category['slug'], $category['name'])) ?> — <?= e(category_description($lang, $category['slug'], $category['description'])) ?></option>
        <?php endforeach; ?>
      </select>
    </label>
    <label><?= e(t('topic_title_label')) ?><input type="text" name="title" value="<?= e($title) ?>" maxlength="140" required></label>
    <label><?= e(t('what_to_say')) ?><textarea name="body" rows="10" required><?= e($body) ?></textarea></label>
    <label><?= e(t('human_check')) ?> <span class="captcha-question"><?= e($captcha['question']) ?></span><input type="text" name="captcha_answer" inputmode="numeric" autocomplete="off" required></label>
    <div class="editor-actions"><a class="button button-secondary" href="<?= e(url('index.php')) ?>"><?= e(t('cancel')) ?></a><button class="button button-primary" type="submit"><?= e(t('post_topic_btn')) ?></button></div>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>