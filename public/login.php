<?php
require __DIR__ . '/../db/db.php';
session_start();

$redirect = $_GET['redirect'] ?? $_POST['redirect'] ?? '/index.php';
if ($redirect === '' || $redirect[0] !== '/') {
    $redirect = '/index.php';
}

$error = '';
$signupError= '';
$activeForm = 'login';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['form_type'];

    if ($formType === 'login') {
        $activeForm = 'login';
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === '' || $password === '') {
            $error = 'Please fill in both username and password.';
        }

        if ($error === '') {
            if (strlen($username) < 3 || strlen($username) > 30) {
                $error = 'Invalid username length.';
            } elseif (strlen($password) < 3 || strlen($password) > 30) {
                $error = 'Invalid password length.';
            }
        }

        if ($error === '' && !preg_match('/^[A-Za-z0-9_]+$/', $username)) {
            $error = 'Username can only contain letters, numbers and underscores.';
        }

        if ($error === '') {
            $stmt = $pdo->prepare('SELECT id, name, username, password_hash FROM users WHERE username = :username');
            $stmt->execute([':username' => $username]);
            $user = $stmt->fetch();

            if ($user && password_verify($password, $user['password_hash'])) {
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'username' => $user['username'],
                ];

                header('Location: ' . $redirect);
                exit;
            } else {
                $error = 'Invalid username or password.';
            }
        }

    } elseif ($formType === 'signup') {
        $activeForm = 'signup';
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name === '' || $username === '' || $password === '') {
            $signupError = 'Please fill in all fields.';
        }

        if ($signupError === '') {
            if (strlen($name) < 3 || strlen($name) > 50) {
                $signupError = 'Name must be between 3 and 50 characters.';
            } elseif (strlen($username) < 3 || strlen($username) > 30) {
                $signupError = 'Username must be between 3 and 30 characters.';
            } elseif (strlen($password) < 3 || strlen($password) > 30) {
                $signupError = 'Password must be between 3 and 30 characters.';
            }
        }

        if ($signupError === '' && !preg_match('/^[A-Za-z0-9_]+$/', $username)) {
            $signupError = 'Username can only contain letters, numbers and underscores.';
        }

        if ($signupError === '') {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE username = :username');
            $stmt->execute([':username' => $username]);
            $existing = $stmt->fetch();

            if ($existing) {
                $signupError = 'That username is already taken.';
            } else {
                $passwordHash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare('
                    INSERT INTO users (name, username, password_hash)
                    VALUES (:name, :username, :password_hash)
                ');

                $stmt->execute([
                    ':name' => $name,
                    ':username' => $username,
                    ':password_hash' => $passwordHash,
                ]);

                $newUserId = $pdo->lastInsertId();

                $baseDir = __DIR__ . '/user_files';

                if (!is_dir($baseDir)) {
                    if (!mkdir($baseDir, 0775, true)) {
                        $signupError = 'Could not initialize user storage.';
                    }
                }

                if ($signupError === '') {
                    $userDir = $baseDir . '/' . $username;

                    if (!is_dir($userDir) && !mkdir($userDir, 0775, true)) {
                        $signupError = 'Could not create user folder.';
                    }
                }

                if ($signupError === '') {
                    $defaultImgPath = __DIR__ . '/images/default_profile_img.png';
                    $userProfileImgPath = $userDir . '/profile_img.png';

                    if (!file_exists($defaultImgPath)) {
                        $signupError = 'Default profile image is missing.';
                    } elseif (!copy($defaultImgPath, $userProfileImgPath)) {
                        $signupError = 'Could not create default profile image.';
                    }
                }

                if ($signupError !== '') {
                    $stmt = $pdo->prepare('DELETE FROM users WHERE id = :id');
                    $stmt->execute([':id' => $newUserId]);
                } else {
                    $_SESSION['user'] = [
                        'id' => $newUserId,
                        'name' => $name,
                        'username' => $username,
                    ];

                    header('Location: ' . $redirect);
                    exit;
                }
            }
        }
    }
}

include '../partials/head.php';
?>

