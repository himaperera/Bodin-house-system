<?php
session_start();

// වෙනම ඇති db.php ගොනුව මෙතැනට සම්බන්ධ කිරීම
require_once 'db.php';

// --- Fetch Universities for Dropdown ---
$stmt_uni = $pdo->query("SELECT * FROM universities ORDER BY name ASC");
$universities = $stmt_uni->fetchAll(PDO::FETCH_ASSOC);

// --- Handle Search or Default Rooms ---
$search_uni = isset($_GET['university']) ? $_GET['university'] : '';
$section_title = "Featured Rooms";
$featured_rooms = [];

if (!empty($search_uni)) {
  // Search කරන ලද විශ්වවිද්‍යාලයට අදාළ බෝඩිං ලබාගැනීම
  $sql = "SELECT r.*, u.name as uni_name, u.location 
            FROM rooms r 
            JOIN universities u ON r.university_id = u.id 
            WHERE u.short_code = :short_code AND r.status = 'available'";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(['short_code' => $search_uni]);
  $featured_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
  $section_title = "Search Results";
} else {
  // Search කර නොමැති විට පෙන්වන සාමාන්‍ය බෝඩිං (Available පමණක්)
  $sql = "SELECT r.*, u.name as uni_name, u.location 
            FROM rooms r 
            LEFT JOIN universities u ON r.university_id = u.id 
            WHERE r.status = 'available' LIMIT 6";
  $stmt = $pdo->query($sql);
  $featured_rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>BoardingRooms — Transparent Glass Edition</title>

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
    }

    body {
      margin: 0;
      font-family: 'Inter', sans-serif;
      color: var(--text-main);
      background-color: var(--navy-deep);
      min-height: 100vh;
      position: relative;
    }

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
      backdrop-filter: blur(4px);
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

    .navbar-links a:hover {
      color: #fff;
      background: rgba(255, 255, 255, 0.1);
      border-radius: 8px;
    }

    /* --- Hero Section --- */
    .hero {
      padding: 80px 40px 100px 40px;
      /* Added bottom padding to accommodate search bar */
      text-align: center;
      animation: fadeIn 1s ease;
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

    .hero h1 {
      font-size: 3.5rem;
      font-weight: 900;
      margin-bottom: 20px;
      text-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
    }

    .hero h1 span {
      color: var(--blue-accent);
    }

    .hero-sub {
      color: var(--text-muted);
      max-width: 600px;
      margin: 0 auto 30px;
      font-size: 1.1rem;
    }

    /* --- New: University Search Bar --- */
    .search-wrapper {
      max-width: 800px;
      margin: -60px auto 40px auto;
      /* Pulls it up over the hero section */
      position: relative;
      z-index: 10;
      padding: 0 20px;
    }

    .search-glass-container {
      background: rgba(255, 255, 255, 0.08);
      backdrop-filter: blur(20px);
      border: 1px solid rgba(255, 255, 255, 0.2);
      border-radius: 100px;
      padding: 10px;
      box-shadow: 0 15px 35px rgba(0, 0, 0, 0.4);
      display: flex;
      align-items: center;
      justify-content: space-between;
    }

    .search-input-group {
      display: flex;
      align-items: center;
      gap: 15px;
      flex: 1;
      padding: 0 25px;
      color: var(--blue-accent);
      font-size: 20px;
    }

    .search-input-group select {
      width: 100%;
      background: transparent;
      border: none;
      color: #fff;
      font-size: 16px;
      font-family: 'Inter', sans-serif;
      outline: none;
      cursor: pointer;
      appearance: none;
    }

    .search-input-group select option {
      background: var(--navy-deep);
      color: #fff;
      padding: 15px;
    }

    .btn-search {
      background: var(--blue-accent);
      color: #000;
      border: none;
      padding: 15px 35px;
      border-radius: 50px;
      font-weight: 800;
      font-size: 15px;
      cursor: pointer;
      display: flex;
      align-items: center;
      gap: 10px;
      transition: 0.3s;
    }

    .btn-search:hover {
      background: #fff;
      transform: scale(1.05);
      box-shadow: 0 0 20px rgba(56, 189, 248, 0.5);
    }

    /* --- Room Grid & Cards --- */
    .room-container {
      padding: 20px 40px 40px;
      max-width: 1300px;
      margin: 0 auto;
    }

    .room-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 25px;
    }

    .room-card {
      background: rgba(255, 255, 255, 0.05);
      backdrop-filter: blur(10px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      overflow: hidden;
      transition: 0.4s;
    }

    .room-card:hover {
      transform: translateY(-10px);
      background: rgba(255, 255, 255, 0.1);
      border-color: var(--blue-accent);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
    }

    .card-image-wrapper {
      height: 180px;
      position: relative;
    }

    .card-image-wrapper img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }

    .card-content {
      padding: 20px;
    }

    .rating-num {
      background: var(--blue-accent);
      color: #000;
      padding: 4px 8px;
      border-radius: 6px;
      font-weight: 800;
    }

    .price-new {
      font-size: 24px;
      font-weight: 800;
      color: #fff;
    }

    /* --- Buttons --- */
    .btn-primary {
      background: var(--red);
      color: white;
      padding: 12px 28px;
      border-radius: 12px;
      text-decoration: none;
      font-weight: 700;
      transition: 0.3s;
      display: inline-block;
      border: none;
      cursor: pointer;
    }

    .btn-primary:hover {
      transform: scale(1.05);
      box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);
    }

    .btn-outline {
      border: 1px solid var(--glass-border);
      color: #fff;
      padding: 12px 28px;
      border-radius: 12px;
      text-decoration: none;
      transition: 0.3s;
    }

    .btn-outline:hover {
      background: rgba(255, 255, 255, 0.1);
    }

    /* --- How it Works Section --- */
    .how-it-works-container {
      padding: 60px 40px;
      max-width: 1300px;
      margin: 0 auto;
      text-align: center;
    }

    .steps-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 30px;
      margin-top: 40px;
    }

    .step-card {
      background: rgba(255, 255, 255, 0.03);
      backdrop-filter: blur(10px);
      border: 1px solid var(--glass-border);
      border-radius: 20px;
      padding: 40px 30px;
      transition: 0.4s;
    }

    .step-card:hover {
      background: rgba(255, 255, 255, 0.08);
      border-color: var(--blue-accent);
      transform: translateY(-5px);
    }

    .step-icon {
      font-size: 45px;
      margin-bottom: 20px;
    }

    /* --- Footer Section --- */
    .site-footer {
      border-top: 1px solid var(--glass-border);
      background: rgba(2, 6, 23, 0.7);
      backdrop-filter: blur(15px);
      padding: 50px 40px 20px;
      margin-top: 60px;
    }

    .footer-content {
      max-width: 1300px;
      margin: 0 auto;
      display: flex;
      flex-wrap: wrap;
      justify-content: space-between;
      gap: 40px;
    }

    .footer-section h3 {
      color: var(--blue-accent);
      margin-bottom: 20px;
      font-size: 18px;
    }

    .footer-section p,
    .footer-section a {
      color: var(--text-muted);
      text-decoration: none;
      font-size: 14px;
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 12px;
      transition: 0.3s;
    }

    .footer-section a:hover {
      color: #fff;
    }

    .social-icons {
      display: flex;
      gap: 15px;
    }

    .social-icons a {
      font-size: 20px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      width: 45px;
      height: 45px;
      background: rgba(255, 255, 255, 0.05);
      border-radius: 50%;
      border: 1px solid var(--glass-border);
      color: var(--text-muted);
      transition: 0.3s;
    }

    .social-icons a:hover {
      background: var(--blue-accent);
      border-color: var(--blue-accent);
      color: #000;
      transform: translateY(-3px);
    }

    .footer-bottom {
      text-align: center;
      margin-top: 40px;
      padding-top: 20px;
      border-top: 1px solid var(--glass-border);
      color: var(--text-muted);
      font-size: 13px;
    }

    /* Mobile Responsive for Search Bar */
    @media (max-width: 768px) {
      .search-glass-container {
        flex-direction: column;
        border-radius: 20px;
        padding: 15px;
        gap: 15px;
      }

      .btn-search {
        width: 100%;
        justify-content: center;
      }

      .search-input-group {
        width: 100%;
        padding: 10px;
      }
    }
  </style>
