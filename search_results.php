<?php
session_start();
require_once 'db.php'; // ඔබගේ Database Connection ගොනුව

// Search කර ඇති විශ්වවිද්‍යාලය ලබා ගැනීම
$search_uni = isset($_GET['university']) ? $_GET['university'] : '';

// Search කර නොමැති නම් ආපසු index පිටුවට යැවීම
if (empty($search_uni)) {
    header("Location: index.php");
    exit;
}

// විශ්වවිද්‍යාලයේ විස්තර ලබා ගැනීම
$stmt_uni = $pdo->prepare("SELECT * FROM universities WHERE short_code = :short_code");
$stmt_uni->execute(['short_code' => $search_uni]);
$university = $stmt_uni->fetch();

if (!$university) {
    die("Invalid University Selected!");
}

// අදාළ විශ්වවිද්‍යාලය අවට ඇති සියලුම බෝඩිං ලබා ගැනීම (දුර අනුව පෙළගස්වා ඇත)
$sql = "SELECT r.* FROM rooms r 
        WHERE r.university_id = :uni_id AND r.status = 'available' 
        ORDER BY r.distance_from_uni_km ASC";
$stmt_rooms = $pdo->prepare($sql);
$stmt_rooms->execute(['uni_id' => $university['id']]);
$rooms = $stmt_rooms->fetchAll();

