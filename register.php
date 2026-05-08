<?php
session_start();
if (isset($_SESSION['user_id'])) {
  header('Location: my_bookings.php');
  exit;
}
$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once 'db.php';
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $password = $_POST['password'] ?? '';
  $confirm = $_POST['confirm_password'] ?? '';

  if (strlen($name) < 2)
    $error = 'Please enter your full name.';
  elseif (!filter_var($email, FILTER_VALIDATE_EMAIL))
    $error = 'Please enter a valid email address.';
  elseif (strlen($password) < 6)
    $error = 'Password must be at least 6 characters.';
  elseif ($password !== $confirm)
    $error = 'Passwords do not match.';
  else {
    $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $check->execute([$email]);
    if ($check->fetch()) {
      $error = 'This email is already registered. Please login.';
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (name, email, phone, password) VALUES (?,?,?,?)");
      $stmt->execute([$name, $email, $phone, $hash]);
      $success = 'Account created successfully!';
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register — BoardingRooms</title>

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

    /* --- Registration Box Layout --- */
    .auth-page {
      flex: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 20px;
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
      max-width: 500px;
      /* Slightly wider for the two-column row */
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
      font-size: 26px;
      box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
    }

    .auth-header h1 {
      font-size: 28px;
      font-weight: 800;
      margin: 0 0 8px 0;
      color: #fff;
    }

    .auth-header h1 span {
      color: var(--blue-accent);
    }

    .auth-header p {
      color: var(--text-muted);
      font-size: 15px;
      margin: 0;
    }

    /* Form Elements */
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

    .input-wrapper {
      position: relative;
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

    .input-icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--text-muted);
      font-size: 14px;
      transition: 0.3s;
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

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    /* Buttons & Alerts */
    .btn-primary {
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

    .btn-primary:hover {
      transform: translateY(-2px);
      background: #fff;
      box-shadow: 0 10px 20px rgba(56, 189, 248, 0.3);
    }

    .alert {
      padding: 14px;
      border-radius: 12px;
      margin-bottom: 20px;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .alert-danger {
      background: rgba(239, 68, 68, 0.15);
      color: #ff8a8a;
      border: 1px solid var(--red);
    }

    .alert-success {
      background: rgba(16, 185, 129, 0.15);
      color: var(--green-accent);
      border: 1px solid var(--green-accent);
    }

    .alert-success a {
      color: #fff;
      font-weight: bold;
      margin-left: auto;
      /* Pushes the login link to the right */
    }

    .auth-footer {
      text-align: center;
      margin-top: 25px;
      color: var(--text-muted);
      font-size: 14px;
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

    @media (max-width: 600px) {
      .form-row {
        grid-template-columns: 1fr;
        gap: 0;
      }
    }
  </style>
</head>

<body>

  <nav class="navbar">
    <a href="index.php" class="navbar-logo">
      <div class="logo-pin">🏠</div>
      <span style="color:var(--red)">boarding</span><span>rooms</span>
    </a>
    <a href="login.php" class="btn-outline-nav">Sign In</a>
  </nav>

  <div class="auth-page">
    <div class="auth-box">
      <div class="auth-header">
        <div class="auth-icon-circle">
          <i class="fa-solid fa-user-plus"></i>
        </div>
        <h1>Join <span>& Find Rooms</span></h1>
        <p>Create an account to book your perfect living space.</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <i class="fa-solid fa-triangle-exclamation"></i>
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <i class="fa-solid fa-circle-check"></i>
          <?= $success ?>
          <a href="login.php">Login now <i class="fa-solid fa-arrow-right"></i></a>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <div class="input-wrapper">
            <input type="text" name="name" class="form-control" placeholder="e.g. Supun Perera" required
              value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            <i class="fa-solid fa-user input-icon"></i>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="input-wrapper">
            <input type="email" name="email" class="form-control" placeholder="name@university.com" required
              value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <i class="fa-solid fa-envelope input-icon"></i>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <div class="input-wrapper">
            <input type="tel" name="phone" class="form-control" placeholder="+94 7x xxx xxxx"
              value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
            <i class="fa-solid fa-phone input-icon"></i>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password</label>
            <div class="input-wrapper">
              <input type="password" name="password" class="form-control" placeholder="••••••••" required>
              <i class="fa-solid fa-lock input-icon"></i>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm Password</label>
            <div class="input-wrapper">
              <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
              <i class="fa-solid fa-check-double input-icon"></i>
            </div>
          </div>
        </div>

        <button type="submit" class="btn-primary">
          Create Account <i class="fa-solid fa-user-check" style="margin-left: 5px;"></i>
        </button>
      </form>

      <div class="auth-footer">
        Already have an account? <a href="login.php">Sign in here</a>
      </div>
    </div>
  </div>

</body>

</html>