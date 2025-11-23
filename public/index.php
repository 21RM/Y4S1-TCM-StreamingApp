<?php
$videos = [
    [
        'id' => 1,
        'title' => 'Banana',
        'duration' => '04:32',
        'thumbnail' => 'videos/thumbnail.jpeg',
        'description' => 'Basic explanation of video compression used in the platform.',
    ],
    [
        'id' => 2,
        'title' => 'Apple',
        'duration' => '14:25',
        'thumbnail' => 'videos/thumbnail2.png',
        'description' => 'Basic explanation of video compression used in the platform.',
    ],
];


///////////// HOME PAGE ///////////////

include 'partials/head.php';
?>

<body class="page-home">
    <?php include 'partials/header.php'; ?> 

    <main>
        <section class="video-grid">
            <?php foreach ($videos as $video): ?>
                <?php include __DIR__ . '/partials/video_box.php'; ?>
            <?php endforeach; ?>
        </section>
    </main>
</body>
</html>