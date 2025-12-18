<?php

require __DIR__ . '/../../db/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user'])) {
    header('Location: /login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'] ?? null;

if (!isset($_FILES['profile-image']) || $_FILES['profile-image']['error'] !== UPLOAD_ERR_OK) {    
    header('Location: /profile.php');
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['profile-image']['tmp_name']);
finfo_close($finfo);

$ext  = null;

if ($mime === 'image/png') {
    $ext = 'png';
} elseif ($mime === 'image/jpeg' || $mime === 'image/pjpeg') {
    $ext = 'jpg';
} elseif ($mime === 'image/webp') {
    $ext = 'webp';
} else {
    header('Location: /profile.php');
    exit;
}


$uploadDir = __DIR__ . '/../user_files/' . $username;
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0775, true);
}

$targetPath = $uploadDir . "/profile_img.$ext";

foreach (['png', 'jpg', 'jpeg', 'webp'] as $oldExt) {
    $oldPath = $uploadDir . "/profile_img.$oldExt";
    if ($oldExt !== $ext && file_exists($oldPath)) {
        unlink($oldPath);
    }
}

if (!move_uploaded_file($_FILES['profile-image']['tmp_name'], $targetPath)) {
    header('Location: /profile.php');
    exit;
}

header('Location: /profile.php');
exit;

?>