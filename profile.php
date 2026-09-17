<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
$username = trim((string)($_GET['user'] ?? ''));
$statement = db()->prepare('SELECT id, username, role, bio, username_change_count, avatar_path, created_at FROM users WHERE username = ?');
$statement->execute([$username]);
$profile = $statement->fetch();
if (!$profile) {
    http_response_code(404);
    $pageTitle = 'User not found';
    require __DIR__ . '/includes/header.php';
    echo '<div class="empty-state"><span class="empty-mark">?</span><h3>' . e(t('no_user_named')) . '</h3><a class="button button-primary" href="' . e(url('index.php')) . '">' . e(t('back_to_topics')) . '</a></div>';
    require __DIR__ . '/includes/footer.php';
    exit;
}

$postStatement = db()->prepare("
  SELECT p.id, p.title, p.created_at, c.name AS category_name, c.slug AS category_slug, c.accent, COUNT(cm.id) AS comment_count
  FROM posts p JOIN categories c ON c.id = p.category_id
  LEFT JOIN comments cm ON cm.post_id = p.id
  WHERE p.user_id = ?
  GROUP BY p.id
  ORDER BY p.created_at DESC
  LIMIT 12
");
$postStatement->execute([$profile['id']]);
$profilePosts = $postStatement->fetchAll();
$pageTitle = '@' . $profile['username'];
require __DIR__ . '/includes/header.php';
?>
<section class="profile-head">
  <?php if ($profile['avatar_path']): ?>
    <img class="profile-avatar profile-avatar-image" src="<?= e(url($profile['avatar_path'])) ?>" alt="<?= e($profile['username']) ?>'s profile picture">
  <?php else: ?>
    <div class="profile-avatar"><?= e(strtoupper(substr($profile['username'], 0, 1))) ?></div>
  <?php endif; ?>
    <div><span class="eyebrow"><?= e(t('member_profile')) ?></span><h2><?= e($profile['username']) ?> <?= role_badge($profile['role'] ?? 'member') ?></h2><p><?= e(t('joined_prefix')) ?><?= e(localized_date($profile['created_at'], $lang)) ?></p></div>
  <?php if (current_user() && (int)current_user()['id'] === (int)$profile['id']): ?><a class="button button-secondary profile-action" href="<?= e(url('edit-profile.php')) ?>"><?= e(t('edit_profile')) ?></a><?php endif; ?>
</section>
<div class="profile-grid">
  <aside class="profile-note">
    <span class="tiny-stamp"><?= e(t('about_label')) ?></span>
    <p><?= $profile['bio'] ? e($profile['bio']) : e(t('no_bio_yet')) ?></p>
    <?php if ((int)$profile['username_change_count'] > 0): ?>
      <span class="profile-change-tag"><?= e(t('username_changed_prefix')) ?><?= (int)$profile['username_change_count'] ?>/3<?= e(t('username_changed_suffix')) ?></span>
    <?php endif; ?>
  </aside>
  <section><div class="section-heading"><h3><?= e(t('topics_started')) ?></h3><span><?= count($profilePosts) ?><?= e(t('shown_suffix')) ?></span></div>
    <div class="profile-posts">
      <?php if (!$profilePosts): ?><div class="comments-empty"><?= e(t('no_topics_posted')) ?></div><?php endif; ?>
      <?php foreach ($profilePosts as $post): ?>
                <a class="profile-post" href="<?= e(url('post.php?id=' . (int)$post['id'])) ?>"><span class="category-tag" style="--tag-color: <?= e($post['accent']) ?>"><?= e(category_label($lang, $post['category_slug'], $post['category_name'])) ?></span><strong><?= e($post['title']) ?></strong><small><?= e(localized_time_ago($post['created_at'], $lang)) ?> · <?= (int)$post['comment_count'] ?> <?= e(t('replies')) ?></small></a>
      <?php endforeach; ?>
    </div>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>