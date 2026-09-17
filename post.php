<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
$postId = (int)($_GET['id'] ?? 0);
$statement = db()->prepare("
  SELECT p.*, u.username, u.bio, u.avatar_path, u.role, c.name AS category_name, c.slug AS category_slug, c.accent
  FROM posts p
  JOIN users u ON u.id = p.user_id
  JOIN categories c ON c.id = p.category_id
  WHERE p.id = ?
");
$statement->execute([$postId]);
$post = $statement->fetch();
if (!$post) {
    http_response_code(404);
    $pageTitle = 'Topic not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty-state"><span class="empty-mark">404</span><h3>' . e(t('topic_not_here')) . '</h3><p>' . e(t('topic_removed_note')) . '</p><a class="button button-primary" href="' . e(url('index.php')) . '">' . e(t('back_to_topics')) . '</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$commentError = '';
$captcha = captcha_challenge();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $viewer = require_auth();
    rate_limit('reply', 20, 3600);
    verify_csrf();
    $body = trim((string)($_POST['body'] ?? ''));
    if (!verify_captcha((string)($_POST['captcha_answer'] ?? ''))) {
        $commentError = t('err_captcha');
    } elseif (mb_strlen($body) < 2) {
        $commentError = t('err_body_length');
    } else {
        $insert = db()->prepare('INSERT INTO comments (post_id, user_id, body) VALUES (?, ?, ?)');
        $insert->execute([$postId, $viewer['id'], $body]);
        redirect('post.php?id=' . $postId . '#comments');
    }
    $captcha = captcha_challenge();
}

$commentsStatement = db()->prepare("
  SELECT cm.body, cm.created_at, u.username, u.avatar_path
  FROM comments cm
  JOIN users u ON u.id = cm.user_id
  WHERE cm.post_id = ?
  ORDER BY cm.created_at ASC
");
$commentsStatement->execute([$postId]);
$comments = $commentsStatement->fetchAll();
$pageTitle = $post['title'];
require __DIR__ . '/includes/header.php';
?>
<div class="thread-wrap">
  <div class="thread-back"><a href="<?= e(url('index.php')) ?>"><?= e(t('back_to_latest')) ?></a><span>/</span><a href="<?= e(url('index.php?category=' . urlencode($post['category_slug']))) ?>"><?= e(category_label($lang, $post['category_slug'], $post['category_name'])) ?></a></div>
  <article class="thread-card">
    <div class="thread-heading">
      <div class="post-meta"><a class="category-tag" style="--tag-color: <?= e($post['accent']) ?>" href="<?= e(url('index.php?category=' . urlencode($post['category_slug']))) ?>"><?= e(category_label($lang, $post['category_slug'], $post['category_name'])) ?></a><span><?= e(localized_time_ago($post['created_at'], $lang)) ?></span></div>
      <h2><?= e($post['title']) ?></h2>
      <div class="author-line">
        <?php if ($post['avatar_path']): ?><img class="avatar avatar-small avatar-image" src="<?= e(url($post['avatar_path'])) ?>" alt="">
        <?php else: ?><span class="avatar avatar-small"><?= e(strtoupper(substr($post['username'], 0, 1))) ?></span><?php endif; ?>
        <?= e(t('posted_by')) ?> <a href="<?= e(url('profile.php?user=' . urlencode($post['username']))) ?>"><?= e($post['username']) ?></a> <?= role_badge($post['role'] ?? 'member') ?>
      </div>
    </div>
    <div class="thread-body"><?= nl2br(e($post['body'])) ?></div>
    <?php if (can_manage_spread($viewer ?? current_user())): $__u = $viewer ?? current_user(); ?>
      <form class="spread-form" method="post" action="<?= e(url('spread.php')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="post_id" value="<?= (int)$post['id'] ?>">
        <?php if (is_owner($__u)): ?>
          <button class="button button-secondary" type="submit"><?= e(t('spread_unlimited_btn')) ?></button>
        <?php else: $__used = weekly_spreads_used((int)$__u['id']); $__left = max(0, 2 - $__used); ?>
          <?php if ($__left > 0): ?>
            <button class="button button-secondary" type="submit"><?= e(sprintf(t('spread_left_btn'), $__left)) ?></button>
          <?php else: ?>
            <button class="button button-secondary" type="button" disabled><?= e(t('spread_used_up_btn')) ?></button>
          <?php endif; ?>
        <?php endif; ?>
      </form>
    <?php endif; ?>
  </article>

  <section class="comments-section" id="comments">
    <div class="section-heading"><h3><?= count($comments) ?> <?= count($comments) === 1 ? e(t('reply_singular')) : e(t('reply_plural')) ?></h3><span><?= e(t('public_record')) ?></span></div>
    <?php if (!$comments): ?><div class="comments-empty"><?= e(t('no_replies_yet')) ?></div><?php endif; ?>
    <div class="comment-list">
      <?php foreach ($comments as $comment): ?>
        <article class="comment">
          <div class="comment-side">
            <?php if ($comment['avatar_path']): ?><img class="avatar avatar-small avatar-image" src="<?= e(url($comment['avatar_path'])) ?>" alt="">
            <?php else: ?><span class="avatar avatar-small"><?= e(strtoupper(substr($comment['username'], 0, 1))) ?></span><?php endif; ?>
            <a href="<?= e(url('profile.php?user=' . urlencode($comment['username']))) ?>"><?= e($comment['username']) ?></a>
          </div>
          <div class="comment-content"><span class="comment-time"><?= e(localized_time_ago($comment['created_at'], $lang)) ?></span><p><?= nl2br(e($comment['body'])) ?></p></div>
        </article>
      <?php endforeach; ?>
    </div>
    <?php if (current_user()): ?>
      <form class="reply-form" method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <label><?= e(t('your_reply_label')) ?><textarea name="body" rows="4" placeholder="<?= e(t('reply_placeholder')) ?>" required></textarea></label>
        <label><?= e(t('human_check')) ?> <span class="captcha-question"><?= e($captcha['question']) ?></span><input type="text" name="captcha_answer" inputmode="numeric" autocomplete="off" required></label>
        <?php if ($commentError): ?><div class="form-error"><?= e($commentError) ?></div><?php endif; ?>
        <button class="button button-primary" type="submit"><?= e(t('reply_to_topic_btn')) ?></button>
      </form>
    <?php else: ?>
      <div class="login-callout"><a href="<?= e(url('login.php')) ?>"><?= e(t('log_in')) ?></a><?= e(t('or_word')) ?><a href="<?= e(url('register.php')) ?>"><?= e(t('create_account')) ?></a><?= e(t('to_reply_suffix')) ?></div>
    <?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>