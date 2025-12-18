<?php
    if (!isset($video)) {
        return;
    }

    $thumbUrl = '/' . ltrim($video['thumbnail'], '/');

    $watchUrl = '/watch.php?id=' . urlencode($video['id']);

    $ownerUsername = $video['owner_username'] ?? null;
    $ownerProfileImageUrl = '/images/default_profile_img.png';

    if ($ownerUsername) {
        $ownerBasePath = __DIR__ . '/../public/user_files/' . $ownerUsername;
        $ownerBaseUrl  = '/user_files/' . rawurlencode($ownerUsername ?? '');

        foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
            $path = "$ownerBasePath/profile_img.$ext";
            if (file_exists($path)) {
                $ownerProfileImageUrl = "$ownerBaseUrl/profile_img.$ext";
                break;
            }
        }
    }
?>

<a class="video-box" href="<?= htmlspecialchars($watchUrl) ?>">
    <div class="thumbnail-wrapper">
        <img class="thumbnail" src="<?= htmlspecialchars($thumbUrl) ?>" alt="Thumbnail of <?= htmlspecialchars($video['title']) ?>" loading="lazy">
        <span class="duration">
            <?= htmlspecialchars($video['duration']) ?>
        </span>
    </div>
    <div class="info">
        <div class="profile-circle">
            <img
                src="<?= htmlspecialchars($ownerProfileImageUrl) ?>"
                alt="<?= htmlspecialchars($ownerUsername) ?> profile picture"
                class="profile-image-on-video-info"
            >
        </div>
        <div class="title-wrapper">
            <span class="title">
                <?= htmlspecialchars($video['title']) ?>
            </span>
        </div>
    </div>
</a>