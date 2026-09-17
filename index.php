<?php

declare(strict_types=1);

$pageTitle = 'Latest topics';
require __DIR__ . '/includes/header.php';

$selectedCategory = trim((string)($_GET['category'] ?? ''));
$params = [];
$where = '';
if ($selectedCategory !== '') {
    $where = 'WHERE c.slug = ?';
    $params[] = $selectedCategory;
}

$query = "
    SELECT p.id, p.title, p.body, p.created_at, u.username, c.name AS category_name,
           c.slug AS category_slug, c.accent, COUNT(cm.id) AS comment_count
    FROM posts p
    JOIN users u ON u.id = p.user_id
    JOIN categories c ON c.id = p.category_id
    LEFT JOIN comments cm ON cm.post_id = p.id
    {$where}
    GROUP BY p.id
    ORDER BY p.created_at DESC
    LIMIT 24
";
$statement = db()->prepare($query);
$statement->execute($params);
$posts = $statement->fetchAll();
?>
<section class="hero-strip">
  <div>
    <span class="eyebrow"><?= e(t('community_index')) ?> // <?= date('Y-m-d') ?></span>
    <h2><?= e(t('hero_headline')) ?></h2>
    <p><?= e(t('hero_sub')) ?></p>
  </div>
  <div class="hero-logo"><img src="<?= e(url('assets/raspgrasp-logo.jpg')) ?>" alt="<?= e(site_display_name($lang)) ?> logo"></div>
</section>

<div class="content-toolbar">
  <div>
    <span class="section-kicker"><?= e(t('latest_topics')) ?></span>
    <?php if ($selectedCategory !== ''): ?>
      <span class="filter-note"><?= e(t('filtered_by')) ?> <?= e(category_label($lang, $selectedCategory, $selectedCategory)) ?> · <a href="<?= e(url('index.php')) ?>"><?= e(t('show_all')) ?></a></span>
    <?php endif; ?>
  </div>
  <?php if (current_user()): ?>
    <a class="button button-primary" href="<?= e(url('create-post.php')) ?>"><?= e(t('new_topic')) ?></a>
  <?php else: ?>
    <a class="button button-secondary" href="<?= e(url('register.php')) ?>"><?= e(t('join_discussion')) ?></a>
  <?php endif; ?>
</div>

<div class="post-list">
  <?php if (!$posts): ?>
    <div class="empty-state">
      <span class="empty-mark">[ ... ]</span>
      <h3><?= e(t('empty_title')) ?></h3>
      <p><?= e(t('empty_sub')) ?></p>
      <a class="button button-primary" href="<?= e(url(current_user() ? 'create-post.php' : 'register.php')) ?>">
        <?= current_user() ? e(t('start_first')) : e(t('create_account')) ?>
      </a>
    </div>
  <?php endif; ?>

  <?php foreach ($posts as $post): ?>
    <article class="post-card">
      <div class="post-card-main">
        <div class="post-meta">
          <a class="category-tag" style="--tag-color: <?= e($post['accent']) ?>" href="<?= e(url('index.php?category=' . urlencode($post['category_slug']))) ?>"><?= e(category_label($lang, $post['category_slug'], $post['category_name'])) ?></a>
          <span><a href="<?= e(url('profile.php?user=' . urlencode($post['username']))) ?>"><?= e($post['username']) ?></a></span>
          <span><?= e(time_ago($post['created_at'])) ?></span>
        </div>
        <h3><a href="<?= e(url('post.php?id=' . (int)$post['id'])) ?>"><?= e($post['title']) ?></a></h3>
        <p><?= e(text_preview($post['body'])) ?></p>
      </div>
      <a class="comment-count" href="<?= e(url('post.php?id=' . (int)$post['id'] . '#comments')) ?>">
        <strong><?= (int)$post['comment_count'] ?></strong>
        <span><?= e(t('replies')) ?></span>
      </a>
    </article>
  <?php endforeach; ?>
</div>

<?php require __DIR__ . '/includes/footer.php'; ?>