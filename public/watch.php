
<?php
include 'partials/head.php';

$videoId = isset($_GET['id']) ? (int)$_GET['id'] : null;
$video = $videoId && isset($videos[$videoId]) ? $videos[$videoId] : null;
?>

<body class="page-watch">
    <?php include 'partials/header.php'; ?> 
    <main class="watch-layout">
        <?php if (!$video): ?>
            <section class="watch-error">
                <h1>Video not found</h1>
                <p>The video you are looking for does not exist.</p>
                <a href="index.php" class="btn">Back to home</a>
            </section>
        <?php else: ?>
            <section class="watch-player">
                <div class="video-wrapper">
                    <video id="main-video" class="video" controls preload="metadata">
                        <source src="<?= htmlspecialchars($video['sources']['original']) ?>" type="video/mp4">
                    </video>
                </div>
            </section>
        <?php endif; ?> 
    </main>
</body>
</html>
