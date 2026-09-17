<?php

declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$viewer = require_auth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

verify_csrf();

if (!can_manage_spread($viewer)) {
    http_response_code(403);
    exit('You do not have permission to do that.');
}

$postId = (int)($_POST['post_id'] ?? 0);
$result = create_spread($viewer, $postId);

if ($result['ok']) {
    flash('success', 'Topic spread started. It will show as the site banner for 24 hours.');
} else {
    flash('notice', $result['error']);
}

redirect('post.php?id=' . $postId);