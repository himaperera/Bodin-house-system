<?php
session_start();
require_once 'db.php';

// Fetch all available rooms from the database
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE status = 'available' ORDER BY created_at DESC");
$stmt->execute();
$rooms = $stmt->fetchAll();

// Check if user is logged in for Navbar rendering
$is_logged_in = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Available Rooms — BoardingRooms</title>

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

    /* --- Page Header --- */
    .page-header {
      padding: 50px 40px 30px;
      max-width: 1300px;
      margin: 0 auto;
      text-align: center;
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

    .page-header h1 {
      font-size: 2.5rem;
      font-weight: 900;
      margin-bottom: 10px;
      color: #fff;
    }

    .page-header h1 span {
      color: var(--blue-accent);
    }

    .page-header p {
      color: var(--text-muted);
      font-size: 1.1rem;
    }

    /* --- Room Grid (4 columns) --- */
    .room-container {
      padding: 0 40px 60px;
      max-width: 1300px;
      margin: 0 auto;
      flex: 1;
    }

    .room-grid {
      display: grid;
      /* Forces 4 columns on large screens */
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
    }

    /* --- Compact Room Card --- */
    .room-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid var(--glass-border);
      border-radius: 16px;
      overflow: hidden;
      transition: 0.4s;
      display: flex;
      flex-direction: column;
    }

    .room-card:hover {
      transform: translateY(-8px);
      background: rgba(255, 255, 255, 0.1);
      border-color: var(--blue-accent);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
    }

    .card-image-wrapper {
      height: 160px;
      /* Smaller height for compact card */
      position: relative;
    }

    .card-image-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .card-content {
      padding: 20px;
      display: flex;
      flex-direction: column;
      flex-grow: 1;
    }

    .card-top-info {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 10px;
    }

    .floor-badge {
      background: rgba(56, 189, 248, 0.15);
      color: var(--blue-accent);
      padding: 4px 8px;
      border-radius: 6px;
      font-size: 11px;
      font-weight: 800;
    }

    .cap-badge {
      color: var(--text-muted);
      font-size: 12px;
      font-weight: 600;
    }

    .room-title {
      font-size: 18px;
      font-weight: 800;
      margin-bottom: 5px;
      color: #fff;
    }

    .room-num {
      color: var(--text-muted);
      font-size: 13px;
      margin-bottom: 12px;
    }

    .amenities-list {
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
      margin-bottom: 15px;
    }

    .amenity-tag {
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid var(--glass-border);
      color: var(--text-muted);
      padding: 3px 8px;
      border-radius: 4px;
      font-size: 11px;
    }

    .card-bottom {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-top: auto;
      padding-top: 15px;
      border-top: 1px dashed var(--glass-border);
    }

    .price-text {
      font-size: 18px;
      font-weight: 800;
      color: #fff;
    }

    .price-mo {
      font-size: 11px;
      color: var(--text-muted);
      font-weight: normal;
    }

    .btn-book {
      background: var(--red);
      color: white;
      text-decoration: none;
      padding: 8px 16px;
      border-radius: 8px;
      font-weight: 700;
      font-size: 13px;
      transition: 0.3s;
      border: none;
    }

    .btn-book:hover {
      background: #dc2626;
      transform: scale(1.05);
      box-shadow: 0 5px 15px rgba(239, 68, 68, 0.4);
    }

    /* --- Footer --- */
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

    /* Responsive Grid adjustments */
    @media (max-width: 1024px) {
      .room-grid {
        grid-template-columns: repeat(3, 1fr);
      }
    }

    @media (max-width: 768px) {
      .room-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 15px;
      }

      .room-container {
        padding: 0 20px 40px;
      }
    }

    @media (max-width: 480px) {
      .room-grid {
        grid-template-columns: 1fr;
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
      <a href="rooms.php" class="active">Available Rooms</a>
      <?php if ($is_logged_in): ?>
        <a href="my_bookings.php">My Bookings</a>
        <a href="user_profile.php">Profile</a>
      <?php endif; ?>
    </div>
    <div class="navbar-actions">
      <?php if ($is_logged_in): ?>
        <a href="logout.php" class="btn-outline-nav">Logout</a>
      <?php else: ?>
        <a href="login.php" class="btn-outline-nav">Login</a>
        <a href="register.php" class="btn-outline-nav" style="background:var(--red); border:none;">Sign Up</a>
      <?php endif; ?>
    </div>
  </nav>

  <header class="page-header">
    <h1>Available <span>Boarding Rooms</span></h1>
    <p>Find the perfect comfortable stay tailored for smart students.</p>
  </header>

  <div class="room-container">
    <div class="room-grid">

      <?php if (count($rooms) > 0): ?>
        <?php foreach ($rooms as $index => $room):
          // Default image if DB doesn't have one
          $img_url = !empty($room['image_url']) ? $room['image_url'] : 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=400&q=80';
          ?>
          <div class="room-card" style="animation: fadeIn 0.5s ease forwards; animation-delay: <?= $index * 0.05 ?>s;">
            <div class="card-image-wrapper">
              <img src="<?= $img_url ?>" alt="Room Image">
            </div>

            <div class="card-content">

              <div class="card-top-info">
                <span class="floor-badge"><i class="fa-solid fa-layer-group"></i> Floor
                  <?= htmlspecialchars($room['floor']) ?>
                </span>
                <span class="cap-badge"><i class="fa-solid fa-users"></i> Cap:
                  <?= htmlspecialchars($room['capacity']) ?>
                </span>
              </div>

              <div class="room-title">
                <?= htmlspecialchars($room['room_type']) ?>
              </div>
              <div class="room-num"><i class="fa-solid fa-door-closed" style="color:var(--blue-accent);"></i> Room No:
                <?= htmlspecialchars($room['room_number']) ?>
              </div>

              <div class="amenities-list">
                <?php
                $tags = explode(',', $room['amenities']);
                // Display max 3 tags to keep card compact
                $display_tags = array_slice($tags, 0, 3);
                foreach ($display_tags as $tag):
                  if (trim($tag)):
                    ?>
                    <span class="amenity-tag">
                      <?= htmlspecialchars(trim($tag)) ?>
                    </span>
                  <?php
                  endif;
                endforeach;
                if (count($tags) > 3):
                  ?>
                  <span class="amenity-tag">+
                    <?= count($tags) - 3 ?>
                  </span>
                <?php endif; ?>
              </div>

              <div class="card-bottom">
                <div class="price-text">
                  Rs.
                  <?= number_format($room['price']) ?><span class="price-mo">/mo</span>
                </div>
                <a href="book_room.php?id=<?= $room['id'] ?>" class="btn-book">Book</a>
              </div>

            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div
          style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: rgba(255,255,255,0.05); border-radius: 20px; border: 1px dashed var(--glass-border);">
          <i class="fa-solid fa-house-circle-xmark"
            style="font-size: 50px; color: var(--text-muted); margin-bottom: 20px;"></i>
          <h3 style="color: #fff; margin-bottom: 10px;">No Rooms Available</h3>
          <p style="color: var(--text-muted);">Sorry, all rooms are currently occupied. Please check back later.</p>
        </div>
      <?php endif; ?>

    </div>
  </div>

  <footer class="site-footer">
    &copy; 2026 BoardingRooms. All Rights Reserved.
  </footer>

</body>

</html>