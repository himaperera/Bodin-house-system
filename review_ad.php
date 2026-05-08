<?php
session_start();
require_once 'db.php';

// if (!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }

if (!isset($_GET['id'])) {
    header('Location: admin_dashboard.php');
    exit;
}
$room_id = $_GET['id'];

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
        $msg = "Sorry, your ad for Room $room_num was rejected. Please contact support.";
        $pdo->prepare("INSERT INTO notifications (user_id, title, message) VALUES (?, 'Ad Rejected', ?)")->execute([$user_id, $msg]);
    }
    header('Location: admin_dashboard.php');
    exit;
}

// Fetch Room Data
$stmt = $pdo->prepare("SELECT r.*, u.name as uni_name FROM rooms r LEFT JOIN universities u ON r.university_id = u.id WHERE r.id = ?");
$stmt->execute([$room_id]);
$room = $stmt->fetch();
$images = json_decode($room['images'], true) ?: ['https://images.unsplash.com/photo-1522771739844-6a9f6d5f14af?auto=format&fit=crop&w=800&q=80'];
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Review Ad - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --navy-deep: #020617;
            --glass-border: rgba(255, 255, 255, 0.12);
            --blue-accent: #38bdf8;
            --red: #ef4444;
            --green-accent: #10b981;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--navy-deep);
            color: #fff;
        }

        .ad-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
            display: flex;
            gap: 30px;
        }

        .ad-left {
            flex: 2;
        }

        .ad-right {
            flex: 1;
        }

        .main-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 12px;
        }

        .admin-box {
            background: rgba(255, 255, 255, 0.05);
            padding: 30px;
            border-radius: 20px;
            border: 1px solid var(--glass-border);
        }

        .btn {
            padding: 15px;
            border-radius: 10px;
            font-weight: bold;
            width: 100%;
            border: none;
            cursor: pointer;
            margin-bottom: 10px;
            color: #fff;
        }

        .btn-approve {
            background: var(--green-accent);
        }

        .btn-reject {
            background: var(--red);
        }
    </style>
</head>

<body>
    <div class="ad-container">
        <div class="ad-left">
            <img src="<?= htmlspecialchars($images[0]) ?>" class="main-image">
            <h1>
                <?= htmlspecialchars($room['room_type']) ?> - Room
                <?= htmlspecialchars($room['room_number']) ?>
            </h1>
            <p>Location:
                <?= htmlspecialchars($room['uni_name']) ?> | Price: Rs.
                <?= number_format($room['price']) ?>
            </p>
            <p>
                <?= nl2br(htmlspecialchars($room['description'])) ?>
            </p>
        </div>
        <div class="ad-right">
            <div class="admin-box">
                <h2 style="color:var(--blue-accent);">Admin Controls</h2>
                <p>Review the details and approve the ad to make it public.</p>
                <form method="POST">
                    <input type="hidden" name="submitted_by" value="<?= $room['submitted_by'] ?>">
                    <input type="hidden" name="room_number" value="<?= htmlspecialchars($room['room_number']) ?>">
                    <button type="submit" name="action" value="approve" class="btn btn-approve">Approve Ad</button>
                    <button type="submit" name="action" value="reject" class="btn btn-reject">Reject Ad</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>