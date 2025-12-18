<?php
    require __DIR__ . '/../db/db.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user']['id'])) {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '/profile.php';
        header('Location: /login.php?redirect=' . urlencode($currentPath));
        exit;
    }

    $userId = $_SESSION['user']['id'];
    $username = $_SESSION['user']['username'] ?? null;
    $name = $_SESSION['user']['name'];

    $basePath = __DIR__ . '/user_files/' . $username;
    $baseUrl = '/user_files/' . rawurlencode($username ?? '');
    $profileImageUrl ='/images/default_profile_img.png';

    foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
        $path = "$basePath/profile_img.$ext";
        if (file_exists($path)) {
            $profileImageUrl = "$baseUrl/profile_img.$ext";
            break;
        }
    }

    $stmt = $pdo->prepare(
        'SELECT v.id, v.title, v.path, v.duration, v.thumbnail, v.description, v.created_at,
                u.username AS owner_username
        FROM videos v
        JOIN users u ON u.id = v.user_id
        WHERE v.user_id = :user_id
        ORDER BY v.created_at DESC'
    );
    $stmt->execute([':user_id' => $userId]);
    $myVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);


    include '../partials/head.php';
?>

<body class="page-profile">
    <main class="profile-layout">
        <aside class="profile-sidebar">
            <div class="profile-sidebar-header">
                <div class="profile-sidebar-logo">
                    <a href="/">
                        <img src="images/eclipse.png" alt="Eclipse logo">
                    </a>
                </div>
            </div>
            <div class="profile-sidebar-options-organizer">
                <button class="blank-button sidebar-option-button" data-target="section-profile">
                    Profile
                </button>
                <button class="blank-button sidebar-option-button is-active" data-target="section-my-videos">
                    My Videos
                </button>
                <button class="blank-button sidebar-option-button" id="logout-button">
                    Log Out
                </button>
            </div>
        </aside>
        <section class="profile-content">
            <div class="profile-container profile-section" id="section-profile">
                <form id="profile-image-form" action="/actions/upload_profile_image.php" method="post" enctype="multipart/form-data">
                    <input type="file" name="profile-image" id="profile-image-input" accept="image/png,image/jpeg,image/webp" hidden>
                    <div class="profile-image-wrapper" id="profile-image-trigger">
                        <img src="<?= htmlspecialchars($profileImageUrl) ?>" alt="Profile picture" class="profile-image">
                        <span class="profile-image-overlay">Change</span>
                    </div>
                </form>
                <div class="profile-info-organizer">
                    <div class="profile-info-title"> Name </div>
                    <div class="profile-info"> <?= htmlspecialchars($name) ?> </div>
                </div>
                <div class="profile-info-organizer">
                    <div class="profile-info-title"> Username </div>
                    <div class="profile-info"> <?= htmlspecialchars($username) ?> </div>
                </div>
            </div>
            <div class="my-videos-container profile-section  is-active" id="section-my-videos">
                <div class="my-videos-header">
                    <a href="/add_video.php">
                        <button class="blank-button add-video-button"> Add Video + </button>
                    </a>
                </div>
                <div class="videos_grid">
                    <?php if (empty($myVideos)): ?>
                        <p class="videos-empty">You haven’t uploaded any videos yet.</p>
                    <?php else: ?>
                        <?php foreach ($myVideos as $video): ?>
                            <?php include __DIR__ . '/../partials/video_box.php'; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </section>
    </main>
</body>
</html>



