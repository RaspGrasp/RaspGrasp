<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
$pageTitle = 'Terms of Use';
require __DIR__ . '/includes/header.php';
$terms = terms_text($lang);
?>
<article class="legal-card">
  <span class="eyebrow"><?= e($terms['eyebrow']) ?></span>
  <h2><?= e($terms['title']) ?></h2>
  <p class="legal-updated"><?= e($terms['updated']) ?></p>

  <?php foreach ($terms['sections'] as $section): ?>
    <h3><?= e($section['h']) ?></h3>
    <p><?= e($section['p']) ?></p>
    <?php if (!empty($section['items'])): ?>
      <ul>
        <?php foreach ($section['items'] as $item): ?>
          <li><?= e($item) ?></li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  <?php endforeach; ?>
</article>
<?php require __DIR__ . '/includes/footer.php'; ?>