</head>

<body>

  <nav class="navbar">
    <div class="navbar-logo">
      <div class="logo-pin">🏠</div>
      <span style="color:var(--red)">boarding</span><span>rooms</span>
    </div>
    <div class="navbar-links">
      <a href="index.php">Home</a>
      <a href="rooms.php">Available Rooms</a>
      <a href="my_bookings.php">My Bookings</a>
      <a href="user_profile.php">Profile</a>
    </div>
    <div class="navbar-actions">
      <a href="login.php" class="btn-outline">Login</a>
      <a href="register.php" class="btn-primary">Sign Up</a>
    </div>
  </nav>

  <section class="hero">
    <div
      style="background: rgba(56, 189, 248, 0.15); color: var(--blue-accent); display: inline-block; padding: 5px 15px; border-radius: 50px; font-size: 12px; font-weight: 700; margin-bottom: 20px; border: 1px solid var(--blue-accent);">
      Your Space. Your Comfort. Your Choice
    </div>
    <h1>Modern Living for<br><span>Smart Students</span></h1>
    <p class="hero-sub">The ultimate boarding house management system. Find, book, and stay in the best locations across
      the island.</p>
  </section>

  <div class="search-wrapper">
    <form action="rooms.php" method="GET" class="search-glass-container">
      <div class="search-input-group">
        <i class="fa-solid fa-graduation-cap"></i>
        <select name="university" id="university" required>
          <option value="" disabled selected>Select your University / Institute...</option>
          <option value="nsbm">NSBM Green University</option>
          <option value="colombo">University of Colombo</option>
          <option value="peradeniya">University of Peradeniya</option>
          <option value="sjp">University of Sri Jayewardenepura</option>
          <option value="kelaniya">University of Kelaniya</option>
          <option value="moratuwa">University of Moratuwa</option>
          <option value="ruhuna">University of Ruhuna</option>
          <option value="jaffna">University of Jaffna</option>
          <option value="rajarata">Rajarata University</option>
          <option value="wayamba">Wayamba University</option>
          <option value="sabaragamuwa">Sabaragamuwa University</option>
          <option value="eastern">Eastern University</option>
          <option value="south-eastern">South Eastern University</option>
          <option value="uva-wellassa">Uva Wellassa University</option>
          <option value="visual-performing-arts">University of the Visual & Performing Arts</option>
          <option value="open-university">Open University of Sri Lanka</option>
          <option value="gampaha-wickramarachchi">Gampaha Wickramarachchi University</option>
          <option value="vavuniya">University of Vavuniya</option>
        </select>
      </div>
      <button type="submit" class="btn-search">
        <i class="fa-solid fa-magnifying-glass"></i> Search Rooms
      </button>
    </form>
  </div>

  <section class="room-container">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
      <h2 style="margin: 0; font-size: 28px;">Featured Rooms</h2>
      <a href="rooms.php" class="btn-outline" style="padding: 10px 20px; font-size: 14px;">See more ➔</a>
    </div>

    <div class="room-grid">
      <?php
      $rooms = [
        ['name' => 'Signature Studio - NSBM', 'price' => '18,500', 'rating' => '9.8', 'img' => 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=500'],
        ['name' => 'Royal Shared Loft - Malabe', 'price' => '14,000', 'rating' => '8.7', 'img' => 'https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&w=500'],
        ['name' => 'Eco-Living Villa', 'price' => '22,000', 'rating' => '9.2', 'img' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=500'],
        ['name' => 'Eco-Living Villa', 'price' => '22,000', 'rating' => '9.2', 'img' => 'https://images.unsplash.com/photo-1613490493576-7fde63acd811?auto=format&fit=crop&w=500']
      ];

      foreach ($rooms as $index => $room):
        ?>
        <div class="room-card" style="animation: fadeIn 0.5s ease forwards; animation-delay: <?= $index * 0.2 ?>s;">
          <div class="card-image-wrapper">
            <img src="<?= $room['img'] ?>" alt="Room Image">
          </div>
          <div class="card-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
              <span class="rating-num">
                <?= $room['rating'] ?>
              </span>
              <span
                style="color: var(--blue-accent); font-size: 11px; font-weight: 800; letter-spacing: 1px;">EXCLUSIVE</span>
            </div>
            <h3 style="margin-bottom: 8px; font-size: 18px;">
              <?= $room['name'] ?>
            </h3>
            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px;">Western Province • Premium Location
            </p>
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <span class="price-new">LKR
                <?= $room['price'] ?>
              </span>
              <a href="rooms.php" class="btn-primary" style="padding: 8px 16px; font-size: 12px;">Book Now</a>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="how-it-works-container" id="how-it-works">
    <h2 style="font-size: 2.5rem; margin-bottom: 10px;">How to Register & Book</h2>
    <p style="color: var(--text-muted);">Get your perfect room in just three simple steps.</p>

    <div class="steps-grid">
      <div class="step-card">
        <div class="step-icon">📝</div>
        <h3>1. Create an Account</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin-top: 10px; line-height: 1.6;">Click on Sign Up and
          fill in your details to create a free student profile in our system.</p>
      </div>
      <div class="step-card">
        <div class="step-icon">🔍</div>
        <h3>2. Find Your Room</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin-top: 10px; line-height: 1.6;">Browse through our
          verified listings, filter by location or price, and choose the best fit.</p>
      </div>
      <div class="step-card">
        <div class="step-icon">✅</div>
        <h3>3. Book & Move In</h3>
        <p style="color: var(--text-muted); font-size: 14px; margin-top: 10px; line-height: 1.6;">Confirm your booking
          online safely and get ready to move into your new comfortable space.</p>
      </div>
    </div>
  </section>

  <footer class="site-footer">
    <div class="footer-content">
      <div class="footer-section" style="max-width: 300px;">
        <div class="navbar-logo" style="margin-bottom: 15px;">
          <div class="logo-pin">🏠</div>
          <span style="color:var(--red)">boarding</span><span>rooms</span>
        </div>
        <p>The ultimate boarding house management system for smart students. Find, book, and stay with ease.</p>
      </div>

      <div class="footer-section">
        <h3>Contact Us</h3>
        <p><i class="fa-solid fa-phone"></i> +94 77 123 4567</p>
        <p><i class="fa-solid fa-phone"></i> +94 11 234 5678</p>
        <p><i class="fa-solid fa-envelope"></i> info@boardingrooms.lk</p>
        <p><i class="fa-solid fa-location-dot"></i> No 123, Main Street, Colombo</p>
      </div>

      <div class="footer-section">
        <h3>Follow Us</h3>
        <div class="social-icons">
          <a href="#" title="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
          <a href="#" title="Instagram"><i class="fa-brands fa-instagram"></i></a>
          <a href="#" title="X (Twitter)"><i class="fa-brands fa-x-twitter"></i></a>
          <a href="#" title="WhatsApp"><i class="fa-brands fa-whatsapp"></i></a>
        </div>
      </div>
    </div>

    <div class="footer-bottom">
      &copy; 2026 BoardingRooms. All Rights Reserved.
    </div>
  </footer>

</body>

</html>