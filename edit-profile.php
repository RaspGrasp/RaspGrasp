<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';
$viewer = require_auth();

$errors = [];
$username = $viewer['username'];
$bio = (string)$viewer['bio'];
$usernameChangeCount = (int)($viewer['username_change_count'] ?? 0);
$avatarPath = (string)($viewer['avatar_path'] ?? '');
$newAvatarPath = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $username = trim((string)($_POST['username'] ?? ''));
    $bio = trim((string)($_POST['bio'] ?? ''));
    $usernameChanged = $username !== $viewer['username'];
    $avatarFile = $_FILES['avatar'] ?? null;

    if (!preg_match('/^[A-Za-z0-9_]{3,32}$/', $username)) {
        $errors[] = t('err_username_format');
    }
    if (mb_strlen($bio) > 255) {
        $errors[] = t('err_bio_length');
    }
    if ($usernameChanged && $usernameChangeCount >= 3) {
        $errors[] = t('err_username_changes_used');
    }
    if ($usernameChanged) {
        $check = db()->prepare('SELECT id FROM users WHERE username = ? AND id <> ?');
        $check->execute([$username, $viewer['id']]);
        if ($check->fetch()) {
            $errors[] = t('err_username_taken');
        }
    }

    if ($avatarFile && (int)($avatarFile['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
        if ((int)$avatarFile['error'] !== UPLOAD_ERR_OK) {
            $errors[] = t('err_avatar_upload');
        } elseif ((int)$avatarFile['size'] > 2 * 1024 * 1024) {
            $errors[] = t('err_avatar_size');
        } elseif (!is_uploaded_file((string)$avatarFile['tmp_name'])) {
            $errors[] = t('err_avatar_invalid');
        } else {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = $finfo ? finfo_file($finfo, (string)$avatarFile['tmp_name']) : false;
            if ($finfo) {
                finfo_close($finfo);
            }
            $extensions = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];
            $imageInfo = @getimagesize((string)$avatarFile['tmp_name']);
            if (!isset($extensions[$mime]) || $imageInfo === false || (int)$imageInfo[0] < 1 || (int)$imageInfo[1] < 1) {
                $errors[] = t('err_avatar_type');
            } else {
                $uploadDirectory = __DIR__ . '/assets/uploads/avatars';
                if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0755, true) && !is_dir($uploadDirectory)) {
                    $errors[] = t('err_avatar_folder');
                } else {
                    $newAvatarPath = 'assets/uploads/avatars/' . bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
                    if (!move_uploaded_file((string)$avatarFile['tmp_name'], __DIR__ . '/' . $newAvatarPath)) {
                        $errors[] = t('err_avatar_save');
                        $newAvatarPath = null;
                    } else {
                        $avatarPath = $newAvatarPath;
                    }
                }
            }
        }
    }

    if (!$errors) {
        $newChangeCount = $usernameChanged ? $usernameChangeCount + 1 : $usernameChangeCount;
        $update = db()->prepare('UPDATE users SET username = ?, bio = ?, username_change_count = ?, avatar_path = ? WHERE id = ?');
        $update->execute([$username, $bio, $newChangeCount, $avatarPath, $viewer['id']]);
        if ($newAvatarPath && $viewer['avatar_path'] && str_starts_with((string)$viewer['avatar_path'], 'assets/uploads/avatars/')) {
            $oldAvatarFile = __DIR__ . '/' . ltrim((string)$viewer['avatar_path'], '/');
            if (is_file($oldAvatarFile)) {
                unlink($oldAvatarFile);
            }
        }
        flash('success', t('flash_profile_updated'));
        redirect('profile.php?user=' . urlencode($username));
    }

    if ($newAvatarPath && !$errors) {
        $newAvatarPath = null;
    }
}

$pageTitle = 'Edit profile';
require __DIR__ . '/includes/header.php';
?>
<section class="form-card post-editor">
  <span class="eyebrow"><?= e(t('your_account')) ?></span>
  <h2><?= e(t('edit_profile_headline')) ?></h2>
  <p class="form-intro"><?= e(t('edit_profile_intro')) ?></p>
  <?php foreach ($errors as $error): ?><div class="form-error"><?= e($error) ?></div><?php endforeach; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
    <label>
      <?= e(t('profile_picture_label')) ?>
      <input type="file" name="avatar" accept="image/jpeg,image/png,image/gif,image/webp">
      <small><?= e(t('profile_picture_hint')) ?></small>
    </label>
    <label>
      <?= e(t('username_label')) ?>
      <input type="text" name="username" value="<?= e($username) ?>" maxlength="32" autocomplete="username" required>
      <small><?= e(sprintf(t('username_change_plural'), max(0, 3 - $usernameChangeCount))) ?></small>
    </label>
    <label>
      <?= e(t('bio_label')) ?>
      <textarea name="bio" rows="6" maxlength="255" placeholder="<?= e(t('bio_placeholder')) ?>"><?= e($bio) ?></textarea>
      <small><?= e(t('bio_max_hint')) ?></small>
    </label>
    <div class="editor-actions">
      <a class="button button-secondary" href="<?= e(url('profile.php?user=' . urlencode($viewer['username']))) ?>"><?= e(t('cancel')) ?></a>
      <button class="button button-primary" type="submit"><?= e(t('save_profile_btn')) ?></button>
    </div>
  </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>