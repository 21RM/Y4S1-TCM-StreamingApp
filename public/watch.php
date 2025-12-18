<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../db/db.php'; 

$videoId = isset($_GET['id']) ? (int)$_GET['id'] : null;

$stmt = $pdo->prepare(
    'SELECT v.id, v.title, v.path, v.duration, v.thumbnail, v.description, v.created_at,
            u.id   AS owner_id,
            u.username AS owner_username,
            u.name AS owner_name
     FROM videos v
     JOIN users u ON v.user_id = u.id
     WHERE v.id = :id
     LIMIT 1'
);
$stmt->execute([':id' => $videoId]);
$myVideo = $stmt->fetch(PDO::FETCH_ASSOC);

$ownerUsername = $myVideo['owner_username'];
$ownerBasePath = __DIR__ . '/user_files/' . $ownerUsername;
$ownerBaseUrl  = '/user_files/' . rawurlencode($ownerUsername ?? '');
$ownerProfileImageUrl = '/images/default_profile_img.png';

foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
    $path = "$ownerBasePath/profile_img.$ext";
    if (file_exists($path)) {
        $ownerProfileImageUrl = "$ownerBaseUrl/profile_img.$ext";
        break;
    }
}


$stmt2 = $pdo->prepare(
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
     WHERE v.id != :id
     ORDER BY v.created_at DESC
     LIMIT 20'
);

$stmt2->execute([':id' => $videoId]);
$otherVideos = $stmt2->fetchAll(PDO::FETCH_ASSOC);

$videoUrl = '/' . ltrim($myVideo['path'], '/');

$ext = strtolower(pathinfo($videoUrl, PATHINFO_EXTENSION));
switch ($ext) {
    case 'webm':
        $videoMime = 'video/webm';
        break;
    case 'ogg':
    case 'ogv':
        $videoMime = 'video/ogg';
        break;
    default:
        $videoMime = 'video/mp4';
        break;
}

include '../partials/head.php';
include '../partials/header.php';
?>


<body class="page-watch">
    <main class="watch-layout">
        <section class="watch-player">
            <div class="video-wrapper">
                <?php if (!$myVideo): ?>
                    <div class="watch-error">
                        <img src="images/not_found.png" alt="Not found">
                        <h1>Video not found</h1>
                        <p>The video you are looking for does not exist.</p>
                    </div>
                <?php else: ?>
                    <video id="main-video" class="video" controls preload="metadata">
                        <source src="<?= htmlspecialchars($videoUrl) ?>" type="<?= htmlspecialchars($videoMime) ?>">
                    </video>
                <?php endif; ?>
            </div>
            <?php if ($myVideo): ?>
                <div class="video-inf-wrapper">
                    <div class="video-info-header">
                        <div class="profile-circle">
                            <img
                                src="<?= htmlspecialchars($ownerProfileImageUrl) ?>"
                                alt="<?= htmlspecialchars($ownerUsername) ?> profile picture"
                                class="profile-image-on-video-info"
                            >
                        </div>
                        <div class="video-title-block">
                            <h1 class="video-title">
                                <?= htmlspecialchars($myVideo['title']) ?>
                            </h1>
                        </div>
                    </div>
                    <?php if (!empty($myVideo['description'])): ?>
                        <p class="video-description">
                            <?= htmlspecialchars($myVideo['description']) ?>
                        </p>
                    <?php endif; ?>
                    <div class="context-sim" data-video-title="<?= htmlspecialchars($myVideo['title']) ?>">
                        <p class="context-sim-label">Contextual awareness (prototype)</p>
                        <button
                            type="button"
                            class="context-sim-button"
                            id="context-sim-button"
                            data-video-title="<?= htmlspecialchars($myVideo['title']) ?>"
                        >
                            Run context detection
                        </button>
                        <div class="context-sim-result" id="context-sim-result">
                            Awaiting analysis for this clip.
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </section>
        <section class="other-videos-sidebar">
            <h2 class="sidebar-title">Related videos</h2>

            <div class="sidebar-videos-list">
                <?php foreach ($otherVideos as $other):
                        $video = $other;
                        include __DIR__ . '/../partials/video_box.php';
                endforeach; ?>
            </div>
        </section>
    </main>
    <?php include '../partials/footer.php'; ?>  
    <script>
        (function () {
            const button = document.getElementById('context-sim-button');
            const result = document.getElementById('context-sim-result');
            if (!button || !result) return;

            const audioProfiles = [
                'dialogue with subtle soundtrack',
                'crowd ambience and passing cars',
                'intense music cues with percussion',
                'quiet room tone with keyboard clicks',
                'street traffic with sporadic horns',
                'nature ambience with birds and wind chimes',
                'alarm bells mixed with emergency radio chatter'
            ];
            const lightingProfiles = [
                'bright studio lighting',
                'dim indoor lighting',
                'sunset golden hour lighting',
                'flashing concert strobes',
                'overcast outdoor lighting',
                'neon-lit nighttime street',
                'harsh midday sunlight'
            ];
            const locationContexts = [
                'downtown GPS cluster (~40.712°N, -74.006°W)',
                'coastal route (~34.019°N, -118.491°W)',
                'campus quad (~37.871°N, -122.258°W)',
                'suburban cul-de-sac (~47.606°N, -122.332°W)',
                'mountain overlook (~39.739°N, -104.990°W)',
                'transit hub (~51.507°N, -0.128°W)'
            ];

            function pickRandom(list) {
                return list[Math.floor(Math.random() * list.length)];
            }

            function simulate() {
                button.disabled = true;
                button.textContent = 'Analyzing…';
                result.textContent = 'Scanning audio, lighting, GPS, and sensor hints…';

                setTimeout(() => {
                    const title = button.dataset.videoTitle || 'this video';
                    const audio = pickRandom(audioProfiles);
                    const lighting = pickRandom(lightingProfiles);
                    const location = pickRandom(locationContexts);
                    const confidence = Math.floor(Math.random() * 20) + 80;

                    result.innerHTML = `
                        <strong>Clip:</strong> ${title}<br>
                        <strong>Audio context:</strong> ${audio}<br>
                        <strong>Lighting:</strong> ${lighting}<br>
                        <strong>Location guess:</strong> ${location}<br>
                        <strong>Confidence:</strong> ${confidence}% (simulated)
                    `;

                    button.disabled = false;
                    button.textContent = 'Run context detection';
                }, 1200 + Math.random() * 800);
            }

            button.addEventListener('click', simulate);
        })();
    </script>
</body>
</html>
