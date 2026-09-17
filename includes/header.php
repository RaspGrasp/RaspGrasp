<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';
$viewer = current_user();
$categories = db()->query('SELECT slug, name, description, accent FROM categories ORDER BY id')->fetchAll();
$flashes = consume_flash();
$pageTitle = $pageTitle ?? site_name();
?>
<!doctype html>
<html lang="<?= e($lang) ?>" dir="<?= is_rtl($lang) ? 'rtl' : 'ltr' ?>">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="description" content="A small public forum for conversations that do not fit inside a 280-character box.">
    
    <?php
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    $ogImage = (stripos($ua, 'Discordbot') !== false)
        ? 'https://raspgrasp.site.je/assets/og-banner.gif'
        : 'https://raspgrasp.site.je/assets/og-banner-static.jpg';
    $ogImageType = (stripos($ua, 'Discordbot') !== false) ? 'image/gif' : 'image/jpeg';
  ?>
  <!-- Open Graph / Social Preview -->
  <meta property="og:title" content="<?= e($pageTitle) ?> · RaspGrasp">
  <meta property="og:description" content="A small public forum for conversations that do not fit inside a 280-character box.">
  <meta property="og:image" content="<?= e($ogImage) ?>">
  <meta property="og:image:type" content="<?= e($ogImageType) ?>">
  <meta property="og:image:width" content="1200">
  <meta property="og:image:height" content="630">
  <meta property="og:url" content="<?= e((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>">
  <meta property="og:type" content="website">
  <meta property="og:site_name" content="RaspGrasp">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= e($pageTitle) ?> · RaspGrasp">
  <meta name="twitter:description" content="A small public forum for conversations that do not fit inside a 280-character box.">
  <meta name="twitter:image" content="<?= e($ogImage) ?>">
    
  <title><?= e($pageTitle) ?> · <?= e(site_display_name($lang)) ?></title>
  <link rel="icon" type="image/png" sizes="64x64" href="<?= e(url('assets/favicon.png')) ?>">
  <link rel="stylesheet" href="<?= e(url('assets/style.css?v=3')) ?>">
