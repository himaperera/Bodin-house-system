<?php
session_start();
if(!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }
require_once 'db.php';
$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ?");
$stmt->execute([$id]);
$room = $stmt->fetch();
if(!$room) { header('Location: admin_dashboard.php'); exit; }
$error = ''; $success = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $room_number = trim($_POST['room_number'] ?? '');
  $room_type   = trim($_POST['room_type'] ?? '');
  $floor       = (int)($_POST['floor'] ?? 1);
  $price       = (float)($_POST['price'] ?? 0);
  $amenities   = trim($_POST['amenities'] ?? '');
  $description = trim($_POST['description'] ?? '');
  $capacity    = (int)($_POST['capacity'] ?? 1);
  $status      = $_POST['status'] ?? 'available';
  if(!$room_number || !$room_type || $price <= 0) { $error = 'Please fill in all required fields.'; }
  else {
    $upd = $pdo->prepare("UPDATE rooms SET room_number=?,room_type=?,floor=?,price=?,amenities=?,description=?,capacity=?,status=? WHERE id=?");
    $upd->execute([$room_number,$room_type,$floor,$price,$amenities,$description,$capacity,$status,$id]);
    $success = 'Room updated successfully!';
    $stmt->execute([$id]);
    $room = $stmt->fetch();
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Room — Admin</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-wrapper">
<header class="topbar">
  <div class="topbar-logo"><span class="topbar-logo-red">boarding</span>house<span class="topbar-badge">Admin Panel</span></div>
  <div class="topbar-right">
    <div class="topbar-user"><div class="topbar-avatar"><?= strtoupper(substr($_SESSION['admin_name']??'A',0,1)) ?></div></div>
    <a href="logout.php" class="topbar-logout">Logout</a>
  </div>
</header>
<aside class="sidebar">
  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="admin_dashboard.php" class="nav-item"><svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>Dashboard</a>
    <a href="add_room.php" class="nav-item active"><svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>Rooms</a>
  </nav>
</aside>
<main class="main-content">
  <div class="page-header-row">
    <div>
      <div class="page-title">Edit Room <?= htmlspecialchars($room['room_number']) ?></div>
      <div class="page-subtitle">Update room details and status.</div>
    </div>
    <a href="admin_dashboard.php" class="btn btn-outline btn-sm">← Back to Dashboard</a>
  </div>
  <div style="max-width:700px;">
    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?><div class="alert alert-success"><?= $success ?></div><?php endif; ?>
    <div class="card">
      <div class="card-header">
        <div class="card-title">Room Details</div>
        <span class="pill <?= $room['status']==='available'?'pill-success':($room['status']==='occupied'?'pill-danger':'pill-warning') ?>"><?= ucfirst($room['status']) ?></span>
      </div>
      <div class="card-body">
        <form method="POST">
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Room Number *</label>
              <input type="text" name="room_number" class="form-control" required value="<?= htmlspecialchars($room['room_number']) ?>">
            </div>
            <div class="form-group">
              <label class="form-label">Room Type *</label>
              <select name="room_type" class="form-control" required>
                <?php foreach(['Single Room','Double Room','Triple Room','Suite','Dormitory'] as $t): ?>
                <option value="<?= $t ?>" <?= $room['room_type']===$t?'selected':'' ?>><?= $t ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="grid-2">
            <div class="form-group">
              <label class="form-label">Floor</label>
              <select name="floor" class="form-control">
                <?php for($i=1;$i<=10;$i++): ?>
                <option value="<?= $i ?>" <?= $room['floor']==$i?'selected':'' ?>>Floor <?= $i ?></option>
                <?php endfor; ?>
              </select>
            </div>
            <div class="form-group">
              <label class="form-label">Status</label>
              <select name="status" class="form-control">
                <option value="available" <?= $room['status']==='available'?'selected':'' ?>>Available</option>
                <option value="occupied"  <?= $room['status']==='occupied'?'selected':'' ?>>Occupied</option>
                <option value="reserved"  <?= $room['status']==='reserved'?'selected':'' ?>>Reserved</option>
                <option value="maintenance" <?= $room['status']==='maintenance'?'selected':'' ?>>Maintenance</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label class="form-label">Monthly Rent (₹) *</label>
            <input type="number" name="price" class="form-control" min="0" step="100" required value="<?= $room['price'] ?>">
          </div>
          <div class="form-group">
            <label class="form-label">Amenities</label>
            <input type="text" name="amenities" class="form-control" value="<?= htmlspecialchars($room['amenities']??'') ?>" placeholder="WiFi, AC, Bathroom...">
          </div>
          <div class="form-group">
            <label class="form-label">Capacity</label>
            <select name="capacity" class="form-control">
              <?php for($i=1;$i<=6;$i++): ?>
              <option value="<?= $i ?>" <?= ($room['capacity']??1)==$i?'selected':'' ?>><?= $i ?> person<?= $i>1?'s':'' ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="form-group">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control"><?= htmlspecialchars($room['description']??'') ?></textarea>
          </div>
          <div style="display:flex;gap:12px;">
            <button type="submit" class="btn btn-primary" style="padding:12px 28px;">Save Changes</button>
            <a href="admin_dashboard.php" class="btn btn-outline" style="padding:12px 20px;">Cancel</a>
            <a href="delete_room.php?id=<?= $id ?>" class="btn btn-danger" style="margin-left:auto;padding:12px 20px;" onclick="return confirm('Delete this room permanently?')">Delete Room</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>
</div>
</body>
</html>
