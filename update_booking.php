<?php
/* update_booking.php */
session_start();
$isAdmin = isset($_SESSION['admin_id']);
$isUser  = isset($_SESSION['user_id']);
if(!$isAdmin && !$isUser) { header('Location: login.php'); exit; }
require_once 'db.php';
$id = (int)($_GET['id'] ?? 0);

if($isAdmin && isset($_GET['action']) && $_GET['action'] === 'confirm') {
  $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=?")->execute([$id]);
  $pdo->prepare("UPDATE rooms SET status='occupied' WHERE id=(SELECT room_id FROM bookings WHERE id=?)")->execute([$id]);
  header('Location: admin_dashboard.php'); exit;
}

$stmt = $pdo->prepare("SELECT b.*, r.room_number, r.room_type, u.name as tenant_name FROM bookings b JOIN rooms r ON b.room_id=r.id JOIN users u ON b.user_id=u.id WHERE b.id=?");
$stmt->execute([$id]);
$booking = $stmt->fetch();
if(!$booking) { header('Location: ' . ($isAdmin ? 'admin_dashboard.php' : 'my_bookings.php')); exit; }
if($isUser && $booking['user_id'] != $_SESSION['user_id']) { header('Location: my_bookings.php'); exit; }

$error = ''; $success = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $status = $isAdmin ? ($_POST['status'] ?? $booking['status']) : $booking['status'];
  $check_in  = $_POST['check_in']  ?? $booking['check_in'];
  $check_out = $_POST['check_out'] ?? $booking['check_out'];
  $pdo->prepare("UPDATE bookings SET status=?,check_in=?,check_out=? WHERE id=?")->execute([$status,$check_in,$check_out,$id]);
  if($status === 'cancelled') $pdo->prepare("UPDATE rooms SET status='available' WHERE id=?")->execute([$booking['room_id']]);
  if($status === 'confirmed') $pdo->prepare("UPDATE rooms SET status='occupied' WHERE id=?")->execute([$booking['room_id']]);
  $success = 'Booking updated successfully!';
  $stmt->execute([$id]);
  $booking = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Update Booking — BoardingHouse</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<?php if($isAdmin): ?>
<div class="admin-wrapper">
<header class="topbar">
  <div class="topbar-logo"><span class="topbar-logo-red">boarding</span>house<span class="topbar-badge">Admin Panel</span></div>
  <div class="topbar-right"><a href="logout.php" class="topbar-logout">Logout</a></div>
</header>
<aside class="sidebar"><nav class="sidebar-nav">
  <a href="admin_dashboard.php" class="nav-item active"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>Dashboard</a>
</nav></aside>
<main class="main-content">
<?php else: ?>
<div style="background:var(--offwhite);min-height:100vh;">
<nav style="background:#fff;border-bottom:1px solid var(--border);padding:0 40px;height:60px;display:flex;align-items:center;justify-content:space-between;">
  <a href="index.php" style="font-size:18px;font-weight:800;text-decoration:none;"><span style="color:var(--red)">boarding</span><span style="color:var(--navy)">house</span></a>
  <a href="my_bookings.php" class="btn btn-outline btn-sm">← My Bookings</a>
</nav>
<div style="max-width:700px;margin:36px auto;padding:0 24px;">
<?php endif; ?>

  <div class="page-header-row">
    <div><div class="page-title">Update Booking #<?= str_pad($id,4,'0',STR_PAD_LEFT) ?></div></div>
    <a href="<?= $isAdmin?'admin_dashboard.php':'my_bookings.php' ?>" class="btn btn-outline btn-sm">← Back</a>
  </div>
  <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
  <div class="card" style="max-width:600px;">
    <div class="card-header">
      <div class="card-title">Booking Details — Room <?= htmlspecialchars($booking['room_number']) ?></div>
      <span class="pill <?= $booking['status']==='confirmed'?'pill-success':($booking['status']==='pending'?'pill-warning':'pill-danger') ?>"><?= ucfirst($booking['status']) ?></span>
    </div>
    <div class="card-body">
      <div style="background:var(--offwhite);border-radius:10px;padding:14px 16px;margin-bottom:20px;">
        <div style="font-weight:700;"><?= htmlspecialchars($booking['tenant_name']) ?></div>
        <div style="font-size:13px;color:var(--muted);">Room <?= htmlspecialchars($booking['room_number']) ?> · <?= htmlspecialchars($booking['room_type']) ?></div>
        <div style="font-size:16px;font-weight:800;color:var(--red);margin-top:6px;">₹<?= number_format($booking['amount']) ?></div>
      </div>
      <form method="POST">
        <div class="grid-2">
          <div class="form-group">
            <label class="form-label">Check-in Date</label>
            <input type="date" name="check_in" class="form-control" value="<?= $booking['check_in'] ?>" <?= !$isAdmin?'readonly':'' ?>>
          </div>
          <div class="form-group">
            <label class="form-label">Check-out Date</label>
            <input type="date" name="check_out" class="form-control" value="<?= $booking['check_out'] ?>" <?= !$isAdmin?'readonly':'' ?>>
          </div>
        </div>
        <?php if($isAdmin): ?>
        <div class="form-group">
          <label class="form-label">Booking Status</label>
          <select name="status" class="form-control">
            <option value="pending"   <?= $booking['status']==='pending'?'selected':'' ?>>Pending</option>
            <option value="confirmed" <?= $booking['status']==='confirmed'?'selected':'' ?>>Confirmed</option>
            <option value="cancelled" <?= $booking['status']==='cancelled'?'selected':'' ?>>Cancelled</option>
          </select>
        </div>
        <?php endif; ?>
        <div style="display:flex;gap:12px;margin-top:8px;">
          <button type="submit" class="btn btn-primary" style="padding:11px 24px;">Save Changes</button>
          <?php if($isAdmin && $booking['status']==='pending'): ?>
          <a href="?id=<?= $id ?>&action=confirm" class="btn" style="background:var(--green-light);color:#1b5e20;border:1px solid #a5d6a7;padding:11px 20px;font-weight:600;">✓ Confirm Now</a>
          <?php endif; ?>
        </div>
      </form>
    </div>
  </div>

<?php if($isAdmin): ?>
</main></div>
<?php else: ?>
</div></div>
<?php endif; ?>
</body></html>