<body class="page-login">
    <main class="login-layout">
        <section class="logo-part">
            <div class="logo-organizer">
                <a href="/">
                    <img class="logo-text" src="images/eclipse.png" alt="eclipse word">
                </a>
                <img class="logo-img" src="images/eclipse_logo.png" alt="eclipse logo">
                <h1 class="logo-message"> Nice to see you again</h1>
            </div>
        </section>
        <section class="inf-part">
            <div class="inf-organizer">

                <!-- LOGIN FORM -->
                <form action="login.php" method="post" class="login-form auth-form <?php echo $activeForm === 'login' ? 'active-flex' : ''; ?>">
                    <h1 class="form-title"> LOG IN </h1>

                    <input type="hidden" name="form_type" value="login">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

                    <label class="form-field">
                        <span class="form-label">Username</span>
                        <input class="form-input" type="text" name="username" minlength="3" maxlength="30" autocomplete="off" required>
                    </label>
                    <label class="form-field">
                        <span class="form-label">Password</span>
                        <input class="form-input" type="password" name="password"  minlength="3" maxlength="30" autocomplete="off" required>
                    </label>
                    <?php if (!empty($error)): ?>
                        <p class="error-message"><?php echo htmlspecialchars($error); ?></p>
                    <?php endif; ?>
                    <button type="submit" class="blank-button form-submit-button">⮕</button>
                </form>

                <!-- SIGN UP FORM -->
                <form action="login.php" method="post" class="signup-form auth-form <?php echo $activeForm === 'signup' ? 'active-flex' : ''; ?>">
                    <h1 class="form-title"> SIGN UP </h1>

                    <input type="hidden" name="form_type" value="signup">
                    <input type="hidden" name="redirect" value="<?php echo htmlspecialchars($redirect); ?>">

                    <label class="form-field">
                        <span class="form-label">Name</span>
                        <input class="form-input" type="text" name="name" minlength="3" maxlength="50" autocomplete="off" required>
                    </label>
                    <label class="form-field">
                        <span class="form-label">Username</span>
                        <input class="form-input" type="text" name="username" minlength="3" maxlength="30" autocomplete="off" required>
                    </label>
                    <label class="form-field">
                        <span class="form-label">Password</span>
                        <input class="form-input" type="password" name="password"  minlength="3" maxlength="30" autocomplete="off" required>
                    </label>
                    <?php if (!empty($signupError)): ?>
                        <p class="error-message"><?php echo htmlspecialchars($signupError); ?></p>
                    <?php endif; ?>
                    <button type="submit" class="blank-button form-submit-button">⮕</button>
                </form>

                <div class="login-text-container active-flex">
                    <h1> Don't have an account? </h1>
                    <button class="blank-button change-auth change-to-signup"> Create one here </button>
                </div>
                <div class="signup-text-container">
                    <h1> Already have an account? </h1>
                    <button class="blank-button change-auth change-to-login"> Log in here </button>
                </div>
            </div>
        </section>
    </main>
</body>
</html>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const loginForm = document.querySelector('.login-form');
        const signupForm = document.querySelector('.signup-form');
        const toSignup = document.querySelector('.change-to-signup');
        const toLogin = document.querySelector('.change-to-login');
        const loginText = document.querySelector('.login-text-container');
        const signupText = document.querySelector('.signup-text-container');
        const logoMessage = document.querySelector('.logo-message');

        function showLogin() {
            loginForm.classList.add('active-flex');
            signupForm.classList.remove('active-flex');
            loginText.classList.add('active-flex');
            signupText.classList.remove('active-flex');
            logoMessage.textContent = "Nice to see you again";
        }

        function showSignup() {
            loginForm.classList.remove('active-flex');
            signupForm.classList.add('active-flex');
            loginText.classList.remove('active-flex');
            signupText.classList.add('active-flex');
            logoMessage.textContent = "Nice to meet you";
        }

        toSignup.addEventListener('click', (e) => {
            e.preventDefault();
            showSignup();
        });

        toLogin.addEventListener('click', (e) => {
            e.preventDefault();
            showLogin();
        });
    });
</script>
