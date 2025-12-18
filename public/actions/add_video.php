<?php

session_start();

if (!isset($_SESSION['user']['id'])) {
    header('Location: /login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$username = $_SESSION['user']['username'];

require_once __DIR__ . '/../../db/db.php'; 

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /add_videos.php');
    exit;
}

$title = trim($_POST['title'] ?? '');
$description = trim($_POST['description'] ?? '');

if (strlen($description) > 500) {
    die("Description too long");
}

if ($title === '') {
    die('Title is required.');
}

$netPreset = strtoupper(trim($_POST['net_preset'] ?? 'MEDIUM'));
if (!in_array($netPreset, ['LOW', 'MEDIUM', 'HIGH'], true)) {
    $netPreset = 'MEDIUM';
}


function compressVideo(string $inputPath, string $outputPath, string $preset): bool
{
    $input  = escapeshellarg($inputPath);
    $output = escapeshellarg($outputPath);

    if ($preset === 'LOW') {
        $maxW = 854;
        $maxH = 480;
        $crf  = 30;
        $speed = 'veryfast';
        $ab   = '96k';
    } elseif ($preset === 'HIGH') {
        $maxW = 1920;
        $maxH = 1080;
        $crf  = 22;
        $speed = 'medium';
        $ab   = '128k';
    } else {
        $maxW = 1280;
        $maxH = 720;
        $crf  = 25;
        $speed = 'fast';
        $ab   = '128k';
    }

    $cmd = 'ffmpeg -y -i ' . $input . ' ' .
           '-vf "scale=\'min(' . $maxW . ',iw)\':\'min(' . $maxH . ',ih)\':force_original_aspect_ratio=decrease,' .
           'pad=ceil(iw/2)*2:ceil(ih/2)*2" ' .
           '-c:v libx264 -preset ' . $speed . ' -crf ' . $crf . ' ' .
           '-c:a aac -b:a ' . $ab . ' ' .
           '-movflags +faststart ' .
           $output . ' 2>&1';

    $outputLines = [];
    $returnCode  = 0;
    exec($cmd, $outputLines, $returnCode);

    file_put_contents('/tmp/ffmpeg_upload.log', "preset=$preset\n$cmd\n" . implode("\n", $outputLines) . "\n\n", FILE_APPEND);

    if ($returnCode !== 0) return false;

    return file_exists($outputPath) && filesize($outputPath) > 0;
}


$slugTitle = preg_replace('/[^a-z0-9_-]+/i', '_', $title);

$publicDir = realpath(__DIR__ . '/..');

$videoFolderRel = 'user_files/' . $username . '/' . $slugTitle . '/';
$videoDir = $publicDir . '/' . $videoFolderRel;

if (!is_dir($videoDir)) {
    mkdir($videoDir, 0775, true);
}

if (!isset($_FILES['thumbnail']) || $_FILES['thumbnail']['error'] !== UPLOAD_ERR_OK) {
    die('Error uploading thumbnail.');
}

$thumbTmp = $_FILES['thumbnail']['tmp_name'];
$thumbExt = strtolower(pathinfo($_FILES['thumbnail']['name'], PATHINFO_EXTENSION));
$thumbName = 'thumb_' . uniqid() . '.' . $thumbExt;
$thumbPath = $videoDir . $thumbName;

if (!move_uploaded_file($thumbTmp, $thumbPath)) {
    die('Failed to move thumbnail file.');
}

$thumbPathDb = $videoFolderRel . $thumbName;

if (!isset($_FILES['video']) || $_FILES['video']['error'] !== UPLOAD_ERR_OK) {
    die('Error uploading video.');
}

$videoTmp = $_FILES['video']['tmp_name'];
$videoExt = strtolower(pathinfo($_FILES['video']['name'], PATHINFO_EXTENSION));

$originalVideoName = 'original_' . uniqid() . '.' . $videoExt;
$originalVideoPath = $videoDir . $originalVideoName;

if (!move_uploaded_file($videoTmp, $originalVideoPath)) {
    die('Failed to move video file.');
}

$compressedVideoName = 'video_' . uniqid() . '.mp4';
$compressedVideoPath = $videoDir . $compressedVideoName;

$useCompressed = compressVideo($originalVideoPath, $compressedVideoPath, $netPreset);

if ($useCompressed) {
    $videoPathDb = $videoFolderRel . $compressedVideoName;
    unlink($originalVideoPath);
    $finalVideoPath = $compressedVideoPath;
} else {
    $videoPathDb = $videoFolderRel . $originalVideoName;
    $finalVideoPath = $originalVideoPath;
}

$durationSeconds = getVideoDurationSeconds($finalVideoPath);
$duration = formatDuration($durationSeconds);

$sql = "INSERT INTO videos (user_id, title, path, duration, thumbnail, description)
        VALUES (:user_id, :title, :path, :duration, :thumbnail, :description)";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    ':user_id'    => $userId,
    ':title'      => $title,
    ':path'       => $videoPathDb,
    ':duration'   => $duration,
    ':thumbnail'  => $thumbPathDb,
    ':description'=> $description,
]);

header('Location: /profile.php');
exit;

function getVideoDurationSeconds(string $filePath): ?float
{
    $file = escapeshellarg($filePath);

    $checkOut = [];
    $checkCode = 0;
    exec("command -v ffprobe 2>&1", $checkOut, $checkCode);

    $cmd = "ffprobe -v error -show_entries format=duration -of default=nw=1:nk=1 $file 2>&1";
    $out = [];
    $code = 0;
    exec($cmd, $out, $code);

    file_put_contents(
        '/tmp/ffprobe_upload.log',
        "ffprobe_in_path=" . ($checkCode === 0 ? trim($checkOut[0] ?? '') : 'NO') . "\n" .
        "filePath=$filePath\n" .
        "cmd=$cmd\n" .
        "code=$code\n" .
        "out=\n" . implode("\n", $out) . "\n\n",
        FILE_APPEND
    );

    if ($code !== 0 || empty($out)) return null;

    $dur = trim($out[0]);
    if (!is_numeric($dur)) return null;

    $seconds = (float)$dur;
    return $seconds > 0 ? $seconds : null;
}

function formatDuration(?float $seconds): string
{
    if ($seconds === null) return '00:00';

    $total = (int)round($seconds);
    $h = intdiv($total, 3600);
    $m = intdiv($total % 3600, 60);
    $s = $total % 60;

    return ($h > 0)
        ? sprintf('%02d:%02d:%02d', $h, $m, $s)
        : sprintf('%02d:%02d', $m, $s);
}

?>