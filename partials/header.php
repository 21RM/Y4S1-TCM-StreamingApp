<?php
require __DIR__ . '/../db/db.php';


$currentPath = $_SERVER['REQUEST_URI'] ?? '/';

$username = $_SESSION['user']['username'] ?? null;

$basePath = __DIR__ . '/../public/user_files/' . $username;
$baseUrl = '/user_files/' . rawurlencode($username ?? '');
$profileImageUrl ='/images/default-avatar.png';

foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
    $path = "$basePath/profile_img.$ext";
    if (file_exists($path)) {
        $profileImageUrl = "$baseUrl/profile_img.$ext";
        break;
    }
}
?>

<header>
    <div class="header">
        <div class="logo">
            <a href="/">
                <img src="images/eclipse.png" alt="Eclipse logo">
            </a>
        </div>
        <div class="search-bar">
            <form class="search-form" action="library.php" method="get">
                
                <input type="search" name="q" class="search-input" placeholder="Search..." autocomplete="off">

                <button type="button" class="clear-button" aria-label="Clear search">✖</button>

                <button type="submit" class="search-button">⮕</button>
            </form>
        </div>
        <div class="account">
            <?php if (isset($_SESSION['user'])): ?>
              <button type="button" class="blank-button account-circle" onclick="window.location.href='/profile.php'">
                <img src="<?= htmlspecialchars($profileImageUrl) ?>"  alt="Profile picture" class="header-profile-image">
              </button>
            <?php else: ?>
              <button class="blank-button login-button" onclick="window.location.href='<?php echo '/login.php?redirect=' . urlencode($currentPath); ?>'">Log In</button>
            <?php endif; ?>
        </div>
    </div>
</header>

<script>
  const input = document.querySelector('.search-input');
  const clearBtn = document.querySelector('.clear-button');

  function toggleClearButton() {
    if (input.value.trim() === '') {
      clearBtn.classList.add('hidden');
    } else {
      clearBtn.classList.remove('hidden');
    }
  }

  // Clear on click
  clearBtn.addEventListener('click', () => {
    input.value = '';
    input.focus();
    toggleClearButton();
  });

  // Show/hide based on typing
  input.addEventListener('input', toggleClearButton);

  // Initial state
  toggleClearButton();
  
</script>
