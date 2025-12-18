<?php
declare(strict_types=1);

session_start();

if (!isset($_SESSION['user']['id'])) {
    header('Location: /login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /profile.php');
    exit;
}

require __DIR__ . '/../../db/db.php';

$userId = (int)$_SESSION['user']['id'];
$username = $_SESSION['user']['username'] ?? '';

$videoId = isset($_POST['video_id']) ? (int)$_POST['video_id'] : 0;

$redirect = $_POST['redirect'] ?? '/profile.php';
if (!is_string($redirect) || $redirect === '' || $redirect[0] !== '/') {
    $redirect = '/profile.php';
}

if ($videoId <= 0) {
    header('Location: ' . $redirect);
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

$errors = [];

if ($title === '') {
    $errors[] = 'Title is required.';
} elseif (strlen($title) > 50) {
    $errors[] = 'Title must be 50 characters or fewer.';
}

if (strlen($description) > 500) {
    $errors[] = 'Description must be 500 characters or fewer.';
}

$stmt = $pdo->prepare('SELECT id, user_id, thumbnail, path FROM videos WHERE id = :id AND user_id = :uid LIMIT 1');
$stmt->execute([
    ':id'  => $videoId,
    ':uid' => $userId,
]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    $_SESSION['edit_error'] = 'Video not found or access denied.';
    header('Location: /profile.php');
    exit;
}

$newThumbnailRel = null;
$thumbnailProvided = isset($_FILES['thumbnail']) && $_FILES['thumbnail']['error'] !== UPLOAD_ERR_NO_FILE;

if ($thumbnailProvided) {
    if ($_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Error uploading the thumbnail.';
    }
}

if (!empty($errors)) {
    $_SESSION['edit_error'] = implode(' ', $errors);
    $_SESSION['edit_values'] = [
        'title' => $title,
        'description' => $description,
    ];
    header('Location: /edit_video.php?id=' . $videoId . '&redirect=' . urlencode($redirect));
    exit;
}

if ($thumbnailProvided) {
    $thumbTmp = $_FILES['thumbnail']['tmp_name'];
    $thumbExt = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
    if ($thumbExt === '') {
        $thumbExt = 'png';
    }

    $publicDir = realpath(__DIR__ . '/..');
    if (!$publicDir) {
        $_SESSION['edit_error'] = 'Storage path is unavailable.';
        header('Location: /edit_video.php?id=' . $videoId . '&redirect=' . urlencode($redirect));
        exit;
    }

    $targetDirRel = '';
    if (!empty($video['thumbnail'])) {
        $targetDirRel = dirname($video['thumbnail']);
    } elseif (!empty($video['path'])) {
        $targetDirRel = dirname($video['path']);
    } elseif ($username) {
        $targetDirRel = 'user_files/' . $username;
    } else {
        $targetDirRel = 'user_files';
    }

    $targetDirRel = trim($targetDirRel, '/');
    if ($targetDirRel !== '') {
        $targetDirRel .= '/';
    }

    $targetDirAbs = $publicDir . '/' . $targetDirRel;
    if (!is_dir($targetDirAbs) && !mkdir($targetDirAbs, 0775, true)) {
        $_SESSION['edit_error'] = 'Could not create thumbnail folder.';
        $_SESSION['edit_values'] = [
            'title' => $title,
            'description' => $description,
        ];
        header('Location: /edit_video.php?id=' . $videoId . '&redirect=' . urlencode($redirect));
        exit;
    }

    $thumbName = 'thumb_' . uniqid() . '.' . $thumbExt;
    $destAbs = $targetDirAbs . $thumbName;

    if (!move_uploaded_file($thumbTmp, $destAbs)) {
        $_SESSION['edit_error'] = 'Failed to save the thumbnail.';
        $_SESSION['edit_values'] = [
            'title' => $title,
            'description' => $description,
        ];
        header('Location: /edit_video.php?id=' . $videoId . '&redirect=' . urlencode($redirect));
        exit;
    }

    $newThumbnailRel = $targetDirRel . $thumbName;

    if (!empty($video['thumbnail'])) {
        $oldThumbAbs = $publicDir . '/' . ltrim($video['thumbnail'], '/');
        if (is_file($oldThumbAbs)) {
            @unlink($oldThumbAbs);
        }
    }
}

$sql = 'UPDATE videos SET title = :title, description = :description';
$params = [
    ':title' => $title,
    ':description' => $description,
    ':id' => $videoId,
    ':uid' => $userId,
];

if ($newThumbnailRel !== null) {
    $sql .= ', thumbnail = :thumbnail';
    $params[':thumbnail'] = $newThumbnailRel;
}

$sql .= ' WHERE id = :id AND user_id = :uid';

$update = $pdo->prepare($sql);
$update->execute($params);

unset($_SESSION['edit_values'], $_SESSION['edit_error']);

header('Location: ' . $redirect);
exit;
