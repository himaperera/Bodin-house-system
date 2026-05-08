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

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

  <style>
    :root {
      --navy-deep: #020617;
      --navy-tint: rgba(2, 6, 23, 0.85);
      --glass-bg: rgba(255, 255, 255, 0.05);
      --glass-border: rgba(255, 255, 255, 0.12);
      --text-main: #f8fafc;
      --text-muted: #cbd5e1;
      --red: #ef4444;
      --blue-accent: #38bdf8;
      --green-accent: #10b981;
    }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      color: var(--text-main);
      background-color: var(--navy-deep);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      position: relative;
    }

    /* Background Image & Overlay */
    body::before {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background-image: url('https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&q=80&w=1600');
      background-size: cover;
      background-position: center;
      background-attachment: fixed;
      z-index: -2;
    }

    body::after {
      content: "";
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: var(--navy-tint);
      backdrop-filter: blur(8px);
      z-index: -1;
    }

    /* --- Navbar --- */
    .navbar {
      background: rgba(2, 6, 23, 0.6);
      backdrop-filter: blur(15px);
      border-bottom: 1px solid var(--glass-border);
      padding: 0 40px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      height: 70px;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .navbar-logo {
      font-size: 22px;
      font-weight: 800;
      display: flex;
      align-items: center;
      gap: 8px;
      text-decoration: none;
      color: #fff;
    }

    .logo-pin {
      width: 32px;
      height: 32px;
      background: var(--red);
      border-radius: 50% 50% 50% 0;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 14px;
    }

    .btn-outline-nav {
      border: 1px solid var(--glass-border);
      color: #fff;
      padding: 8px 16px;
      border-radius: 12px;
      text-decoration: none;
      transition: 0.3s;
      font-size: 14px;
      font-weight: 600;
    }

    .btn-outline-nav:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    /* --- Login Box Layout --- */
    .auth-wrap {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 40px 20px;
      animation: fadeIn 0.8s ease;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .auth-box {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(20px);
      border-radius: 24px;
      border: 1px solid var(--glass-border);
      padding: 48px;
      width: 100%;
      max-width: 440px;
      box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
    }

    .auth-header {
      text-align: center;
      margin-bottom: 32px;
    }

    .auth-icon-circle {
      width: 70px;
      height: 70px;
      background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(139, 92, 246, 0.2));
      border: 1px solid rgba(56, 189, 248, 0.3);
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
      color: var(--blue-accent);
      font-size: 28px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .auth-header h1 {
      font-size: 28px;
      font-weight: 800;
      margin: 0 0 8px 0;
      color: #fff;
    }

    .auth-header p {
      color: var(--text-muted);
      font-size: 15px;
      margin: 0;
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
      color: var(--text-muted);
    }

    .form-control {
      background: rgba(0, 0, 0, 0.3);
      border: 1px solid var(--glass-border);
      color: #fff;
      border-radius: 12px;
      padding: 14px 16px 14px 40px;
      /* Space for icon */
      width: 100%;
      box-sizing: border-box;
      font-size: 15px;
      font-family: 'Inter', sans-serif;
      transition: all 0.3s ease;
    }

    .input-wrapper {
      position: relative;
    }

    .input-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: 14px;
    }

    .form-control:focus {
      border-color: var(--blue-accent);
      outline: none;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
      background: rgba(0, 0, 0, 0.5);
    }

    .form-control:focus+.input-icon {
      color: var(--blue-accent);
    }

    .btn-submit {
      background: var(--blue-accent);
      color: #000;
      border: none;
      border-radius: 12px;
      padding: 15px;
      width: 100%;
      font-size: 16px;
      font-weight: 800;
      cursor: pointer;
      margin-top: 10px;
      transition: 0.3s;
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      background: #fff;
      box-shadow: 0 10px 20px rgba(56, 189, 248, 0.3);
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
      background: var(--glass-border);
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
      color: var(--blue-accent);
      text-decoration: none;
      font-weight: 700;
      transition: 0.3s;
    }

    .auth-footer a:hover {
      color: #fff;
    }

    .admin-portal {
      text-align: center;
      margin-top: 10px;
    }

    .admin-portal a {
      font-size: 13px;
      color: var(--text-muted);
      border: 1px solid var(--glass-border);
      border-radius: 10px;
      padding: 10px 20px;
      display: inline-block;
      text-decoration: none;
      transition: 0.3s;
      background: rgba(255, 255, 255, 0.02);
    }

    .admin-portal a:hover {
      border-color: var(--blue-accent);
      color: #fff;
      background: rgba(56, 189, 248, 0.1);
    }

    .alert {
      background: rgba(239, 68, 68, 0.15);
      color: #ff8a8a;
      padding: 14px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 14px;
      border: 1px solid var(--red);
      text-align: center;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
  </style>
</head>

<body>

  <nav class="navbar">
    <a href="index.php" class="navbar-logo">
      <div class="logo-pin">🏠</div>
      <span style="color:var(--red)">boarding</span><span>rooms</span>
    </a>
    <a href="register.php" class="btn-outline-nav">Create Account</a>
  </nav>

  <div class="auth-wrap">
    <div class="auth-box">
      <div class="auth-header">
        <div class="auth-icon-circle">
          <i class="fa-solid fa-user-lock"></i>
        </div>
        <h1>Welcome Back</h1>
        <p>Sign in to manage your bookings</p>
      </div>

      <?php if ($error): ?>
        <div class="alert">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST">

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="input-wrapper">
            <input type="email" name="email" class="form-control" placeholder="you@email.com" required
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <i class="fa-solid fa-envelope input-icon"></i>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" style="display:flex; justify-content:space-between;">
            Password
            <a href="#" style="font-size:12px; color:var(--red); text-decoration:none; font-weight:600;">Forgot?</a>
          </label>
          <div class="input-wrapper">
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            <i class="fa-solid fa-lock input-icon"></i>
          </div>
        </div>

        <button type="submit" class="btn-submit">
          Sign In <i class="fa-solid fa-arrow-right-to-bracket" style="margin-left: 5px;"></i>
        </button>
      </form>

      <div class="auth-divider"><span>or</span></div>

      <div class="admin-portal">
        <a href="admin_login.php"><i class="fa-solid fa-shield-halved"></i> Admin Login Portal</a>
      </div>

      <div class="auth-footer">
        Don't have an account? <a href="register.php">Create one free</a>
      </div>

    </div>
  </div>

</body>

</html>