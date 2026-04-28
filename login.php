<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header('Location: my_bookings.php');
  exit;
}
if (isset($_SESSION['admin_id'])) {
  header('Location: admin_dashboard.php');
  exit;
}
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once 'db.php';
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
  $stmt->execute([$email]);
  $user = $stmt->fetch();
  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    header('Location: my_bookings.php');
    exit;
  } else {
    $error = 'Invalid email or password. Please try again.';
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — BoardingRooms</title>
  <style>
    :root {
      --dark-bg: #0b1120;
      --card-bg: #1e293b;
      --accent-red: #ef4444;
      --accent-blue: #38bdf8;
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --border-color: #334155;
    }

    body {
      background-color: var(--dark-bg);
      color: var(--text-main);
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      margin: 0;
      display: flex;
      flex-direction: column;
      min-height: 100vh;
    }

    /* Navigation Styling */
    .auth-nav {
      background: rgba(15, 23, 42, 0.9);
      backdrop-filter: blur(8px);
      border-bottom: 1px solid var(--border-color);
      padding: 0 40px;
      height: 70px;
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .logo-wrap {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
    }

    .logo-pin {
      width: 38px;
      height: 38px;
      background: var(--accent-red);
      border-radius: 50% 50% 50% 10%;
      transform: rotate(-45deg);
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .logo-pin span {
      transform: rotate(45deg);
      font-size: 18px;
    }

    .logo-text {
      font-size: 26px;
      font-weight: 800;
      letter-spacing: -1px;
    }

    .btn-outline {
      border: 1px solid var(--border-color);
      color: #fff;
      padding: 8px 18px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
      background: rgba(255, 255, 255, 0.05);
      transition: 0.2s;
    }

    .btn-outline:hover {
      background: rgba(255, 255, 255, 0.1);
      border-color: var(--text-muted);
    }

    /* Login Box Layout */
    .auth-wrap {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
    }

    .auth-box {
      background: var(--card-bg);
      border-radius: 24px;
      border: 1px solid var(--border-color);
      padding: 48px;
      width: 100%;
      max-width: 440px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .auth-header {
      text-align: center;
      margin-bottom: 32px;
    }

    .auth-icon-circle {
      width: 60px;
      height: 60px;
      background: rgba(56, 189, 248, 0.1);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
      color: var(--accent-blue);
    }

    .auth-header h1 {
      font-size: 28px;
      font-weight: 800;
      margin: 0 0 8px 0;
    }

    .auth-header p {
      color: var(--text-muted);
      font-size: 15px;
    }

    /* Form Styling */
    .form-group {
      margin-bottom: 20px;
    }

    .form-label {
      display: block;
      font-size: 14px;
      font-weight: 600;
      margin-bottom: 8px;
    }

    .form-control {
      background: #0f172a;
      border: 1px solid var(--border-color);
      color: #fff;
      border-radius: 12px;
      padding: 14px 16px;
      width: 100%;
      box-sizing: border-box;
      font-size: 16px;
      transition: all 0.2s ease;
    }

    .form-control:focus {
      border-color: var(--accent-blue);
      outline: none;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.15);
    }

    .btn-submit {
      background: var(--accent-red);
      color: white;
      border: none;
      border-radius: 12px;
      padding: 15px;
      width: 100%;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      margin-top: 10px;
      transition: transform 0.2s, background 0.2s;
    }

    .btn-submit:hover {
      transform: translateY(-1px);
      background: #dc2626;
    }

    .auth-divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 24px 0;
    }

    .auth-divider::before,
    .auth-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: var(--border-color);
    }

    .auth-divider span {
      font-size: 13px;
      color: var(--text-muted);
    }

    .auth-footer {
      text-align: center;
      margin-top: 24px;
      font-size: 14px;
      color: var(--text-muted);
    }

    .auth-footer a {
      color: var(--accent-blue);
      text-decoration: none;
      font-weight: 700;
    }

    .admin-portal {
      text-align: center;
      margin-top: 10px;
    }

    .admin-portal a {
      font-size: 13px;
      color: var(--text-muted);
      border: 1px solid var(--border-color);
      border-radius: 10px;
      padding: 10px 20px;
      display: inline-block;
      text-decoration: none;
      transition: 0.2s;
    }

    .admin-portal a:hover {
      border-color: #fff;
      color: #fff;
      background: rgba(255, 255, 255, 0.05);
    }

    .alert {
      background: rgba(239, 68, 68, 0.1);
      color: #fca5a5;
      padding: 14px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 14px;
      border: 1px solid rgba(239, 68, 68, 0.2);
      text-align: center;
    }
  </style>
</head>

<body>
  <nav class="auth-nav">
    <a href="index.php" class="logo-wrap">
      <div class="logo-pin"><span>🏠</span></div>
      <div class="logo-text">
        <span style="color: var(--accent-red);">boarding</span>
        <span style="color: #fff;">rooms</span>
      </div>
    </a>
    <a href="register.php" class="btn-outline">Create Account</a>
  </nav>

  <div class="auth-wrap">
    <div class="auth-box">
      <div class="auth-header">
        <div class="auth-icon-circle">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="currentColor">
            <path
              d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
          </svg>
        </div>
        <h1>Welcome back</h1>
        <p>Sign in to manage your bookings</p>
      </div>

      <?php if ($error): ?>
        <div class="alert">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="you@email.com" required
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>
        <div class="form-group">
          <label class="form-label" style="display:flex;justify-content:space-between;">
            Password <a href="#"
              style="font-size:12px;color:var(--accent-red);text-decoration:none;font-weight:600;">Forgot?</a>
          </label>
          <input type="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>
        <button type="submit" class="btn-submit">Sign In</button>
      </form>

      <div class="auth-divider"><span>or</span></div>

      <div class="admin-portal">
        <a href="admin_login.php">🔐 Admin Login Portal</a>
      </div>

      <div class="auth-footer">
        Don't have an account? <a href="register.php">Create one free</a>
      </div>
    </div>
  </div>
</body>

</html>