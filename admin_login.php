<?php
session_start();
if (isset($_SESSION['admin_id'])) {
  header('Location: admin_dashboard.php');
  exit;
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  require_once 'db.php';
  $username = trim($_POST['username'] ?? '');
  $password = $_POST['password'] ?? '';

  $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
  $stmt->execute([$username]);
  $admin = $stmt->fetch();

  // IMPORTANT: This only works if your DB password was created with password_hash()
  if ($admin && password_verify($password, $admin['password'])) {
    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_name'] = $admin['name'];
    header('Location: admin_dashboard.php');
    exit;
  } else {
    $error = 'Invalid admin credentials. Please try again.';
    sleep(1); // Security delay
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Admin Portal — BoardingRooms</title>
  <style>
    :root {
      --main-bg: #0b1120;
      --card-bg: #1e293b;
      --accent-red: #ef4444;
      --accent-blue: #38bdf8;
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --border-color: #334155;
    }

    body {
      background-color: var(--main-bg);
      background-image: radial-gradient(circle at bottom left, #1e293b, #0b1120);
      color: var(--text-main);
      font-family: 'Inter', system-ui, sans-serif;
      margin: 0;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
    }

    .admin-wrap {
      width: 100%;
      max-width: 420px;
      padding: 20px;
    }

    /* Teardrop Logo Styling */
    .logo-block {
      text-align: center;
      margin-bottom: 40px;
    }

    .logo-pin {
      width: 45px;
      height: 45px;
      background: var(--accent-red);
      border-radius: 50% 50% 50% 10%;
      transform: rotate(-45deg);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 20px;
      box-shadow: 0 10px 15px -3px rgba(239, 68, 68, 0.3);
    }

    .logo-pin span {
      transform: rotate(45deg);
      font-size: 24px;
    }

    .logo-text {
      font-size: 32px;
      font-weight: 800;
      letter-spacing: -1px;
    }

    .admin-card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 24px;
      padding: 40px;
      box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
    }

    .admin-header {
      text-align: center;
      margin-bottom: 30px;
    }

    .admin-header h2 {
      font-size: 24px;
      font-weight: 800;
      margin: 0;
    }

    .admin-header p {
      color: var(--accent-blue);
      font-size: 13px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 1px;
      margin-top: 5px;
    }

    /* Form Styling */
    .form-label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      color: var(--text-muted);
      margin-bottom: 8px;
    }

    .input-group {
      position: relative;
      margin-bottom: 20px;
    }

    .admin-input {
      width: 100%;
      background: #0f172a;
      border: 1px solid var(--border-color);
      color: #fff;
      padding: 14px 16px;
      border-radius: 12px;
      font-size: 15px;
      box-sizing: border-box;
      transition: border-color 0.2s;
    }

    .admin-input:focus {
      border-color: var(--accent-blue);
      outline: none;
    }

    .btn-admin {
      width: 100%;
      background: var(--accent-red);
      color: white;
      border: none;
      padding: 15px;
      border-radius: 12px;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      transition: all 0.2s;
      margin-top: 10px;
    }

    .btn-admin:hover {
      background: #dc2626;
      transform: translateY(-1px);
    }

    .alert-error {
      background: rgba(239, 68, 68, 0.1);
      border: 1px solid rgba(239, 68, 68, 0.2);
      color: #fca5a5;
      padding: 12px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 20px;
      text-align: center;
    }

    .footer-links {
      text-align: center;
      margin-top: 30px;
    }

    .footer-links a {
      color: var(--text-muted);
      text-decoration: none;
      font-size: 13px;
      transition: color 0.2s;
    }

    .footer-links a:hover {
      color: var(--text-main);
    }

    .security-tag {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      font-size: 11px;
      color: #475569;
      margin-top: 20px;
      text-transform: uppercase;
      font-weight: 700;
    }
  </style>
</head>

<body>

  <div class="admin-wrap">
    <div class="logo-block">
      <div class="logo-pin"><span>🏠</span></div>
      <div class="logo-text">
        <span style="color: var(--accent-red);">boarding</span>rooms
      </div>
    </div>

    <div class="admin-card">
      <div class="admin-header">
        <h2>Admin Login</h2>
        <p>Restricted Access</p>
      </div>

      <?php if ($error): ?>
        <div class="alert-error">
          <?= htmlspecialchars($error) ?>
        </div>
      <?php endif; ?>

      <form method="POST">
        <div class="form-group">
          <label class="form-label">Username</label>
          <div class="input-group">
            <input type="text" name="username" class="admin-input" placeholder="Admin username" required
              autocomplete="off" value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-group">
            <input type="password" name="password" class="admin-input" placeholder="••••••••" required>
          </div>
        </div>

        <button type="submit" class="btn-admin">Enter Admin Panel</button>
      </form>

      <div class="security-tag">
        🛡️ Encrypted Session Active
      </div>
    </div>

    <div class="footer-links">
      <a href="login.php">← Back to Tenant Portal</a>
    </div>
  </div>

</body>

</html>