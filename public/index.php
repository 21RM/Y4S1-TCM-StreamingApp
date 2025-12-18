<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../db/db.php';

$stmt = $pdo->query(
    'SELECT v.id,
            v.title,
            v.path,
            v.duration,
            v.thumbnail,
            v.description,
            v.created_at,
            u.username AS owner_username
     FROM videos v
     JOIN users u ON v.user_id = u.id
     ORDER BY v.created_at DESC
     LIMIT 50'
);

$videos = $stmt->fetchAll(PDO::FETCH_ASSOC);

///////////// HOME PAGE ///////////////
include '../partials/head.php';
?>

<body class="page-home">
    <?php include '../partials/header.php'; ?> 

    <main>
        <section class="video-grid">
            <?php if (empty($videos)): ?>
                <p class="no-videos-message">
                    No videos have been uploaded yet. Be the first to add one!
                </p>
            <?php else: ?>
                <?php foreach ($videos as $video): ?>
                    <?php include '../partials/video_box.php'; ?>
                <?php endforeach; ?>
            <?php endif; ?>
        </section>
    </main>
    <?php include '../partials/footer.php'; ?> 
</body>
</html>