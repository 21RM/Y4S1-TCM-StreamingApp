<?php
require __DIR__ . '/../db/db.php';

if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$q = trim($_GET['q'] ?? '');

if ($q === '') {
  $stmt = $pdo->prepare(
    'SELECT v.id, v.title, v.path, v.duration, v.thumbnail, v.description, v.created_at,
            u.username AS owner_username
     FROM videos v
     JOIN users u ON u.id = v.user_id
     ORDER BY v.created_at DESC
     LIMIT 60'
  );
  $stmt->execute();
} else {
  $like = '%' . $q . '%';
  $stmt = $pdo->prepare(
    'SELECT v.id, v.title, v.path, v.duration, v.thumbnail, v.description, v.created_at,
            u.username AS owner_username
     FROM videos v
     JOIN users u ON u.id = v.user_id
     WHERE v.title LIKE :q
        OR v.description LIKE :q
        OR u.username LIKE :q
     ORDER BY v.created_at DESC
     LIMIT 60'
  );
  $stmt->execute([':q' => $like]);
}

$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$shownIds = array_map(fn($v) => (int)$v['id'], $videos);

$placeholders = [];
$params = [];

foreach ($shownIds as $i => $id) {
  $key = ":id$i";
  $placeholders[] = $key;
  $params[$key] = $id;
}

$where = [];
if (!empty($placeholders)) {
  $where[] = 'v.id NOT IN (' . implode(',', $placeholders) . ')';
}

$whereSql = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

$sqlOther = "
  SELECT v.id, v.title, v.path, v.duration, v.thumbnail, v.description, v.created_at,
         u.username AS owner_username
  FROM videos v
  JOIN users u ON u.id = v.user_id
  $whereSql
  ORDER BY v.created_at DESC
  LIMIT 24
";

$stmt2 = $pdo->prepare($sqlOther);
$stmt2->execute($params);
$otherVideos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

include __DIR__ . '/../partials/head.php';
include __DIR__ . '/../partials/header.php';
?>


<body class="page-library">
  <main class="library-layout">
    <h1 class="my-videos-header">
      <?= $q === '' ? 'Library' : 'Results for: ' . htmlspecialchars($q) ?>
    </h1>

    <div class="videos_grid">
      <?php if (empty($videos)): ?>
        <p class="videos-empty">No results.</p>
      <?php else: ?>
        <?php foreach ($videos as $video): ?>
          <?php include __DIR__ . '/../partials/video_box.php'; ?>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php if (!empty($otherVideos)): ?>
        <h2 class="my-videos-header" style="margin-top: 28px;">Other videos</h2>

        <div class="videos_grid">
            <?php foreach ($otherVideos as $video): ?>
            <?php include __DIR__ . '/../partials/video_box.php'; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
  </main>
</body>
</html>