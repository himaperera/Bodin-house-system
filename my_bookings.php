<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
$userId = $_SESSION['user_id'];

// Fetch Bookings with Room Details
$bookings_query = $pdo->prepare("
  SELECT b.*, r.room_number, r.room_type, r.price, r.floor, r.amenities
  FROM bookings b
  JOIN rooms r ON b.room_id = r.id
  WHERE b.user_id = ?
  ORDER BY b.created_at DESC
");
$bookings_query->execute([$userId]);
$bookings = $bookings_query->fetchAll();

// Fetch User Info
$user_query = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$user_query->execute([$userId]);
$user = $user_query->fetch();

$confirmed = array_filter($bookings, fn($b) => $b['status'] === 'confirmed');
$pending = array_filter($bookings, fn($b) => $b['status'] === 'pending');
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Bookings — BoardingRooms</title>
  <style>
    :root {
      /* Exact Colors from your Homepage Hero */
      --main-bg: #111827;
      /* Dark foundation */
      --nav-bg: #1a202c;
      /* Slightly lighter navy */
      --card-bg: #1f2937;
      /* Card color from the homepage hero */
      --accent-red: #ef4444;
      /* The "Explore Rooms" Red */
      --accent-blue: #38bdf8;
      /* The "Smart Students" Blue */
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --border-color: #374151;
      --success: #22c55e;
      --warning: #fbbf24;
    }

    body {
      background-color: var(--main-bg);
      /* This mimics the subtle dark gradient seen in your screenshot */
      background-image: radial-gradient(circle at top right, #1e293b, #111827);
      color: var(--text-main);
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      margin: 0;
      min-height: 100vh;
    }

    /* Navbar with Logo Styling */
    .page-navbar {
      background: rgba(17, 24, 39, 0.85);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--border-color);
      padding: 0 40px;
      height: 75px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 100;
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
      font-size: 28px;
      font-weight: 800;
      letter-spacing: -1px;
    }

    .btn-sm {
      padding: 9px 20px;
      border-radius: 10px;
      text-decoration: none;
      font-size: 14px;
      font-weight: 600;
      transition: 0.2s;
    }

    .btn-primary {
      background: var(--accent-red);
      color: #fff;
    }

    .btn-outline {
      border: 1px solid var(--border-color);
      color: #fff;
      background: rgba(255, 255, 255, 0.05);
    }

    /* Dashboard Container */
    .my-page {
      max-width: 1000px;
      margin: 40px auto;
      padding: 0 20px;
    }

    .user-header {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 24px;
      padding: 35px;
      display: flex;
      align-items: center;
      gap: 25px;
      margin-bottom: 40px;
      box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.4);
    }

    .avatar-lg {
      width: 75px;
      height: 75px;
      border-radius: 50%;
      background: var(--accent-blue);
      color: #000;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      font-weight: 900;
    }

    .user-stats {
      margin-left: auto;
      display: flex;
      gap: 40px;
    }

    .stat-val {
      font-size: 28px;
      font-weight: 900;
      line-height: 1;
    }

    .stat-label {
      font-size: 12px;
      color: var(--text-muted);
      text-transform: uppercase;
      margin-top: 6px;
      letter-spacing: 0.5px;
    }

    /* Booking Cards Styling */
    .booking-card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 18px;
      padding: 30px;
      margin-bottom: 24px;
      display: flex;
      gap: 30px;
      transition: all 0.2s ease;
    }

    .booking-card:hover {
      border-color: var(--accent-blue);
      box-shadow: 0 10px 20px rgba(56, 189, 248, 0.1);
    }

    .room-icon {
      width: 65px;
      height: 65px;
      border-radius: 14px;
      background: rgba(56, 189, 248, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 28px;
      color: var(--accent-blue);
    }

    .booking-main {
      flex: 1;
    }

    .room-title {
      font-size: 20px;
      font-weight: 800;
      margin-bottom: 6px;
    }

    .room-meta {
      font-size: 15px;
      color: var(--text-muted);
      margin-bottom: 20px;
    }

    .date-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }

    .date-label {
      font-size: 11px;
      font-weight: 800;
      color: var(--accent-blue);
      text-transform: uppercase;
      letter-spacing: 0.8px;
    }

    .date-val {
      font-size: 15px;
      font-weight: 600;
      margin-top: 5px;
    }

    .booking-status {
      text-align: right;
      min-width: 140px;
    }

    .status-pill {
      display: inline-block;
      padding: 7px 14px;
      border-radius: 9px;
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
    }

    .status-confirmed {
      background: rgba(34, 197, 94, 0.15);
      color: var(--success);
      border: 1px solid rgba(34, 197, 94, 0.3);
    }

    .status-pending {
      background: rgba(251, 191, 36, 0.15);
      color: var(--warning);
      border: 1px solid rgba(251, 191, 36, 0.3);
    }

    .price-tag {
      font-size: 24px;
      font-weight: 900;
      color: var(--accent-red);
      margin-top: 15px;
    }
  </style>
