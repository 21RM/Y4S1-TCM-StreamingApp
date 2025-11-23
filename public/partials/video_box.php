<?php
    if (!isset($video)) {
        return; // fail-safe
    }
?>

<a class="video-box" href="watch.php?id=<?= htmlspecialchars($video['id']) ?>">
    <div class="thumbnail-wrapper">
        <img class="thumbnail" src="<?= htmlspecialchars($video['thumbnail']) ?>" alt="Thumbnail of <?= htmlspecialchars($video['title']) ?>" loading="lazy">
        <span class="duration">
            <?= htmlspecialchars($video['duration']) ?>
        </span>
    </div>
    <div class="info">
        <span class="title">
            <?= htmlspecialchars($video['title']) ?>
        </span>
    </div>
</a>