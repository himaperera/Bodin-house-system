<?php
session_start();
require_once 'db.php';

// URL එකෙන් Room ID එක ලබා ගැනීම
if (!isset($_GET['id'])) {
    header('Location: rooms.php');
    exit;
}

$room_id = $_GET['id'];
$error = '';
$success = '';

// බෝඩිමේ සම්පූර්ණ විස්තර සහ විශ්වවිද්‍යාලයේ නම ලබා ගැනීම
$stmt = $pdo->prepare("
    SELECT r.*, u.name as uni_name 
    FROM rooms r 
    LEFT JOIN universities u ON r.university_id = u.id 
    WHERE r.id = ?
");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    die("Room not found!");
}

// Database එකේ ඇති පින්තූර Array එකක් ලෙස ලබා ගැනීම (JSON decode කිරීම)
$images = json_decode($room['images'], true);

// පින්තූර නොමැති නම් පෙන්වීමට Default පින්තූරයක් තැබීම
if (empty($images) || !is_array($images)) {
    $images = ['https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=800&q=80'];
}

// Booking එක Submit කළ විට
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        $error = "You must be logged in to book a room.";
    } else {
        $user_id = $_SESSION['user_id'];
        $check_in = $_POST['check_in'];
        $check_out = $_POST['check_out'];

        // මාස ගණන ගණනය කර මුළු මුදල (Amount) සෑදීම
        $datetime1 = new DateTime($check_in);
        $datetime2 = new DateTime($check_out);
        $interval = $datetime1->diff($datetime2);
        $months = max(1, round($interval->days / 30));
        $total_amount = $months * $room['price'];

        try {
            $book_stmt = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out, amount, status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $book_stmt->execute([$user_id, $room_id, $check_in, $check_out, $total_amount]);
            $success = "Your booking request has been sent! Please wait for confirmation.";

            // අවශ්‍ය නම් room එකේ status එක 'reserved' කරන්න පුළුවන් (දැනට available ලෙසම තබමු)
        } catch (PDOException $e) {
            $error = "Failed to process booking. Try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Room
        <?= htmlspecialchars($room['room_number']) ?> - BoardingRooms
    </title>

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
            backdrop-filter: blur(10px);
            z-index: -1;
        }

        /* Navbar */
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

        /* Page Layout */
        .ad-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
            display: flex;
            gap: 30px;
            flex-wrap: wrap;
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

        /* --- Left: Image Gallery & Details --- */
        .ad-left {
            flex: 2;
            min-width: 300px;
        }

        .image-gallery {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 15px;
            margin-bottom: 30px;
        }

        .main-image {
            width: 100%;
            height: 400px;
            border-radius: 14px;
            object-fit: cover;
            margin-bottom: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .thumbnail-container {
            display: flex;
            gap: 10px;
            overflow-x: auto;
        }

        .thumbnail {
            width: 80px;
            height: 60px;
            border-radius: 8px;
            object-fit: cover;
            cursor: pointer;
            opacity: 0.6;
            transition: 0.3s;
            border: 2px solid transparent;
        }

        .thumbnail:hover,
        .thumbnail.active {
            opacity: 1;
            border-color: var(--blue-accent);
        }

        .ad-details-box {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            backdrop-filter: blur(10px);
        }

        .ad-title {
            font-size: 28px;
            font-weight: 800;
            margin: 0 0 10px 0;
            color: #fff;
        }

        .ad-location {
            color: var(--text-muted);
            font-size: 15px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }

        .feature-item {
            background: rgba(56, 189, 248, 0.05);
            border: 1px solid rgba(56, 189, 248, 0.15);
            padding: 15px;
            border-radius: 12px;
            text-align: center;
        }

        .feature-item i {
            font-size: 24px;
            color: var(--blue-accent);
            margin-bottom: 8px;
        }

        .feature-label {
            font-size: 11px;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        .feature-val {
            font-size: 16px;
            font-weight: 700;
            color: #fff;
        }

        .ad-description {
            line-height: 1.7;
            color: var(--text-muted);
            border-top: 1px dashed var(--glass-border);
            padding-top: 25px;
        }

        /* --- Right: Booking Form --- */
        .ad-right {
            flex: 1;
            min-width: 300px;
        }

        .booking-card {
            background: rgba(2, 6, 23, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            padding: 30px;
            position: sticky;
            top: 100px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        .booking-price {
            font-size: 32px;
            font-weight: 900;
            color: var(--red);
            margin-bottom: 5px;
        }

        .booking-price span {
            font-size: 14px;
            color: var(--text-muted);
            font-weight: normal;
        }

        .form-group {
            margin-top: 20px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-muted);
        }

        .form-control {
            background: rgba(0, 0, 0, 0.4);
            border: 1px solid var(--glass-border);
            color: #fff;
            border-radius: 12px;
            padding: 12px 15px;
            width: 100%;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        .form-control:focus {
            border-color: var(--blue-accent);
            outline: none;
        }

        .btn-submit {
            background: var(--blue-accent);
            color: #000;
            border: none;
            border-radius: 12px;
            padding: 16px;
            width: 100%;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 25px;
            transition: 0.3s;
        }

        .btn-submit:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(56, 189, 248, 0.3);
        }

        .alert {
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
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
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-logo">
            <div class="logo-pin">🏠</div>
            <span style="color:var(--red)">boarding</span><span>rooms</span>
        </a>
        <div style="display:flex; gap:15px; align-items:center;">
            <a href="rooms.php" style="color:var(--text-muted); text-decoration:none; font-size:14px;"><i
                    class="fa-solid fa-arrow-left"></i> Back to Rooms</a>
        </div>
    </nav>

    <div class="ad-container">

        <div class="ad-left">

            <div class="image-gallery">
                <img src="<?= htmlspecialchars($images[0]) ?>" id="mainImage" class="main-image" alt="Room Main Image">

                <div class="thumbnail-container">
                    <?php foreach ($images as $index => $img): ?>
                        <img src="<?= htmlspecialchars($img) ?>" class="thumbnail <?= $index === 0 ? 'active' : '' ?>"
                            onclick="changeImage(this, '<?= htmlspecialchars($img) ?>')" alt="Room Thumbnail">
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="ad-details-box">
                <h1 class="ad-title">
                    <?= htmlspecialchars($room['room_type']) ?> - Room
                    <?= htmlspecialchars($room['room_number']) ?>
                </h1>
                <div class="ad-location">
                    <i class="fa-solid fa-location-dot" style="color:var(--blue-accent);"></i>
                    <?= $room['uni_name'] ? "Near " . htmlspecialchars($room['uni_name']) . " (" . htmlspecialchars($room['distance_from_uni_km']) . " KM away)" : "Location not specified" ?>
                </div>

                <div class="features-grid">
                    <div class="feature-item">
                        <i class="fa-solid fa-users"></i>
                        <div class="feature-label">Capacity</div>
                        <div class="feature-val">
                            <?= htmlspecialchars($room['capacity']) ?> Persons
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fa-solid fa-layer-group"></i>
                        <div class="feature-label">Floor</div>
                        <div class="feature-val">Level
                            <?= htmlspecialchars($room['floor']) ?>
                        </div>
                    </div>
                    <div class="feature-item">
                        <i class="fa-solid fa-wifi"></i>
                        <div class="feature-label">Amenities</div>
                        <div class="feature-val" style="font-size: 13px;">
                            <?= htmlspecialchars($room['amenities']) ?>
                        </div>
                    </div>
                </div>

                <div class="ad-description">
                    <h3 style="color:#fff; margin-top:0;">About this Room</h3>
                    <p>
                        <?= nl2br(htmlspecialchars($room['description'] ?: 'No additional description provided.')) ?>
                    </p>
                </div>
            </div>

        </div>

        <div class="ad-right">
            <div class="booking-card">
                <div class="booking-price">Rs.
                    <?= number_format($room['price']) ?> <span>/ month</span>
                </div>
                <p
                    style="color:var(--text-muted); font-size:13px; margin-bottom: 25px; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px;">
                    Includes base utilities. Subject to terms.</p>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <?= $error ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success">
                        <?= $success ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="form-group">
                        <label class="form-label">Check-in Date</label>
                        <input type="date" name="check_in" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Check-out Date</label>
                        <input type="date" name="check_out" class="form-control" required
                            min="<?= date('Y-m-d', strtotime('+1 month')) ?>">
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button type="submit" class="btn-submit">Request to Book</button>
                    <?php else: ?>
                        <a href="login.php" class="btn-submit"
                            style="display:block; text-align:center; text-decoration:none; background:var(--red); color:#fff;">Login
                            to Book</a>
                    <?php endif; ?>
                </form>

            </div>
        </div>

    </div>

    <footer class="site-footer">
        &copy; 2026 BoardingRooms. All Rights Reserved.
    </footer>

    <script>
        function changeImage(element, imageUrl) {
            // ප්‍රධාන පින්තූරය මාරු කිරීම
            document.getElementById('mainImage').src = imageUrl;

            // Thumbnail වල active class එක මාරු කිරීම
            let thumbnails = document.querySelectorAll('.thumbnail');
            thumbnails.forEach(thumb => thumb.classList.remove('active'));
            element.classList.add('active');
        }
    </script>

</body>

</html>