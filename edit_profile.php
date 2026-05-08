<?php
session_start();
require_once 'db.php';

// පරිශීලකයා ලොග් වී නොමැති නම් Login පිටුවට යවන්න
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Form එක Submit කළ විට දත්ත Update කිරීම
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $new_password = $_POST['new_password'];

    try {
        if (!empty($new_password)) {
            // Password එකත් අලුතින් දීලා නම්, ඒකත් update කරන්න (Hash කරලා)
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ?, password = ? WHERE id = ?");
            $update_stmt->execute([$name, $phone, $hashed_password, $user_id]);
        } else {
            // Password එක වෙනස් කරන්නේ නැති නම් නම සහ දුරකථන අංකය පමණක් update කරන්න
            $update_stmt = $pdo->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $update_stmt->execute([$name, $phone, $user_id]);
        }
        $success_msg = "Your profile has been updated successfully!";
    } catch (PDOException $e) {
        $error_msg = "Something went wrong! Please try again.";
    }
}

// දැනට පවතින පරිශීලක දත්ත ලබා ගැනීම (Form එකේ පෙන්වීමට)
$stmt = $pdo->prepare("SELECT name, email, phone FROM users WHERE id = ?");
$stmt->execute([$user_id]);
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
    <title>Edit Profile - BoardingRooms</title>

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

        /* Buttons */
        .btn-primary {
            background: var(--blue-accent);
            color: #000;
            padding: 12px 28px;
            border-radius: 12px;
            font-weight: 800;
            border: none;
            cursor: pointer;
            transition: 0.3s;
            display: inline-block;
            text-align: center;
            width: 100%;
        }

        .btn-primary:hover {
            background: #fff;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(56, 189, 248, 0.4);
        }

        .btn-outline {
            border: 1px solid var(--glass-border);
            color: #fff;
            padding: 8px 16px;
            border-radius: 12px;
            text-decoration: none;
            transition: 0.3s;
        }

        .btn-outline:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* --- Profile Section --- */
        .profile-wrapper {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 60px 20px;
        }

        .profile-card {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
            width: 100%;
            max-width: 900px;
            display: flex;
            overflow: hidden;
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

        /* Left Sidebar (Avatar) */
        .profile-sidebar {
            background: rgba(0, 0, 0, 0.2);
            padding: 50px 30px;
            width: 35%;
            text-align: center;
            border-right: 1px solid var(--glass-border);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .avatar-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--blue-accent), #8b5cf6);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 50px;
            color: #fff;
            margin-bottom: 20px;
            box-shadow: 0 10px 25px rgba(56, 189, 248, 0.4);
            border: 4px solid rgba(255, 255, 255, 0.1);
        }

        .profile-sidebar h2 {
            margin: 0 0 5px 0;
            font-size: 22px;
        }

        .profile-sidebar p {
            color: var(--text-muted);
            font-size: 14px;
            margin: 0;
        }

        .badge-tenant {
            background: rgba(56, 189, 248, 0.15);
            color: var(--blue-accent);
            padding: 5px 15px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: 700;
            margin-top: 15px;
            border: 1px solid var(--blue-accent);
        }

        /* Right Side (Form) */
        .profile-form-container {
            padding: 50px 40px;
            width: 65%;
        }

        .profile-form-container h3 {
            font-size: 24px;
            margin-top: 0;
            margin-bottom: 30px;
            color: #fff;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            flex: 1;
        }

        .form-group label {
            display: block;
            color: var(--text-muted);
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 14px 16px;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            border-radius: 12px;
            color: #fff;
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            outline: none;
            transition: 0.3s;
            box-sizing: border-box;
        }

        .form-control:focus {
            border-color: var(--blue-accent);
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
            background: rgba(0, 0, 0, 0.5);
        }

        .form-control:read-only {
            background: rgba(255, 255, 255, 0.02);
            color: #64748b;
            cursor: not-allowed;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            border: 1px solid var(--green-accent);
            color: var(--green-accent);
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid var(--red);
            color: #ff8a8a;
        }

        /* Footer */
        .site-footer {
            border-top: 1px solid var(--glass-border);
            background: rgba(2, 6, 23, 0.7);
            backdrop-filter: blur(15px);
            padding: 30px 40px;
            margin-top: auto;
            text-align: center;
            color: var(--text-muted);
            font-size: 13px;
        }

        @media (max-width: 768px) {
            .profile-card {
                flex-direction: column;
            }

            .profile-sidebar,
            .profile-form-container {
                width: 100%;
            }

            .profile-sidebar {
                border-right: none;
                border-bottom: 1px solid var(--glass-border);
                padding: 30px 20px;
            }

            .form-row {
                flex-direction: column;
                gap: 0;
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
            <a href="edit_profile.php" style="color:#fff; font-weight:bold;">Profile</a>
        </div>
        <div class="navbar-actions">
            <a href="logout.php" class="btn-outline">Logout</a>
        </div>
    </nav>

    <div class="profile-wrapper">
        <div class="profile-card">

            <div class="profile-sidebar">
                <?php
                $initials = preg_match_all('#\b\w#', $user['name'], $matches) ? implode('', $matches[0]) : '?';
                $initials = strtoupper(substr($initials, 0, 2));
                ?>
                <div class="avatar-circle">
                    <?= $initials ?>
                </div>
                <h2>
                    <?= htmlspecialchars($user['name']) ?>
                </h2>
                <p>
                    <?= htmlspecialchars($user['email']) ?>
                </p>
                <span class="badge-tenant"><i class="fa-solid fa-user-check"></i> Registered Student</span>
            </div>

            <div class="profile-form-container">
                <h3><i class="fa-solid fa-user-pen" style="color:var(--blue-accent);"></i> Edit Your Profile</h3>

                <?php if ($success_msg): ?>
                    <div class="alert alert-success">
                        <i class="fa-solid fa-circle-check"></i>
                        <?= $success_msg ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div class="alert alert-error">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <?= $error_msg ?>
                    </div>
                <?php endif; ?>

                <form action="edit_profile.php" method="POST">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Full Name</label>
                            <input type="text" id="name" name="name" class="form-control"
                                value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" class="form-control"
                                value="<?= htmlspecialchars($user['email']) ?>" readonly
                                title="Email cannot be changed">
                        </div>
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="text" id="phone" name="phone" class="form-control"
                                value="<?= htmlspecialchars($user['phone']) ?>">
                        </div>
                    </div>

                    <div style="border-top: 1px dashed var(--glass-border); margin: 30px 0 20px;"></div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="new_password">New Password (Leave blank to keep current)</label>
                            <input type="password" id="new_password" name="new_password" class="form-control"
                                placeholder="••••••••">
                        </div>
                    </div>

                    <div style="margin-top: 30px;">
                        <button type="submit" class="btn-primary">
                            <i class="fa-solid fa-floppy-disk"></i> Save Changes
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>

    <footer class="site-footer">
        &copy; 2026 BoardingRooms. All Rights Reserved.
    </footer>

</body>

</html>