<script>
    document.addEventListener('DOMContentLoaded', () => {
        // SIDEBAR BUTTONS -------------------------------------------------------------------
        const buttons = document.querySelectorAll('.sidebar-option-button[data-target]');
        const sections = document.querySelectorAll('.profile-section');

        function showSection(sectionId) {
            sections.forEach(sec => {
                sec.classList.toggle('is-active', sec.id === sectionId);
            });
        }

        function setActiveButton(activeBtn) {
            buttons.forEach(btn => btn.classList.remove('is-active'));
            if (activeBtn) activeBtn.classList.add('is-active');
        }

        buttons.forEach(btn => {
            btn.addEventListener('click', () => {
                const targetId = btn.dataset.target;
                if (!targetId) return;
                setActiveButton(btn);
                showSection(targetId);
            });
        });

        const logoutBtn = document.getElementById('logout-button');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                window.location.href = '/actions/logout.php';
            });
        }



        // CHANGE PROFILE IMAGE ------------------------------------------------------------------------
        const profileImageTrigger = document.getElementById('profile-image-trigger');
        const profileImageInput = document.getElementById('profile-image-input');
        const profileImageForm = document.getElementById('profile-image-form');

        if (profileImageTrigger && profileImageInput && profileImageForm) {
            profileImageTrigger.addEventListener('click', () => {
                profileImageInput.click();
            });

            profileImageInput.addEventListener('change', () => {
                if (profileImageInput.files && profileImageInput.files.length > 0) {
                    profileImageForm.submit();
                }
            });
        }

        // DELETE BUTTON (My Videos only) ------------------------------------------------------
        const myVideosSection = document.getElementById('section-my-videos');
        if (myVideosSection) {
        const style = document.createElement('style');
        style.textContent = `
            .mv-card { position: relative; }
            .mv-action-btn{
                position:absolute;
                top:15px;
                z-index: 50;
                opacity:0;
                pointer-events:none;
                padding:8px;
                border-radius:10px;
                background: rgba(0,0,0,.55);
                line-height:0;
                border:0;
                cursor:pointer;
                transition: opacity 0.15s ease;
            }
            .mv-card:hover .mv-action-btn { opacity:1; pointer-events:auto; }
            .mv-action-btn svg { fill:#fff; }
            .mv-del-btn { right:30px; }
            .mv-edit-btn { right:70px; }
        `;
        document.head.appendChild(style);

        let cards = [];
        cards = Array.from(myVideosSection.querySelectorAll(".video-box"));

        cards.forEach(card => {
            card.classList.add('mv-card');

            if (card.tagName.toLowerCase() !== 'a') return;

            let videoId = null;
            try {
                const url = new URL(card.getAttribute('href'), window.location.origin);
                videoId = url.searchParams.get('id');
            } catch {
                return;
            }
            if (!videoId) return;

            const delBtn = document.createElement('button');
            delBtn.type = 'button';
            delBtn.className = 'mv-action-btn mv-del-btn';
            delBtn.innerHTML = `
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                <path d="M9 3h6l1 2h5v2H3V5h5l1-2Zm1 7h2v9h-2v-9Zm4 0h2v9h-2v-9ZM6 8h12l-1 13H7L6 8Z"></path>
            </svg>
            `;

            delBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (!confirm('Are you sure you want to delete this video?')) return;

            // Redirect to your delete action (this reloads the page)
            const redirect = encodeURIComponent(window.location.pathname + window.location.search);
            window.location.href = `/actions/delete_video.php?id=${encodeURIComponent(videoId)}&redirect=${redirect}`;
            });

            const editBtn = document.createElement('button');
            editBtn.type = 'button';
            editBtn.className = 'mv-action-btn mv-edit-btn';
            editBtn.innerHTML = `
            <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true">
                <path d="M3 17.25V21h3.75L17.81 9.94l-3.75-3.75L3 17.25Zm14.71-8.54c.39-.39.39-1.02 0-1.41l-2.54-2.54a.996.996 0 0 0-1.41 0l-1.83 1.83 3.75 3.75 2.03-2.03Z"></path>
            </svg>
            `;

            editBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            const redirect = encodeURIComponent(window.location.pathname + window.location.search);
            window.location.href = `/edit_video.php?id=${encodeURIComponent(videoId)}&redirect=${redirect}`;
            });

            card.appendChild(editBtn);
            card.appendChild(delBtn);
        });
        }

    });
</script>