</head>

<body>

  <nav class="page-navbar">
    <a href="index.php" class="logo-wrap">
      <div class="logo-pin"><span>🏠</span></div>
      <div class="logo-text">
        <span style="color: var(--accent-red);">boarding</span>
        <span style="color: #fff;">rooms</span>
      </div>
    </a>
    <div style="display:flex; gap:12px; align-items:center;">
      <a href="rooms.php" class="btn-sm btn-outline">Browse Rooms</a>
      <a href="logout.php"
        style="color: var(--text-muted); text-decoration:none; font-size: 14px; font-weight:600; margin-left:10px;">Logout</a>
    </div>
  </nav>

  <div class="my-page">
    <div class="user-header">
      <div class="avatar-lg">
        <?= strtoupper(substr($user['name'], 0, 1)) ?>
      </div>
      <div>
        <h2 style="margin:0; font-size: 24px;">
          <?= htmlspecialchars($user['name']) ?>
        </h2>
        <p style="margin:6px 0 0; color: var(--text-muted); font-size: 15px;">Logged in as:
          <?= htmlspecialchars($user['email']) ?>
        </p>
      </div>
      <div class="user-stats">
        <div class="stat-item">
          <div class="stat-val">
            <?= count($bookings) ?>
          </div>
          <div class="stat-label">Total</div>
        </div>
        <div class="stat-item">
          <div class="stat-val" style="color: var(--success);">
            <?= count($confirmed) ?>
          </div>
          <div class="stat-label">Active</div>
        </div>
        <div class="stat-item">
          <div class="stat-val" style="color: var(--warning);">
            <?= count($pending) ?>
          </div>
          <div class="stat-label">Pending</div>
        </div>
      </div>
    </div>

    <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:30px;">
      <h3
        style="font-size: 24px; font-weight: 800; margin:0; border-left: 4px solid var(--accent-red); padding-left: 15px;">
        My Bookings</h3>
      <a href="book.php" class="btn-sm btn-primary">+ New Booking</a>
    </div>

    <?php if (empty($bookings)): ?>
      <div
        style="background: var(--card-bg); border-radius: 20px; padding: 80px; text-align: center; border: 1px dashed var(--border-color);">
        <div style="font-size: 48px; margin-bottom: 20px;">🗓️</div>
        <h3 style="font-size: 22px;">No active bookings</h3>
        <p style="color: var(--text-muted); margin-bottom: 30px;">Start your journey by finding a comfortable place to
          stay.</p>
        <a href="rooms.php" class="btn-sm btn-primary">Browse Available Rooms</a>
      </div>
    <?php else: ?>
      <div class="bookings-list">
        <?php foreach ($bookings as $b):
          $statusClass = $b['status'] === 'confirmed' ? 'status-confirmed' : 'status-pending';
          $months = max(1, round((strtotime($b['check_out']) - strtotime($b['check_in'])) / (30 * 24 * 3600)));
          ?>
          <div class="booking-card">
            <div class="room-icon">🏠</div>
            <div class="booking-main">
              <div class="room-title">Room
                <?= htmlspecialchars($b['room_number']) ?> —
                <?= htmlspecialchars($b['room_type']) ?>
              </div>
              <div class="room-meta">Floor
                <?= htmlspecialchars($b['floor']) ?> ·
                <?= htmlspecialchars($b['amenities']) ?>
              </div>

              <div class="date-grid">
                <div class="date-box">
                  <span class="date-label">Arrival</span>
                  <span class="date-val">
                    <?= date('d M Y', strtotime($b['check_in'])) ?>
                  </span>
                </div>
                <div class="date-box">
                  <span class="date-label">Departure</span>
                  <span class="date-val">
                    <?= date('d M Y', strtotime($b['check_out'])) ?>
                  </span>
                </div>
                <div class="date-box">
                  <span class="date-label">Duration</span>
                  <span class="date-val">
                    <?= $months ?> Month
                    <?= $months > 1 ? 's' : '' ?>
                  </span>
                </div>
              </div>
            </div>
            <div class="booking-status">
              <span class="status-pill <?= $statusClass ?>">
                <?= ucfirst($b['status']) ?>
              </span>
              <div class="price-tag">Rs.
                <?= number_format($b['amount']) ?>
              </div>
              <div
                style="font-size: 11px; color: var(--text-muted); margin-top: 5px; font-weight:700; text-transform:uppercase;">
                Grand Total</div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

</body>

</html>