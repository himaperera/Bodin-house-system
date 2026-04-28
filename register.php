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
  <style>
    /* Global Modern Dark Theme */
    :root {
      --dark-bg: #0f172a;
      /* Deep navy background */
      --card-bg: #1e293b;
      /* Slightly lighter card background */
      --accent-red: #ef4444;
      /* Logo and Primary Button Red */
      --accent-blue: #38bdf8;
      /* "Smart Students" blue highlight */
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --border-color: #334155;
    }

    body {
      background-color: var(--dark-bg);
      color: var(--text-main);
      font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
      margin: 0;
      line-height: 1.5;
    }

    /* Header & Logo Styling */
    .auth-nav {
      background: rgba(15, 23, 42, 0.9);
      backdrop-filter: blur(8px);
      border-bottom: 1px solid var(--border-color);
      padding: 0 40px;
      height: 50px;
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
      width: 35px;
      height: 35px;
      background: #ef4444;
      /* This specific radius creates the flat-bottom teardrop from your 1st image */
      border-radius: 50% 50% 50% 10%;
      transform: rotate(-0deg);
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 8px;
    }

    .logo-pin span {
      transform: rotate(0deg);
      /* Flips the house back upright */
      font-size: 17px;
      line-height: 1;
      margin-top: -2px;
      /* Fine-tuning the house position */
    }

    .logo-text {
      font-size: 28px;
      /* Larger to match the screenshot */
      font-weight: 800;
      letter-spacing: -1px;
      display: flex;
      gap: 8px;
    }




    /* Registration Form Layout */
    .auth-page {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 60px 20px;
      min-height: calc(100vh - 120px);
    }

    .auth-box {
      background: var(--card-bg);
      border-radius: 20px;
      border: 1px solid var(--border-color);
      padding: 40px;
      width: 100%;
      max-width: 480px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .auth-header h1 {
      font-size: 28px;
      font-weight: 800;
      margin-bottom: 8px;
      color: #fff;
    }

    .auth-header h1 span {
      color: var(--accent-blue);
    }

    .auth-header p {
      color: var(--text-muted);
      margin-bottom: 30px;
      font-size: 15px;
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
      color: var(--text-main);
    }

    .form-control {
      background: #0f172a;
      border: 1px solid var(--border-color);
      color: #fff;
      border-radius: 10px;
      padding: 12px 16px;
      width: 100%;
      box-sizing: border-box;
      font-size: 15px;
      transition: all 0.2s ease;
    }

    .form-control:focus {
      border-color: var(--accent-blue);
      outline: none;
      box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
    }

    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }

    /* Buttons & Alerts */
    .btn-primary {
      background: var(--accent-red);
      color: white;
      border: none;
      border-radius: 10px;
      padding: 14px;
      width: 100%;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      transition: 0.2s;
      margin-top: 10px;
    }

    .btn-primary:hover {
      background: #dc2626;
      transform: translateY(-1px);
    }

    .btn-outline {
      border: 1px solid var(--border-color);
      color: #fff;
      padding: 8px 16px;
      border-radius: 8px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
    }

    .alert {
      padding: 12px 16px;
      border-radius: 10px;
      margin-bottom: 20px;
      font-size: 14px;
      border: 1px solid transparent;
    }

    .alert-danger {
      background: rgba(239, 68, 68, 0.1);
      color: #fca5a5;
      border-color: rgba(239, 68, 68, 0.3);
    }

    .alert-success {
      background: rgba(34, 197, 94, 0.1);
      color: #86efac;
      border-color: rgba(34, 197, 94, 0.3);
    }

    .auth-footer {
      text-align: center;
      margin-top: 25px;
      color: var(--text-muted);
      font-size: 14px;
    }

    .auth-footer a {
      color: var(--accent-blue);
      text-decoration: none;
      font-weight: 700;
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

    <a href="index.php" class="btn-outline">Home</a>
  </nav>

  <div class="auth-page">
    <div class="auth-box">
      <div class="auth-header">
        <h1>Join <span>& Find Boarding Rooms</span></h1>
        <p>Create an account to book your living area.</p>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <?php if ($success): ?>
        <div class="alert alert-success">
          <?= $success ?> <a href="login.php" style="color:#fff; text-decoration: underline;">Login now →</a>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input type="text" name="name" class="form-control" placeholder="e.g. John Smith" required
            value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" name="email" class="form-control" placeholder="name@university.com" required
            value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label class="form-label">Phone Number</label>
          <input type="tel" name="phone" class="form-control" placeholder="+94 7x xxx xxxx"
            value="<?= htmlspecialchars($_POST['phone'] ?? '') ?>">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
          </div>
          <div class="form-group">
            <label class="form-label">Confirm</label>
            <input type="password" name="confirm_password" class="form-control" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn-primary">Create Your Account</button>
      </form>

      <div class="auth-footer">
        Already have an account? <a href="login.php">Sign in here</a>
      </div>
    </div>
  </div>

</body>

</html>