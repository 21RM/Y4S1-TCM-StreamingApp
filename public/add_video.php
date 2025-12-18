<?php

    require __DIR__ . '/../db/db.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user']['id'])) {
        $currentPath = $_SERVER['REQUEST_URI'] ?? '/add_video.php';
        header('Location: /login.php?redirect=' . urlencode($currentPath));
        exit;
    }

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
                    <img class="thumbnail" id="preview-thumb" src="/images/default_thumbnail.png" alt="Thumbnail preview" loading="lazy">
                    <span class="duration" id="preview-duration">00:00</span>
                    </div>

                    <div class="info">
                    <div class="profile-circle">
                        <img src="/images/default_profile_img.png" alt="Profile picture" class="profile-image-on-video-info">
                    </div>

                    <div class="title-wrapper">
                        <span class="title" id="preview-title">Your title will appear here</span>
                    </div>
                    </div>
                </a>

            <p class="preview-hint">This is a live preview. It won’t upload until you click “Upload video”.</p>
            </div>
        </section>
        <section class="forms-section">
            <div class="inf-organizer">
                <form class="add-video-form" id="add-video-form" action="actions/add_video.php" method="POST" enctype="multipart/form-data">
                    
                    <h1 class="form-title">Add a new video</h1>

                    <div class="form-field">
                        <label class="form-label" for="title">Title</label>
                        <input class="form-input" type="text" id="title" name="title" placeholder="Title me please... " maxlength="50" required>
                    </div>

                    <div class="form-field">
                        <label class="form-label" for="description">Description</label>
                        <textarea class="form-input" id="description" name="description" rows="4" placeholder="Write a short description..." maxlength="500"></textarea>
                    </div>

                    <div class="form-field-files">
                        <label class="form-label" for="thumbnail">Thumbnail image</label>
                        <input class="file-submit" type="file" id="thumbnail" name="thumbnail" accept="image/*" required>
                    </div>

                    <div class="form-field-files">
                        <label class="form-label" for="video">Video file</label>
                        <input class="file-submit" type="file" id="video" name="video" accept="video/*" required>
                    </div>

                    <button type="submit" class="blank-button form-submit-button" id="upload-submit">
                        Upload video
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
  const videoInput = document.getElementById("video");
  const form = document.getElementById("add-video-form");
  const submitBtn = document.getElementById("upload-submit");

  const previewTitle = document.getElementById("preview-title");
  const previewThumb = document.getElementById("preview-thumb");
  const previewDuration = document.getElementById("preview-duration");

  let thumbObjectUrl = null;
  let videoObjectUrl = null;

  function setDurationFromFile(file) {
    if (!file) { previewDuration.textContent = "00:00"; return; }

    if (videoObjectUrl) URL.revokeObjectURL(videoObjectUrl);
    videoObjectUrl = URL.createObjectURL(file);

    const v = document.createElement("video");
    v.preload = "metadata";
    v.src = videoObjectUrl;

    v.onloadedmetadata = () => {
      const seconds = Math.max(0, Math.round(v.duration || 0));
      const h = Math.floor(seconds / 3600);
      const m = Math.floor((seconds % 3600) / 60);
      const s = seconds % 60;

      previewDuration.textContent = (h > 0)
        ? String(h).padStart(2, "0") + ":" + String(m).padStart(2, "0") + ":" + String(s).padStart(2, "0")
        : String(m).padStart(2, "0") + ":" + String(s).padStart(2, "0");
    };

    v.onerror = () => {
      previewDuration.textContent = "00:00";
    };
  }

  function syncTitle() {
    const t = (titleInput.value || "").trim();
    previewTitle.textContent = t !== "" ? t : "Your title will appear here";
  }
  titleInput.addEventListener("input", syncTitle);
  syncTitle();

  thumbInput.addEventListener("change", () => {
    const file = thumbInput.files && thumbInput.files[0];
    if (!file) return;

    if (thumbObjectUrl) URL.revokeObjectURL(thumbObjectUrl);
    thumbObjectUrl = URL.createObjectURL(file);
    previewThumb.src = thumbObjectUrl;
  });

  videoInput.addEventListener("change", () => {
    const file = videoInput.files && videoInput.files[0];
    setDurationFromFile(file);
  });

  if (form && submitBtn) {
    form.addEventListener("submit", () => {
      submitBtn.disabled = true;
      submitBtn.classList.add("is-loading");
      submitBtn.innerHTML = '<span class="spinner" aria-hidden="true"></span> Uploading…';
    });
  }

  window.addEventListener("beforeunload", () => {
    if (thumbObjectUrl) URL.revokeObjectURL(thumbObjectUrl);
    if (videoObjectUrl) URL.revokeObjectURL(videoObjectUrl);
  });
});
</script>
