<?php
session_start();
require_once 'db.php';

// Admin authentication
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin_login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}

$room_id = $_GET['id'];
$adminName = $_SESSION['admin_name'] ?? 'Admin';

// Handle Approval / Rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $user_id = $_POST['submitted_by'];
    $room_num = $_POST['room_number'];

    if ($action === 'approve') {
        // Status එක available කිරීම
        $pdo->prepare("UPDATE rooms SET status = 'available' WHERE id = ?")->execute([$room_id]);
        // User ට Notification යැවීම
        $msg = "Great news! Your ad for Room $room_num has been approved and is now live.";
        $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Ad Approved!', ?)")->execute([$user_id, $msg]);
    } else if ($action === 'reject') {
        $pdo->prepare("UPDATE rooms SET status = 'maintenance' WHERE id = ?")->execute([$room_id]);
        $msg = "Sorry, your ad for Room $room_num was rejected. Please review our guidelines or contact support.";
        $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Ad Rejected', ?)")->execute([$user_id, $msg]);
    }
    header('Location: admin_dashboard.php');
    exit;
}

// Fetch Room Data
$stmt = $pdo->prepare("SELECT r.*, u.name as uni_name, usr.name as owner_name FROM rooms r LEFT JOIN universities u ON r.university_id = u.id LEFT JOIN users usr ON r.submitted_by = usr.id WHERE r.id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();

if (!$room) {
    header('Location: admin_dashboard.php');
    exit;
}

$images = json_decode($room['images'], true) ?: ['https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=800&q=80'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Ad — Admin Panel</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* Specific styles for the review page to keep it simple and clean */
        .review-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 24px;
            align-items: start;
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
            margin-bottom: 20px;
            border: 1px solid var(--glass-border);
        }

        .detail-group {
            margin-bottom: 18px;
        }

        .detail-label {
            font-size: 12px;
            color: var(--muted);
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            display: block;
        }

        .detail-val {
            font-size: 16px;
            color: var(--text);
            font-weight: 500;
            line-height: 1.5;
        }

        .price-tag {
            font-size: 24px;
            font-weight: 800;
            color: var(--blue-accent);
        }

        @media (max-width: 900px) {
            .review-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="admin-wrapper">

        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-logo">
                <span class="topbar-logo-red">boarding</span>house
                <span class="topbar-badge">Admin Panel</span>
            </div>
            <div class="topbar-right">
                <div class="topbar-user">
                    <div class="topbar-avatar">
                        <?= strtoupper(substr($adminName, 0, 1)) ?>
                    </div>
                    <span>
                        <?= htmlspecialchars($adminName) ?>
                    </span>
                </div>
                <a href="logout.php" class="topbar-logout">Logout</a>
            </div>
        </header>

        <!-- SIDEBAR -->
        <aside class="sidebar">
            <nav class="sidebar-nav">
                <div class="nav-section-label">Main</div>
                <a href="admin_dashboard.php" class="nav-item">
                    <svg viewBox="0 0 24 24">
                        <path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z" />
                    </svg>
                    Dashboard
                </a>
                <a href="rooms.php" class="nav-item">
                    <svg viewBox="0 0 24 24">
                        <path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z" />
                    </svg>
                    Rooms
                </a>
            </nav>
        </aside>

        <!-- MAIN CONTENT -->
        <main class="main-content">
            <!-- Page Header -->
            <div class="page-header-row" style="margin-bottom: 24px;">
                <div>
                    <div class="page-title">Review Advertisement</div>
                    <div class="page-subtitle">Evaluate the submitted room listing before publishing to the public.
                    </div>
                </div>
                <div>
                    <a href="admin_dashboard.php" class="btn btn-outline btn-sm"><i class="fa-solid fa-arrow-left"></i>
                        Back to Dashboard</a>
                </div>
            </div>

            <div class="review-grid">

                <!-- LEFT COLUMN: Room Details -->
                <div class="card" style="padding: 24px;">
                    <img src="<?= htmlspecialchars($images[0]) ?>" class="main-image" alt="Room Image">

                    <div
                        style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px; border-bottom: 1px solid var(--glass-border); padding-bottom: 20px;">
                        <div>
                            <h1 style="margin: 0 0 8px 0; font-size: 24px; color: #fff;">
                                <?= htmlspecialchars($room['room_type']) ?> - Room
                                <?= htmlspecialchars($room['room_number']) ?>
                            </h1>
                            <div style="color: var(--muted); font-size: 14px;">
                                <i class="fa-solid fa-location-dot" style="color: var(--red);"></i>
                                <?= htmlspecialchars($room['uni_name'] ?? 'Not Specified') ?>
                            </div>
                        </div>
                        <div class="price-tag">
                            Rs.
                            <?= number_format($room['price']) ?>
                        </div>
                    </div>

                    <div class="detail-group">
                        <span class="detail-label">Description</span>
                        <div class="detail-val" style="color: var(--muted);">
                            <?= nl2br(htmlspecialchars($room['description'])) ?>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top: 20px;">
                        <div class="detail-group">
                            <span class="detail-label">Floor Level</span>
                            <div class="detail-val">
                                <?= htmlspecialchars($room['floor'] ?? 'N/A') ?>
                            </div>
                        </div>
                        <div class="detail-group">
                            <span class="detail-label">Amenities provided</span>
                            <div class="detail-val">
                                <?= htmlspecialchars($room['amenities'] ?? 'None mentioned') ?>
                            </div>
                        </div>
                    </div>
                </div>


                <div>
                    <div class="card" style="border-top: 4px solid var(--blue-accent); position: sticky; top: 90px;">
                        <div class="card-header" style="border-bottom: none; padding-bottom: 0;">
                            <div class="card-title">Approval Action</div>
                        </div>
                        <div class="card-body">
                            <div class="detail-group"
                                style="background: var(--offwhite); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                <span class="detail-label">Submitted By</span>
                                <div class="detail-val" style="display: flex; align-items: center; gap: 10px;">
                                    <i class="fa-solid fa-circle-user"
                                        style="font-size: 24px; color: var(--muted);"></i>
                                    <?= htmlspecialchars($room['owner_name'] ?? 'Unknown User') ?>
                                </div>
                            </div>

                            <p style="font-size: 13px; color: var(--muted); margin-bottom: 20px;">
                                Please ensure the details meet the community guidelines before approving. Rejecting will
                                send the ad to maintenance mode.
                            </p>

                            <form method="POST">
                                <input type="hidden" name="submitted_by" value="<?= $room['submitted_by'] ?>">
                                <input type="hidden" name="room_number"
                                    value="<?= htmlspecialchars($room['room_number']) ?>">

                                <button type="submit" name="action" value="approve"
                                    style="background: var(--green); color: white; width: 100%; padding: 12px; font-size: 15px; font-weight: bold; border: none; border-radius: 8px; margin-bottom: 12px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; transition: 0.3s;">
                                    <i class="fa-solid fa-check"></i> Approve Ad
                                </button>

                                <button type="submit" name="action" value="reject"
                                    style="background: transparent; color: var(--red); width: 100%; padding: 12px; font-size: 15px; font-weight: bold; border: 1px solid var(--red); border-radius: 8px; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; transition: 0.3s;"
                                    onmouseover="this.style.background='rgba(239, 68, 68, 0.1)'"
                                    onmouseout="this.style.background='transparent'">
                                    <i class="fa-solid fa-xmark"></i> Reject Ad
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </main>

    </div>
</body>

</html>