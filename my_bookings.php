<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}
require_once 'db.php';
$userId = $_SESSION['user_id'];
// Fetch User Notifications
$notif_query = $pdo->prepare("SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$notif_query->execute([$userId]);
$notifications = $notif_query->fetchAll();

// Fetch User's Posted Ads
$my_ads_query = $pdo->prepare("SELECT * FROM rooms WHERE submitted_by = ? ORDER BY created_at DESC");
$my_ads_query->execute([$userId]);
$my_ads = $my_ads_query->fetchAll();

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

// Fetch Recommended Rooms (Available rooms NOT booked by this user)
$rec_query = $pdo->prepare("
  SELECT * FROM rooms 
  WHERE status = 'available' 
  AND id NOT IN (SELECT room_id FROM bookings WHERE user_id = ?) 
  ORDER BY RAND() LIMIT 4
");
$rec_query->execute([$userId]);
$recommendations = $rec_query->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>My Dashboard — BoardingRooms</title>

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
      --warning: #fbbf24;
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

    .navbar-links a {
      padding: 8px 16px;
      font-size: 14px;
      color: var(--text-muted);
      text-decoration: none;
      transition: 0.3s;
    }

    .navbar-links a:hover,
    .navbar-links a.active {
      color: #fff;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 8px;
      font-weight: bold;
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

    /* Dashboard Container */
    .my-page {
      max-width: 1200px;
      margin: 40px auto;
      padding: 0 20px;
      flex: 1;
      width: 100%;
      box-sizing: border-box;
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

    .user-header {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(20px);
      border: 1px solid var(--glass-border);
      border-radius: 24px;
      padding: 35px;
      display: flex;
      align-items: center;
      flex-wrap: wrap;
      gap: 25px;
      margin-bottom: 40px;
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
    }

    .avatar-lg {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      background: linear-gradient(135deg, var(--blue-accent), #8b5cf6);
      color: #fff;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 32px;
      font-weight: 900;
      border: 3px solid rgba(255, 255, 255, 0.1);
    }

    .user-info {
      flex: 1;
      min-width: 200px;
    }

    .user-stats {
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
    }

    .stat-item {
      text-align: center;
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

    /* Action Bar */
    .action-bar {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      flex-wrap: wrap;
      gap: 15px;
    }

    .btn-primary {
      background: var(--red);
      color: white;
      padding: 10px 20px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 700;
      transition: 0.3s;
      border: none;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-primary:hover {
      background: #dc2626;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
    }

    .btn-post-ad {
      background: var(--blue-accent);
      color: #000;
      padding: 10px 20px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 800;
      transition: 0.3s;
      display: inline-flex;
      align-items: center;
      gap: 8px;
    }

    .btn-post-ad:hover {
      background: #fff;
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(56, 189, 248, 0.4);
    }

    /* Booking Cards Styling */
    .booking-card {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(10px);
      border: 1px solid var(--glass-border);
      border-radius: 18px;
      padding: 25px;
      margin-bottom: 20px;
      display: flex;
      flex-wrap: wrap;
      gap: 25px;
      transition: all 0.3s ease;
    }

    .booking-card:hover {
      border-color: var(--blue-accent);
      background: rgba(255, 255, 255, 0.06);
      transform: translateY(-3px);
    }

    .room-icon {
      width: 60px;
      height: 60px;
      border-radius: 14px;
      background: rgba(56, 189, 248, 0.1);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 24px;
      color: var(--blue-accent);
      border: 1px solid rgba(56, 189, 248, 0.2);
    }

    .booking-main {
      flex: 1;
      min-width: 300px;
    }

    .room-title {
      font-size: 20px;
      font-weight: 800;
      margin-bottom: 6px;
    }

    .room-meta {
      font-size: 14px;
      color: var(--text-muted);
      margin-bottom: 15px;
    }

    .date-grid {
      display: flex;
      gap: 30px;
      flex-wrap: wrap;
    }

    .date-label {
      font-size: 11px;
      font-weight: 800;
      color: var(--blue-accent);
      text-transform: uppercase;
      letter-spacing: 0.8px;
      display: block;
    }

    .date-val {
      font-size: 15px;
      font-weight: 600;
      margin-top: 5px;
      display: block;
    }

    .booking-status {
      text-align: right;
      min-width: 140px;
    }

    .status-pill {
      display: inline-block;
      padding: 6px 12px;
      border-radius: 50px;
      font-size: 12px;
      font-weight: 800;
      text-transform: uppercase;
    }

    .status-confirmed {
      background: rgba(16, 185, 129, 0.15);
      color: var(--green-accent);
      border: 1px solid var(--green-accent);
    }

    .status-pending {
      background: rgba(251, 191, 36, 0.15);
      color: var(--warning);
      border: 1px solid var(--warning);
    }

    .status-cancelled {
      background: rgba(239, 68, 68, 0.15);
      color: #ff8a8a;
      border: 1px solid var(--red);
    }

    .price-tag {
      font-size: 22px;
      font-weight: 900;
      color: #fff;
      margin-top: 15px;
    }

    /* --- Recommendations Section --- */
    .recommendations-section {
      margin-top: 60px;
      border-top: 1px dashed var(--glass-border);
      padding-top: 40px;
    }

    .room-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
      gap: 20px;
      margin-top: 20px;
    }

    .rec-card {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      border-radius: 16px;
      overflow: hidden;
      transition: 0.3s;
    }

    .rec-card:hover {
      transform: translateY(-5px);
      border-color: var(--blue-accent);
      background: rgba(255, 255, 255, 0.08);
    }

    .rec-img {
      width: 100%;
      height: 140px;
      object-fit: cover;
    }

    .rec-content {
      padding: 15px;
    }

    .rec-title {
      font-size: 16px;
      font-weight: bold;
      margin-bottom: 5px;
    }

    .rec-price {
      color: var(--blue-accent);
      font-weight: 800;
      font-size: 18px;
      margin-bottom: 10px;
    }

    /* Footer */
    .site-footer {
      border-top: 1px solid var(--glass-border);
      background: rgba(2, 6, 23, 0.7);
      backdrop-filter: blur(15px);
      padding: 30px 40px;
      text-align: center;
      color: var(--text-muted);
      font-size: 13px;
      margin-top: auto;
    }

    @media (max-width: 600px) {
      .booking-card {
        flex-direction: column;
        text-align: center;
      }

      .room-icon {
        margin: 0 auto;
      }

      .date-grid {
        justify-content: center;
      }

      .booking-status {
        text-align: center;
        margin-top: 15px;
      }

      .user-stats {
        justify-content: center;
        width: 100%;
        margin-top: 20px;
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
    <div class="navbar-links">
      <a href="index.php">Home</a>
      <a href="rooms.php">Available Rooms</a>
      <a href="my_bookings.php" class="active">Dashboard</a>
      <a href="user_profile.php">Profile</a>
    </div>
    <div class="navbar-actions">
      <a href="logout.php" class="btn-outline-nav"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
    </div>
  </nav>

  <div class="my-page">

    <div class="user-header">
      <div class="avatar-lg">
        <?= strtoupper(substr($user['name'], 0, 1)) ?>
      </div>
      <div class="user-info">
        <h2 style="margin:0; font-size: 24px; color: #fff;">
          <?= htmlspecialchars($user['name']) ?>
        </h2>
        <p style="margin:6px 0 0; color: var(--text-muted); font-size: 14px;"><i class="fa-regular fa-envelope"></i>
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
          <div class="stat-val" style="color: var(--green-accent);">
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

    <div class="action-bar">
      <h3 style="font-size: 22px; font-weight: 800; margin:0; border-left: 4px solid var(--red); padding-left: 15px;">My
        Bookings</h3>
      <div style="display: flex; gap: 10px;">
        <a href="submit_room.php" class="btn-post-ad"><i class="fa-solid fa-bullhorn"></i> Post an Ad</a>
        <a href="rooms.php" class="btn-primary"><i class="fa-solid fa-magnifying-glass"></i> Find Rooms</a>
      </div>
    </div>

    <?php if (empty($bookings)): ?>
      <div
        style="background: rgba(255,255,255,0.03); border-radius: 20px; padding: 60px 20px; text-align: center; border: 1px dashed var(--glass-border);">
        <i class="fa-regular fa-calendar-xmark"
          style="font-size: 50px; color: var(--text-muted); margin-bottom: 20px;"></i>
        <h3 style="font-size: 22px; color: #fff; margin-bottom: 10px;">No active bookings</h3>
        <p style="color: var(--text-muted); margin-bottom: 25px;">You haven't booked any rooms yet. Start your journey by
          finding a comfortable place to stay.</p>
      </div>
    <?php else: ?>
      <div class="bookings-list">
        <?php foreach ($bookings as $b):
          $statusClass = 'status-pending';
          if ($b['status'] === 'confirmed')
            $statusClass = 'status-confirmed';
          if ($b['status'] === 'cancelled')
            $statusClass = 'status-cancelled';

          $months = max(1, round((strtotime($b['check_out']) - strtotime($b['check_in'])) / (30 * 24 * 3600)));
          ?>
          <div class="booking-card">
            <div class="room-icon"><i class="fa-solid fa-bed"></i></div>

            <div class="booking-main">
              <div class="room-title">Room
                <?= htmlspecialchars($b['room_number']) ?> —
                <?= htmlspecialchars($b['room_type']) ?>
              </div>
              <div class="room-meta"><i class="fa-solid fa-layer-group"></i> Floor
                <?= htmlspecialchars($b['floor']) ?> &nbsp;|&nbsp; <i class="fa-solid fa-list-check"></i>
                <?= htmlspecialchars($b['amenities']) ?>
              </div>

              <div class="date-grid">
                <div class="date-box">
                  <span class="date-label">Check-in</span>
                  <span class="date-val"><i class="fa-regular fa-calendar-check" style="color:var(--text-muted);"></i>
                    <?= date('d M Y', strtotime($b['check_in'])) ?>
                  </span>
                </div>
                <div class="date-box">
                  <span class="date-label">Check-out</span>
                  <span class="date-val"><i class="fa-regular fa-calendar-xmark" style="color:var(--text-muted);"></i>
                    <?= date('d M Y', strtotime($b['check_out'])) ?>
                  </span>
                </div>
                <div class="date-box">
                  <span class="date-label">Duration</span>
                  <span class="date-val"><i class="fa-regular fa-clock" style="color:var(--text-muted);"></i>
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

    <?php if (!empty($recommendations)): ?>
      <div class="recommendations-section">
        <h3 style="font-size: 20px; font-weight: 800; margin:0 0 10px 0; color: #fff;"><i class="fa-solid fa-star"
            style="color:var(--warning);"></i> Recommended For You</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin-bottom: 20px;">Based on available rooms near
          universities.</p>

        <div class="room-grid">
          <?php foreach ($recommendations as $rec): ?>
            <div class="rec-card">
              <img src="https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=400&q=80"
                class="rec-img" alt="Room Image">
              <div class="rec-content">
                <div class="rec-price">Rs.
                  <?= number_format($rec['price']) ?> <span
                    style="font-size:12px; font-weight:normal; color:var(--text-muted);">/mo</span>
                </div>
                <div class="rec-title">
                  <?= htmlspecialchars($rec['room_type']) ?>
                </div>
                <div style="color: var(--text-muted); font-size: 13px; margin-bottom: 15px;"><i
                    class="fa-solid fa-door-closed"></i> Room
                  <?= htmlspecialchars($rec['room_number']) ?>
                </div>
                <a href="book_room.php?id=<?= $rec['id'] ?>" class="btn-primary"
                  style="width:100%; justify-content:center; padding: 8px; font-size: 13px;">View Details</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endif; ?>

  </div>

  <footer class="site-footer">
    &copy; 2026 BoardingRooms. All Rights Reserved.
  </footer>

</body>

</html>