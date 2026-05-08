<?php
session_start();
// Redirect to login if not authenticated
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

// Fetch current user data
$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    session_destroy();
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — BoardingRooms</title>

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
        }

        .btn-outline-nav:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* --- Profile Layout --- */
        .profile-container {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 60px 20px;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 28px;
            width: 100%;
            max-width: 550px;
            overflow: hidden;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
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

        /* Profile Header */
        .profile-header {
            background: rgba(0, 0, 0, 0.3);
            padding: 50px 40px 40px;
            text-align: center;
            border-bottom: 1px solid var(--glass-border);
            position: relative;
        }

        .avatar-circle {
            width: 110px;
            height: 110px;
            background: linear-gradient(135deg, var(--blue-accent), #8b5cf6);
            color: #fff;
            font-size: 44px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 4px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 25px rgba(56, 189, 248, 0.4);
        }

        .profile-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .badge-student {
            display: inline-block;
            background: rgba(56, 189, 248, 0.15);
            color: var(--blue-accent);
            padding: 6px 16px;
            border-radius: 50px;
            font-size: 13px;
            font-weight: 700;
            margin-top: 15px;
            border: 1px solid var(--blue-accent);
        }

        /* Profile Info Body */
        .profile-info {
            padding: 40px;
        }

        .info-row {
            display: flex;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px dashed var(--glass-border);
        }

        .info-row:last-of-type {
            border-bottom: none;
        }

        .info-icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--blue-accent);
            font-size: 18px;
            margin-right: 20px;
            border: 1px solid var(--glass-border);
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            color: var(--text-muted);
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 4px;
            display: block;
        }

        .info-value {
            color: var(--text-main);
            font-weight: 600;
            font-size: 16px;
        }

        .status-active {
            color: var(--green-accent);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Action Buttons */
        .profile-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 40px;
        }

        .btn-edit {
            background: var(--blue-accent);
            color: #000;
            text-align: center;
            padding: 14px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 800;
            transition: 0.3s;
            border: none;
        }

        .btn-edit:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(56, 189, 248, 0.3);
        }

        .btn-logout {
            background: transparent;
            color: var(--red);
            border: 1px solid var(--red);
            text-align: center;
            padding: 14px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-logout:hover {
            background: var(--red);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(239, 68, 68, 0.3);
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
        }

        @media (max-width: 600px) {
            .profile-actions {
                grid-template-columns: 1fr;
            }

            .profile-header,
            .profile-info {
                padding: 30px 20px;
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
            <a href="my_bookings.php">My Bookings</a>
            <a href="user_profile.php" class="active">Profile</a>
        </div>
        <div class="navbar-actions">
            <a href="logout.php" class="btn-outline-nav">Logout</a>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-card">

            <div class="profile-header">
                <?php
                // Create initials from name (e.g. Supun Perera -> SP)
                $initials = preg_match_all('#\b\w#', $user['name'], $matches) ? implode('', $matches[0]) : '?';
                $initials = strtoupper(substr($initials, 0, 2));
                ?>
                <div class="avatar-circle">
                    <?= $initials ?>
                </div>
                <h1>
                    <?= htmlspecialchars($user['name']) ?>
                </h1>
                <div class="badge-student"><i class="fa-solid fa-user-check"></i> Verified Student</div>
            </div>

            <div class="profile-info">

                <div class="info-row">
                    <div class="info-icon"><i class="fa-regular fa-id-card"></i></div>
                    <div class="info-content">
                        <span class="info-label">Full Name</span>
                        <span class="info-value">
                            <?= htmlspecialchars($user['name']) ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="fa-regular fa-envelope"></i></div>
                    <div class="info-content">
                        <span class="info-label">Email Address</span>
                        <span class="info-value">
                            <?= htmlspecialchars($user['email']) ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="fa-solid fa-phone"></i></div>
                    <div class="info-content">
                        <span class="info-label">Phone Number</span>
                        <span class="info-value">
                            <?= htmlspecialchars($user['phone'] ?: 'Not linked') ?>
                        </span>
                    </div>
                </div>

                <div class="info-row">
                    <div class="info-icon"><i class="fa-solid fa-shield-halved"></i></div>
                    <div class="info-content">
                        <span class="info-label">Account Status</span>
                        <span class="info-value status-active">
                            <i class="fa-solid fa-circle" style="font-size: 10px;"></i> Active
                        </span>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="edit_profile.php" class="btn-edit"><i class="fa-solid fa-pen-to-square"></i> Edit
                        Profile</a>
                    <a href="logout.php" class="btn-logout"><i class="fa-solid fa-arrow-right-from-bracket"></i> Sign
                        Out</a>
                </div>

            </div>
        </div>
    </div>

    <footer class="site-footer">
        &copy; 2026 BoardingRooms. All Rights Reserved.
    </footer>

</body>

</html>