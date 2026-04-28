<?php
session_start();
require_once 'db.php';

// Fetch all rooms from the database
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE status = 'available' ORDER BY created_at DESC");
$stmt->execute();
$rooms = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Available Rooms — BoardingRooms</title>
  <style>
    :root {
      --main-bg: #111827;
      --card-bg: #1f2937;
      --accent-red: #ef4444;
      --accent-blue: #38bdf8;
      --text-main: #f8fafc;
      --text-muted: #94a3b8;
      --border-color: #374151;
    }

    body {
      background-color: var(--main-bg);
      background-image: radial-gradient(circle at top right, #1e293b, #111827);
      color: var(--text-main);
      font-family: 'Inter', system-ui, sans-serif;
      margin: 0;
      min-height: 100vh;
    }

    /* Standard Navbar */
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

    /* Room Grid Layout */
    .container {
      max-width: 1200px;
      margin: 50px auto;
      padding: 0 20px;
    }

    .header-section {
      margin-bottom: 40px;
    }

    .header-section h2 {
      font-size: 32px;
      font-weight: 900;
      margin: 0;
    }

    .header-section p {
      color: var(--text-muted);
      margin-top: 10px;
    }

    .room-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 30px;
    }

    /* Room Card Styling */
    .room-card {
      background: var(--card-bg);
      border: 1px solid var(--border-color);
      border-radius: 20px;
      overflow: hidden;
      transition: all 0.3s ease;
    }

    .room-card:hover {
      transform: translateY(-10px);
      border-color: var(--accent-blue);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
    }

    .room-image {
      width: 100%;
      height: 200px;
      background: #374151;
      /* Placeholder if no image */
      object-fit: cover;
    }

    .room-content {
      padding: 25px;
    }

    .room-price {
      color: var(--accent-blue);
      font-size: 20px;
      font-weight: 800;
      margin-bottom: 10px;
    }

    .room-title {
      font-size: 22px;
      font-weight: 800;
      margin-bottom: 8px;
    }

    .room-location {
      color: var(--text-muted);
      font-size: 14px;
      margin-bottom: 15px;
      display: flex;
      align-items: center;
      gap: 5px;
    }

    .amenities-list {
      display: flex;
      flex-wrap: wrap;
      gap: 8px;
      margin-bottom: 20px;
    }

    .amenity-tag {
      background: rgba(56, 189, 248, 0.1);
      color: var(--accent-blue);
      padding: 4px 10px;
      border-radius: 6px;
      font-size: 12px;
      font-weight: 600;
    }

    .btn-book {
      display: block;
      width: 100%;
      text-align: center;
      background: var(--accent-red);
      color: white;
      text-decoration: none;
      padding: 14px;
      border-radius: 12px;
      font-weight: 700;
      transition: 0.2s;
    }

    .btn-book:hover {
      background: #dc2626;
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
    <div style="display:flex; gap:15px; align-items:center;">
      <a href="my_bookings.php"
        style="color: var(--text-main); text-decoration: none; font-size: 14px; font-weight: 600;">My Bookings</a>
      <a href="logout.php" style="color: var(--text-muted); text-decoration: none; font-size: 14px;">Logout</a>
    </div>
  </nav>

  <div class="container">
    <div class="header-section">
      <h2>Available <span>Boarding Rooms</span></h2>
      <p>Find the perfect stay near your university.</p>
    </div>

    <div class="room-grid">
      <?php foreach ($rooms as $room): ?>
        <div class="room-card">
          <img
            src="<?= $room['image_url'] ?? 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=400&q=80' ?>"
            class="room-image" alt="Room Image">

          <div class="room-content">
            <div class="room-price">Rs.
              <?= number_format($room['price']) ?> / mo
            </div>
            <div class="room-title">
              <?= htmlspecialchars($room['room_type']) ?> — No.
              <?= htmlspecialchars($room['room_number']) ?>
            </div>
            <div class="room-location">📍 Floor
              <?= htmlspecialchars($room['floor']) ?>
            </div>

            <div class="amenities-list">
              <?php
              $tags = explode(',', $room['amenities']);
              foreach ($tags as $tag):
                if (trim($tag)):
                  ?>
                  <span class="amenity-tag">
                    <?= htmlspecialchars(trim($tag)) ?>
                  </span>
                <?php endif; endforeach; ?>
            </div>

            <a href="book.php?id=<?= $room['id'] ?>" class="btn-book">Book Now</a>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

</body>

</html>