</head>
<body>
   <?php $spread = active_spread(); ?>
  <?php if ($spread): ?>
    <a class="spread-banner" href="<?= e(url('post.php?id=' . $spread['post_id'])) ?>">
      <span class="spread-badge">
        <svg class="spread-icon" viewBox="0 0 30 24" width="30" height="24" fill="currentColor" aria-hidden="true">
          <rect x="6" y="0" width="3" height="3"/><rect x="9" y="0" width="3" height="3"/>
          <rect x="3" y="3" width="3" height="3"/><rect x="6" y="3" width="3" height="3"/><rect x="9" y="3" width="3" height="3"/><rect x="12" y="3" width="3" height="3"/>
          <rect x="0" y="6" width="3" height="3"/><rect x="3" y="6" width="3" height="3"/><rect x="6" y="6" width="3" height="3"/><rect x="9" y="6" width="3" height="3"/><rect x="12" y="6" width="3" height="3"/><rect x="15" y="6" width="3" height="3"/><rect x="21" y="6" width="3" height="3"/><rect x="24" y="6" width="3" height="3"/>
          <rect x="0" y="9" width="3" height="3"/><rect x="3" y="9" width="3" height="3"/><rect x="6" y="9" width="3" height="3"/><rect x="9" y="9" width="3" height="3"/><rect x="12" y="9" width="3" height="3"/><rect x="15" y="9" width="3" height="3"/><rect x="18" y="9" width="3" height="3"/><rect x="21" y="9" width="3" height="3"/><rect x="24" y="9" width="3" height="3"/><rect x="27" y="9" width="3" height="3"/>
          <rect x="0" y="12" width="3" height="3"/><rect x="3" y="12" width="3" height="3"/><rect x="6" y="12" width="3" height="3"/><rect x="9" y="12" width="3" height="3"/><rect x="12" y="12" width="3" height="3"/><rect x="15" y="12" width="3" height="3"/><rect x="18" y="12" width="3" height="3"/><rect x="21" y="12" width="3" height="3"/><rect x="24" y="12" width="3" height="3"/><rect x="27" y="12" width="3" height="3"/>
          <rect x="0" y="15" width="3" height="3"/><rect x="3" y="15" width="3" height="3"/><rect x="6" y="15" width="3" height="3"/><rect x="9" y="15" width="3" height="3"/><rect x="12" y="15" width="3" height="3"/><rect x="15" y="15" width="3" height="3"/><rect x="21" y="15" width="3" height="3"/><rect x="24" y="15" width="3" height="3"/>
          <rect x="3" y="18" width="3" height="3"/><rect x="6" y="18" width="3" height="3"/><rect x="9" y="18" width="3" height="3"/><rect x="12" y="18" width="3" height="3"/>
          <rect x="6" y="21" width="3" height="3"/><rect x="9" y="21" width="3" height="3"/>
        </svg>
      </span>
      <div class="spread-text">
        <span class="spread-meta">spreading now · by <?= e($spread['username']) ?> · <?= e($spread['category_name']) ?></span>
        <strong class="spread-title"><?= e($spread['title']) ?></strong>
        <span class="spread-excerpt"><?= e(excerpt($spread['body'], 140)) ?></span>
      </div>
    </a>
  <?php endif; ?>
  <div class="site-shell">
    <aside class="sidebar">
            <a class="brand" href="<?= e(url('index.php')) ?>">
        <img class="brand-logo" src="<?= e(url('assets/raspgrasp-logo.jpg')) ?>" alt="RaspGrasp logo">
        <span><strong><?= e(site_display_name($lang)) ?></strong><small><?= e(t('tagline')) ?></small></span>
      </a>
      <a class="lang-toggle" href="?lang=<?= e(next_lang($lang)) ?>" title="Switch language">
        <svg viewBox="0 0 20 20" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
          <circle cx="10" cy="10" r="8.4"/>
          <ellipse cx="10" cy="10" rx="3.6" ry="8.4"/>
          <line x1="1.6" y1="10" x2="18.4" y2="10"/>
          <path d="M3.3 5.2c1.6 1 4.1 1.6 6.7 1.6s5.1-.6 6.7-1.6"/>
          <path d="M3.3 14.8c1.6-1 4.1-1.6 6.7-1.6s5.1.6 6.7 1.6"/>
        </svg>
        <span class="lang-code"><?= strtoupper($lang) ?></span>
      </a>

      <div class="sidebar-rule"></div>
            <p class="side-label"><?= e(t('sections')) ?></p>
      <nav class="section-nav" aria-label="Forum sections">
        <?php foreach ($categories as $category): ?>
          <a href="<?= e(url('index.php?category=' . urlencode($category['slug']))) ?>">
            <span class="dot" style="background: <?= e($category['accent']) ?>"></span>
                        <?= e(category_label($lang, $category['slug'], $category['name'])) ?>
          </a>
        <?php endforeach; ?>
      </nav>

      <div class="sidebar-rule"></div>
            <p class="side-label"><?= e(t('account')) ?></p>
      <nav class="section-nav">
        <?php if ($viewer): ?>
          <a href="<?= e(url('profile.php?user=' . urlencode($viewer['username']))) ?>"><?= e(t('your_profile')) ?></a>
          <a href="<?= e(url('create-post.php')) ?>"><?= e(t('start_topic')) ?></a>
          <a href="<?= e(url('logout.php')) ?>"><?= e(t('log_out')) ?></a>
        <?php else: ?>
          <a href="<?= e(url('login.php')) ?>"><?= e(t('log_in')) ?></a>
          <a href="<?= e(url('register.php')) ?>"><?= e(t('create_account')) ?></a>
        <?php endif; ?>
      </nav>

      <div class="sidebar-note">
        <span class="tiny-stamp">NOTICE</span>
                <p><?= e(t('notice_text')) ?></p>
      </div>

      <div class="sidebar-footer">
        <span><?= e(site_display_name($lang)) ?> v1.0</span>
        <span><?= e(t('made_for_web')) ?></span>
      </div>
    </aside>

    <main class="main-content">
      <header class="topbar">
        <div>
                    <span class="breadcrumb">/ <?= e(t('home')) ?> / <bdi><?= e(strtolower(breadcrumb_title($pageTitle, $lang))) ?></bdi></span>
          <h1><?= e($pageTitle) ?></h1>
        </div>
        <?php if ($viewer): ?>
          <div class="user-chip">
            <?php if ($viewer['avatar_path']): ?>
              <img class="avatar avatar-image" src="<?= e(url($viewer['avatar_path'])) ?>" alt="">
            <?php else: ?>
              <span class="avatar"><?= e(strtoupper(substr($viewer['username'], 0, 1))) ?></span>
            <?php endif; ?>
                                    <span><?= e(t('signed_in_as')) ?> <a href="<?= e(url('profile.php?user=' . urlencode($viewer['username']))) ?>"><?= e($viewer['username']) ?></a> <?= role_badge($viewer['role'] ?? 'member') ?></span>
          </div>
        <?php endif; ?>
      </header>

      <?php foreach ($flashes as $flash): ?>
        <div class="flash flash-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
      <?php endforeach; ?>