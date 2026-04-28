<?php
session_start();
if(!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }
require_once 'db.php';
$error = ''; $success = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $room_number = trim($_POST['room_number'] ?? '');
  $room_type   = trim($_POST['room_type'] ?? '');
  $floor       = (int)($_POST['floor'] ?? 1);
  $price       = (float)($_POST['price'] ?? 0);
  $amenities   = trim($_POST['amenities'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $capacity    = (int)($_POST['capacity'] ?? 1);
  if(!$room_number) $error = 'Room number is required.';
  elseif(!$room_type) $error = 'Room type is required.';
  elseif($price <= 0) $error = 'Please enter a valid monthly price.';
  else {
    $check = $pdo->prepare("SELECT id FROM rooms WHERE room_number = ?");
    $check->execute([$room_number]);
    if($check->fetch()) { $error = "Room number '$room_number' already exists."; }
    else {
      $ins = $pdo->prepare("INSERT INTO rooms (room_number, room_type, floor, price, amenities, description, capacity, status) VALUES (?,?,?,?,?,?,?,'available')");
      $ins->execute([$room_number, $room_type, $floor, $price, $amenities, $description, $capacity]);
      $success = "Room $room_number added successfully!";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Add Room — Admin</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-wrapper">
<header class="topbar">
  <div class="topbar-logo"><span class="topbar-logo-red">boarding</span>house<span class="topbar-badge">Admin Panel</span></div>
  <div class="topbar-right">
    <div class="topbar-user"><div class="topbar-avatar"><?= strtoupper(substr($_SESSION['admin_name']??'A',0,1)) ?></div><span><?= htmlspecialchars($_SESSION['admin_name']??'Admin') ?></span></div>
    <a href="logout.php" class="topbar-logout">Logout</a>
  </div>
</header>
<aside class="sidebar">
  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="admin_dashboard.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>Dashboard</a>
    <a href="add_room.php" class="nav-item active"><svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>Rooms</a>
    <a href="admin_dashboard.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>Bookings</a>
    <div class="nav-section-label" style="margin-top:8px;">Management</div>
    <a href="add_room.php" class="nav-item active"><svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>Add Room</a>
  </nav>
</aside>
<main class="main-content">
  <div class="page-header-row">
    <div>
      <div class="page-title">Add New Room</div>
      <div class="page-subtitle">List a new room with full details and pricing.</div>
    </div>
    <a href="admin_dashboard.php" class="btn btn-outline btn-sm">← Back to Dashboard</a>
  </div>

  <div style="max-width:700px;">
    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?= $success ?> <a href="admin_dashboard.php" style="font-weight:700;">View Dashboard →</a></div><?php endif; ?>

    <div class="card">
      <div class="card-header"><div class="card-title">Room Details</div></div>
      <div class="card-body">
        <form method="POST">
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Room Number <span style="color:var(--red)">*</span></label>
              <input type="text" name="room_number" class="form-control" placeholder="e.g. 101, 2A" required value="<?= htmlspecialchars($_POST['room_number']??'') ?>">
              <div class="form-hint">Must be unique across all rooms</div>
            </div>
            <div class="form-group">
              <label class="form-label">Room Type <span style="color:var(--red)">*</span></label>
              <select name="room_type" class="form-control" required>
                <option value="">Select type...</option>
                <?php foreach(['Single Room','Double Room','Triple Room','Suite','Dormitory'] as $t): ?>
                <option value="<?= $t ?>" <?= ($_POST['room_type']??'')===$t?'selected':'' ?>><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Floor Number</label>
              <select name="floor" class="form-control">
                <?php for($i=1;$i<=10;$i++): ?>
                <option value="<?= $i ?>" <?= ($_POST['floor']??1)==$i?'selected':'' ?>>Floor <?= $i ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Capacity (persons)</label>
              <select name="capacity" class="form-control">
                <?php for($i=1;$i<=6;$i++): ?>
                <option value="<?= $i ?>" <?= ($_POST['capacity']??1)==$i?'selected':'' ?>><?= $i ?> person<?= $i>1?'s':'' ?></option>
                <?php endfor; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Monthly Rent (₹) <span style="color:var(--red)">*</span></label>
            <div class="input-group">
              <svg class="input-icon" viewBox="0 0 24 24"><path d="M11.8 10.9c-2.27-.59-3-1.2-3-2.15 0-1.09 1.01-1.85 2.7-1.85 1.78 0 2.44.85 2.5 2.1h2.21c-.07-1.72-1.12-3.3-3.21-3.81V3h-3v2.16c-1.94.42-3.5 1.68-3.5 3.61 0 2.31 1.91 3.46 4.7 4.13 2.5.6 3 1.48 3 2.41 0 .69-.49 1.79-2.7 1.79-2.06 0-2.87-.92-2.98-2.1h-2.2c.12 2.19 1.76 3.42 3.68 3.83V21h3v-2.15c1.95-.37 3.5-1.5 3.5-3.55 0-2.84-2.43-3.81-4.7-4.4z"/></svg>
              <input type="number" name="price" class="form-control" placeholder="e.g. 5000" min="0" step="100" required value="<?= htmlspecialchars($_POST['price']??'') ?>">
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Amenities</label>
            <input type="text" name="amenities" class="form-control" placeholder="e.g. WiFi, AC, Attached Bathroom, TV" value="<?= htmlspecialchars($_POST['amenities']??'') ?>">
            <div class="form-hint">Comma-separated list of amenities included</div>
          </div>
          <div class="form-group">
            <label class="form-label">Room Description</label>
            <textarea name="description" class="form-control" placeholder="Describe the room, its features, view, etc."><?= htmlspecialchars($_POST['description']??'') ?></textarea>
          </div>
          <div style="display:flex;gap:12px;margin-top:8px;">
            <button type="submit" class="btn btn-primary" style="padding:12px 28px;font-size:15px;">Add Room</button>
            <a href="admin_dashboard.php" class="btn btn-outline" style="padding:12px 20px;">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>
</div>
</body>
</html>
