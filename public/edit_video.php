<?php
require __DIR__ . '/../db/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user']['id'])) {
    $currentPath = $_SERVER['REQUEST_URI'] ?? '/edit_video.php';
    header('Location: /login.php?redirect=' . urlencode($currentPath));
    exit;
}

$redirect = $_GET['redirect'] ?? '/profile.php';
if (!is_string($redirect) || $redirect === '') {
    $redirect = '/profile.php';
}
if ($redirect[0] !== '/') {
    $redirect = '/profile.php';
}

$videoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($videoId <= 0) {
    header('Location: ' . $redirect);
    exit;
}

$userId = (int)$_SESSION['user']['id'];
$username = $_SESSION['user']['username'] ?? null;

$stmt = $pdo->prepare(
    'SELECT id, user_id, title, description, thumbnail, duration
     FROM videos
     WHERE id = :id AND user_id = :uid
     LIMIT 1'
);
$stmt->execute([
    ':id'  => $videoId,
    ':uid' => $userId,
]);
$video = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$video) {
    header('Location: ' . $redirect);
    exit;
}

$thumbUrl = '/images/default_thumbnail.png';
if (!empty($video['thumbnail'])) {
    $thumbUrl = '/' . ltrim($video['thumbnail'], '/');
}

$durationLabel = $video['duration'] ?: '00:00';

$profileImageUrl = '/images/default_profile_img.png';
if ($username) {
    $ownerBasePath = __DIR__ . '/user_files/' . $username;
    $ownerBaseUrl  = '/user_files/' . rawurlencode($username);

    foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
        $path = "$ownerBasePath/profile_img.$ext";
        if (file_exists($path)) {
            $profileImageUrl = "$ownerBaseUrl/profile_img.$ext";
            break;
        }
    }
}

$flashError = $_SESSION['edit_error'] ?? '';
$oldValues = $_SESSION['edit_values'] ?? [];
unset($_SESSION['edit_error'], $_SESSION['edit_values']);

$formTitle = $oldValues['title'] ?? $video['title'];
$formDescription = $oldValues['description'] ?? $video['description'];

include '../partials/head.php';
?>

<body class="page-add-video">
    <main class="add-video-layout">
        <section class="preview-section">
            <div class="add-preview">
                <a class="add-preview-logo" href="/">
                    <img src="/images/eclipse.png" alt="Eclipse logo">
                </a>

                <a class="video-box preview-video-box" href="javascript:void(0)">
                    <div class="thumbnail-wrapper">
                        <img class="thumbnail" id="preview-thumb" src="<?= htmlspecialchars($thumbUrl) ?>" alt="Thumbnail preview" loading="lazy">
                        <span class="duration" id="preview-duration" data-default-duration="<?= htmlspecialchars($durationLabel) ?>">
                            <?= htmlspecialchars($durationLabel) ?>
                        </span>
                    </div>

                    <div class="info">
                        <div class="profile-circle">
                            <img src="<?= htmlspecialchars($profileImageUrl) ?>" alt="Profile picture" class="profile-image-on-video-info">
                        </div>

                        <div class="title-wrapper">
                            <span class="title" id="preview-title" data-default-title="<?= htmlspecialchars($video['title']) ?>">
                                <?= htmlspecialchars($video['title']) ?>
                            </span>
                        </div>
                    </div>
                </a>

                <p class="preview-hint">Preview updates as you edit. Changes apply after saving.</p>
            </div>
        </section>
        <section class="forms-section">
            <div class="inf-organizer">
                <form class="add-video-form" action="actions/edit_video.php" method="POST" enctype="multipart/form-data">
                    <h1 class="form-title">Edit video</h1>

                    <?php if (!empty($flashError)): ?>
                        <p class="error-message" style="max-width: 360px; text-align:center;">
                            <?= htmlspecialchars($flashError) ?>
                        </p>
                    <?php endif; ?>

                    <input type="hidden" name="video_id" value="<?= htmlspecialchars((string)$videoId) ?>">
                    <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">

                    <div class="form-field">
                        <label class="form-label" for="title">Title</label>
                        <input
                            class="form-input"
                            type="text"
                            id="title"
                            name="title"
                            maxlength="50"
                            value="<?= htmlspecialchars($formTitle) ?>"
                            required
                        >
                    </div>

                    <div class="form-field">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-input" id="description" name="description" rows="4" maxlength="500" placeholder="Write a short description..."><?= htmlspecialchars($formDescription) ?></textarea>
                    </div>

                    <div class="form-field-files">
                        <label class="form-label" for="thumbnail">Thumbnail image</label>
                        <input class="file-submit" type="file" id="thumbnail" name="thumbnail" accept="image/*">
                        <small style="opacity:0.7;">Leave blank to keep the current thumbnail.</small>
                    </div>

                    <button type="submit" class="blank-button form-submit-button">
                        Save changes
                    </button>
                </form>
            </div>
        </section>
    </main>
    <?php include '../partials/footer.php'; ?> 
</body>
</html>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const titleInput = document.getElementById("title");
    const thumbInput = document.getElementById("thumbnail");

    const previewTitle = document.getElementById("preview-title");
    const previewThumb = document.getElementById("preview-thumb");

    const originalTitle = (previewTitle.dataset.defaultTitle || "").trim() || "Your title will appear here";

    function syncTitle() {
        const t = (titleInput.value || "").trim();
        previewTitle.textContent = t !== "" ? t : originalTitle;
    }

    titleInput.addEventListener("input", syncTitle);
    syncTitle();

    let thumbObjectUrl = null;
    thumbInput.addEventListener("change", () => {
        const file = thumbInput.files && thumbInput.files[0];
        if (!file) return;

        if (thumbObjectUrl) URL.revokeObjectURL(thumbObjectUrl);
        thumbObjectUrl = URL.createObjectURL(file);
        previewThumb.src = thumbObjectUrl;
    });

    window.addEventListener("beforeunload", () => {
        if (thumbObjectUrl) URL.revokeObjectURL(thumbObjectUrl);
    });
});
</script>
