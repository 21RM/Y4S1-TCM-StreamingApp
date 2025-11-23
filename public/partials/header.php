<header>
    <div class="header">
        <div class="logo">
            <a href="/">
                <img src="images/eclipse.png" alt="Eclipse logo">
            </a>
        </div>
        <div class="search-bar">
            <form class="search-form" action="library.php" method="get">
                
                <input type="search" name="q" class="search-input" placeholder="Search..."  autocomplete="off">

                <button type="button" class="clear-button" aria-label="Clear search">✖</button>

                <button type="submit" class="search-button">⮕</button>
            </form>
        </div>
        <div class="account">
            <button class="blank-button signin-button">Sign In</button>
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