// පරිශීලකයා Login වී ඇත්දැයි පරීක්ෂා කිරීම (ඔබගේ login script එකේ session variable එක 'user_id' යැයි උපකල්පනය කර ඇත)
$is_logged_in = isset($_SESSION['user_id']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?= htmlspecialchars($university['name']) ?> - BoardingRooms
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
            backdrop-filter: blur(8px);
            /* slightly more blur for inner pages */
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

        /* Page Header for Search Results */
        .results-header {
            padding: 60px 40px 40px;
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

        .results-header h1 {
            font-size: 2.8rem;
            font-weight: 900;
            margin-bottom: 15px;
        }

        .results-header h1 span {
            color: var(--blue-accent);
        }

        .results-header p {
            color: var(--text-muted);
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Room Cards */
        .room-container {
            padding: 0 40px 60px;
            max-width: 1300px;
            margin: 0 auto;
        }

        .room-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 30px;
        }

        .room-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            border: 1px solid var(--glass-border);
            border-radius: 20px;
            overflow: hidden;
            transition: 0.4s;
            display: flex;
            flex-direction: column;
        }

        .room-card:hover {
            transform: translateY(-10px);
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--blue-accent);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.4);
        }

        .card-image-wrapper {
            height: 200px;
            position: relative;
        }

        .card-image-wrapper img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .card-content {
            padding: 25px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .rating-num {
            background: var(--blue-accent);
            color: #000;
            padding: 4px 10px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 12px;
        }

        .price-new {
            font-size: 24px;
            font-weight: 800;
            color: #fff;
            margin-top: auto;
            /* pushes price to bottom */
        }

        /* Buttons */
        .btn-primary {
            background: var(--green-accent);
            color: white;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
            border: none;
            text-align: center;
            cursor: pointer;
        }

        .btn-primary:hover {
            transform: scale(1.05);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.4);
        }

        .btn-login-prompt {
            background: transparent;
            color: var(--red);
            border: 1px solid var(--red);
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
            text-align: center;
        }

        .btn-login-prompt:hover {
            background: var(--red);
            color: #fff;
            box-shadow: 0 0 15px rgba(239, 68, 68, 0.4);
        }

        .btn-outline {
            border: 1px solid var(--glass-border);
            color: #fff;
            padding: 8px 16px;
            border-radius: 12px;
            text-decoration: none;
            transition: 0.3s;
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
            <?php if ($is_logged_in): ?>
                <a href="logout.php" class="btn-outline">Logout</a>
            <?php else: ?>
                <a href="login.php" class="btn-outline">Login</a>
                <a href="register.php" class="btn-outline" style="background:var(--red); border:none;">Sign Up</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="results-header">
        <div
            style="display:inline-block; padding:8px 20px; background:rgba(56, 189, 248, 0.15); color:var(--blue-accent); border-radius:50px; border:1px solid var(--blue-accent); font-size:13px; font-weight:700; margin-bottom:20px;">
            <i class="fa-solid fa-check-circle"></i> Search Results Found
        </div>
        <h1>Boarding Houses near <br><span>
                <?= htmlspecialchars($university['name']) ?>
            </span></h1>
        <p><i class="fa-solid fa-map-location-dot"></i>
            <?= htmlspecialchars($university['location']) ?>
        </p>
    </header>

    <section class="room-container">
        <div class="room-grid">

            <?php if (count($rooms) > 0): ?>
                <?php foreach ($rooms as $index => $room):
                    $image_url = 'https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=500';
                    ?>
                    <div class="room-card" style="animation: fadeIn 0.5s ease forwards; animation-delay: <?= $index * 0.1 ?>s;">
                        <div class="card-image-wrapper">
                            <img src="<?= $image_url ?>" alt="Room Image">
                        </div>

                        <div class="card-content">
                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                                <span class="rating-num"><i class="fa-solid fa-users"></i> Cap:
                                    <?= htmlspecialchars($room['capacity']) ?>
                                </span>
                                <span
                                    style="color: var(--blue-accent); font-size: 11px; font-weight: 800; letter-spacing: 1px; background: rgba(56, 189, 248, 0.1); padding: 4px 8px; border-radius: 4px;">
                                    <i class="fa-solid fa-person-walking"></i>
                                    <?= htmlspecialchars($room['distance_from_uni_km']) ?> KM AWAY
                                </span>
                            </div>

                            <h3 style="margin-bottom: 10px; font-size: 20px;">
                                <?= htmlspecialchars($room['room_type']) ?>
                                <span style="font-size:14px; color:var(--text-muted);">#
                                    <?= htmlspecialchars($room['room_number']) ?>
                                </span>
                            </h3>

                            <p style="color: var(--text-muted); font-size: 13px; margin-bottom: 20px; line-height: 1.5;">
                                <i class="fa-solid fa-list-check" style="color:var(--blue-accent);"></i>
                                <?= htmlspecialchars($room['amenities']) ?>
                            </p>

                            <div
                                style="display: flex; justify-content: space-between; align-items: center; margin-top: auto; border-top: 1px solid var(--glass-border); padding-top: 15px;">
                                <span class="price-new">LKR
                                    <?= number_format($room['price'], 0) ?>
                                </span>

                                <?php if ($is_logged_in): ?>
                                    <a href="book_room.php?id=<?= $room['id'] ?>" class="btn-primary">
                                        <i class="fa-solid fa-check-to-slot"></i> Confirm
                                    </a>
                                <?php else: ?>
                                    <a href="login.php?redirect=search_results.php?university=<?= urlencode($search_uni) ?>"
                                        class="btn-login-prompt"
                                        onclick="alert('You must be logged in to confirm a booking. Please login or sign up first.');">
                                        <i class="fa-solid fa-lock"></i> Login to Book
                                    </a>
                                <?php endif; ?>

                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php else: ?>
                <div
                    style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; background: rgba(255,255,255,0.05); border-radius: 20px; border: 1px dashed var(--glass-border);">
                    <i class="fa-solid fa-house-circle-xmark"
                        style="font-size: 50px; color: var(--text-muted); margin-bottom: 20px;"></i>
                    <h3 style="color: #fff; margin-bottom: 10px; font-size: 24px;">No Rooms Found</h3>
                    <p style="color: var(--text-muted);">Sorry, all boarding houses near <strong>
                            <?= htmlspecialchars($university['name']) ?>
                        </strong> are currently full or unavailable.</p>
                    <a href="index.php" class="btn-outline" style="margin-top: 20px; display: inline-block;">Search Another
                        University</a>
                </div>
            <?php endif; ?>

        </div>
    </section>

</body>

</html>