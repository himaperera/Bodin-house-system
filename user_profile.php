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
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile — BoardingRooms</title>
    <style>
        :root {
            --dark-bg: #0b1120;
            /* Deep midnight navy */
            --card-bg: #1e293b;
            /* Slate card background */
            --accent-red: #ef4444;
            /* Brand Red */
            --accent-blue: #38bdf8;
            /* Sky Blue highlight */
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --border-color: #334155;
        }

        body {
            background-color: var(--dark-bg);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
            line-height: 1.6;
        }

        /* Standardized Navigation */
        .auth-nav {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 0 40px;
            height: 70px;
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
            /* Teardrop shape */
            transform: rotate(-45deg);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .logo-pin span {
            transform: rotate(45deg);
            font-size: 20px;
        }

        .logo-text {
            font-size: 26px;
            font-weight: 800;
            letter-spacing: -1px;
        }

        .nav-btn-group {
            display: flex;
            gap: 12px;
        }

        .btn-outline {
            border: 1px solid var(--border-color);
            color: #fff;
            padding: 8px 20px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.2s;
            background: rgba(255, 255, 255, 0.05);
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--text-muted);
        }

        /* Profile Layout */
        .profile-container {
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 80px 20px;
            min-height: calc(100vh - 150px);
        }

        .profile-card {
            background: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 28px;
            width: 100%;
            max-width: 550px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
        }

        .profile-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            padding: 50px 40px;
            text-align: center;
            border-bottom: 1px solid var(--border-color);
        }

        .avatar-circle {
            width: 110px;
            height: 110px;
            background: var(--accent-blue);
            color: #0f172a;
            font-size: 44px;
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            margin: 0 auto 20px;
            border: 5px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
        }

        .profile-header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 800;
            letter-spacing: -0.5px;
        }

        .profile-header p {
            color: var(--accent-blue);
            margin: 8px 0 0;
            font-size: 15px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .profile-info {
            padding: 40px;
            background: rgba(15, 23, 42, 0.3);
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .info-row:last-of-type {
            border-bottom: none;
        }

        .info-label {
            color: var(--text-muted);
            font-size: 14px;
            font-weight: 600;
        }

        .info-value {
            color: var(--text-main);
            font-weight: 500;
            font-size: 15px;
        }

        /* Action Buttons */
        .profile-actions {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 35px;
        }

        .btn-edit {
            background: var(--accent-blue);
            color: #0f172a;
            text-align: center;
            padding: 14px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: transform 0.2s, background 0.2s;
        }

        .btn-edit:hover {
            background: #7dd3fc;
            transform: translateY(-2px);
        }

        .btn-logout {
            background: rgba(239, 68, 68, 0.1);
            color: var(--accent-red);
            border: 1px solid var(--accent-red);
            text-align: center;
            padding: 14px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 700;
            transition: all 0.2s;
        }

        .btn-logout:hover {
            background: var(--accent-red);
            color: white;
            transform: translateY(-2px);
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
        <div class="nav-btn-group">
            <a href="available_rooms.php" class="btn-outline">Browse Rooms</a>
            <a href="index.php" class="btn-outline">Dashboard</a>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-header">
                <div class="avatar-circle">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h1>
                    <?= htmlspecialchars($user['name']) ?>
                </h1>
                <p>Verified Student</p>
            </div>

            <div class="profile-info">
                <div class="info-row">
                    <span class="info-label">Full Name</span>
                    <span class="info-value">
                        <?= htmlspecialchars($user['name']) ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Email Address</span>
                    <span class="info-value">
                        <?= htmlspecialchars($user['email']) ?>
                    </span>
                </div>

                <div class="info-row">
                    <span class="info-label">Phone Number</span>
                    <span class="info-value">
                        <?= htmlspecialchars($user['phone'] ?: 'Not linked') ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Account Status</span>
                    <span class="info-value" style="color: var(--accent-blue);">Active</span>
                </div>

                <div class="profile-actions">
                    <a href="edit_profile.php" class="btn-edit">Edit Profile</a>
                    <a href="logout.php" class="btn-logout">Sign Out</a>
                </div>
            </div>
        </div>
    </div>

</body>

</html>