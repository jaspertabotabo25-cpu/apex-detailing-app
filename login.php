<?php
require_once 'config/auth.php';

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            login_user($user);
            header('Location: index.php');
            exit;
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Login - Apex Custom Detailing</title>
  <link rel="stylesheet" href="styles.css">
  <style>
    body { background: #1a1d24 url('assets/pattern-hero.svg') center/cover no-repeat; display: flex; align-items: center; justify-content: center; min-height: 100vh; font-family: 'Inter', sans-serif; color: var(--white); margin: 0; }
    .auth-box { background: rgba(30, 33, 41, 0.7); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); padding: 48px 40px; border-radius: 12px; box-shadow: 0 25px 50px rgba(0,0,0,0.5); width: 100%; max-width: 420px; border: 1px solid rgba(255,255,255,0.08); }
    .auth-box img { height: 44px; margin: 0 auto 28px; display: block; filter: brightness(0) invert(1) drop-shadow(0 4px 6px rgba(0,0,0,0.3)); }
    .auth-box h2 { font-family: 'Poppins', sans-serif; font-size: 1.6rem; margin-bottom: 8px; text-align: center; color: #fff; font-weight: 700; }
    .auth-box p { color: #a9acb3; font-size: 0.95rem; margin-bottom: 32px; text-align: center; }
    .form-group { margin-bottom: 20px; position: relative; }
    .form-group label { display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 8px; color: #d1d5db; letter-spacing: 0.5px; }
    .form-group input { width: 100%; box-sizing: border-box; padding: 14px 16px; background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.15); border-radius: 6px; font-family: 'Inter', sans-serif; font-size: 1rem; color: #fff; transition: border-color 0.2s, box-shadow 0.2s; }
    .form-group input:focus { outline: none; border-color: #686d76; box-shadow: 0 0 0 3px rgba(104,109,118,0.2); }
    .password-wrapper { position: relative; display: flex; align-items: center; }
    .password-wrapper input { padding-right: 48px; }
    .toggle-password { position: absolute; right: 12px; background: none; border: none; color: #9ca3af; cursor: pointer; padding: 4px; display: flex; align-items: center; justify-content: center; transition: color 0.2s; }
    .toggle-password:hover { color: #fff; }
    .toggle-password svg { width: 20px; height: 20px; }
    .btn-submit { width: 100%; margin-top: 12px; text-align: center; padding: 14px; font-size: 0.95rem; text-transform: uppercase; letter-spacing: 1px; border-radius: 6px; background: #3a3f4b; transition: background 0.2s; font-weight: 700; cursor: pointer; color: white; border: none; }
    .btn-submit:hover { background: #4b515d; }
    .error { background: rgba(217, 83, 79, 0.1); border: 1px solid rgba(217, 83, 79, 0.3); color: #ff8a8a; padding: 12px; border-radius: 6px; font-size: 0.85rem; margin-bottom: 20px; font-weight: 500; text-align: center; }
    .auth-links { margin-top: 24px; text-align: center; font-size: 0.9rem; color: #a9acb3; }
    .auth-links a { color: #fff; font-weight: 600; text-decoration: underline; text-decoration-color: rgba(255,255,255,0.3); text-underline-offset: 4px; transition: text-decoration-color 0.2s; }
    .auth-links a:hover { text-decoration-color: #fff; }
  </style>
</head>
<body>
  <div class="auth-box">
    <a href="index.php"><img src="assets/svg_brand_logo.svg" alt="Apex Logo"></a>
    <h2>Welcome Back</h2>
    <p>Sign in to manage your appointments.</p>
    
    <?php if ($error): ?>
      <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="">
      <div class="form-group">
        <label for="email">Email Address</label>
        <input type="email" id="email" name="email" required>
      </div>
      <div class="form-group">
        <label for="password">Password</label>
        <div class="password-wrapper">
          <input type="password" id="password" name="password" required>
          <button type="button" class="toggle-password" id="togglePassword" aria-label="Toggle password visibility">
            <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
          </button>
        </div>
      </div>
      <button type="submit" class="btn btn-navy btn-submit">Log In</button>
    </form>
    
    <div class="auth-links">
      Don't have an account? <a href="register.php">Sign up</a>
    </div>
  </div>

  <script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');
    const eyeIcon = document.querySelector('#eye-icon');

    const eyeOpenSvg = `<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />`;
    const eyeClosedSvg = `<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />`;

    togglePassword.addEventListener('click', function () {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      eyeIcon.innerHTML = type === 'password' ? eyeOpenSvg : eyeClosedSvg;
    });
  </script>
</body>
</html>
