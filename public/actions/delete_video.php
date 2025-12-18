<?php
declare(strict_types=1);

require __DIR__ . '/../../db/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']['id'])) {
    header('Location: /login.php');
    exit;
}

$userId = (int)$_SESSION['user']['id'];

$videoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$redirect = $_GET['redirect'] ?? '/profile.php';
if (!is_string($redirect) || $redirect === '') $redirect = '/profile.php';

if ($redirect[0] !== '/') $redirect = '/profile.php';

if ($videoId <= 0) {
    header('Location: ' . $redirect);
    exit;
}

$stmt = $pdo->prepare('SELECT id, user_id, path, thumbnail FROM videos WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $videoId]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video || (int)$video['user_id'] !== $userId) {
    header('Location: ' . $redirect);
    exit;
}

$del = $pdo->prepare('DELETE FROM videos WHERE id = :id AND user_id = :uid');
$del->execute([':id' => $videoId, ':uid' => $userId]);

$publicDir = realpath(__DIR__ . '/..');
if ($publicDir) {
    $paths = [];

    if (!empty($video['path']) && is_string($video['path'])) $paths[] = $video['path'];
    if (!empty($video['thumbnail']) && is_string($video['thumbnail'])) $paths[] = $video['thumbnail'];

    $foldersToDelete = [];

    foreach ($paths as $rel) {
        $rel = ltrim($rel, '/');
        $candidate = $publicDir . DIRECTORY_SEPARATOR . $rel;

        $real = realpath($candidate);

        if ($real && is_inside($real, $publicDir) && is_file($real)) {
            @unlink($real);

            $parent = realpath(dirname($real));
            if ($parent) $foldersToDelete[] = $parent;
        }
    }

    $userFilesRoot = realpath($publicDir . DIRECTORY_SEPARATOR . 'user_files');
    if ($userFilesRoot) {
        $foldersToDelete = array_unique($foldersToDelete);

        usort($foldersToDelete, fn($a, $b) => strlen($b) <=> strlen($a));

        foreach ($foldersToDelete as $folder) {
            $folderReal = realpath($folder);
            if ($folderReal && is_inside($folderReal, $userFilesRoot)) {
                rrmdir($folderReal);
            }
        }
    }
}

header('Location: ' . $redirect);
exit;


function rrmdir(string $dir): void {
    if (!is_dir($dir)) return;

    $items = scandir($dir);
    if ($items === false) return;

    foreach ($items as $item) {
        if ($item === '.' || $item === '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $item;

        if (is_dir($path)) rrmdir($path);
        else @unlink($path);
    }

    @rmdir($dir);
}

function is_inside(string $path, string $base): bool {
    $base = rtrim($base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    return str_starts_with($path, $base);
}