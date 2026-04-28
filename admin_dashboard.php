<?php
session_start();
if(!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }
require_once 'db.php';
$adminName = $_SESSION['admin_name'] ?? 'Admin';

$totalRooms   = $pdo->query("SELECT COUNT(*) FROM rooms")->fetchColumn();
$occupiedRooms= $pdo->query("SELECT COUNT(*) FROM rooms WHERE status='occupied'")->fetchColumn();
$availRooms   = $pdo->query("SELECT COUNT(*) FROM rooms WHERE status='available'")->fetchColumn();
$totalBookings= $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBk    = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='pending'")->fetchColumn();
$revenue      = $pdo->query("SELECT SUM(amount) FROM bookings WHERE status='confirmed'")->fetchColumn() ?? 0;

$recentBookings = $pdo->query("
  SELECT b.*, u.name as tenant_name, r.room_number, r.room_type
  FROM bookings b
  JOIN users u ON b.user_id = u.id
  JOIN rooms r ON b.room_id = r.id
  ORDER BY b.created_at DESC LIMIT 8
")->fetchAll();

$rooms = $pdo->query("
  SELECT r.*, u.name as tenant_name
  FROM rooms r
  LEFT JOIN bookings b ON b.room_id = r.id AND b.status = 'confirmed'
  LEFT JOIN users u ON b.user_id = u.id
  ORDER BY r.room_number LIMIT 10
")->fetchAll();

$hour = (int)date('H');
$greet = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$occupancy = $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard — BoardingHouse</title>
<link rel="stylesheet" href="style.css">
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
      <div class="topbar-avatar"><?= strtoupper(substr($adminName,0,1)) ?></div>
      <span><?= htmlspecialchars($adminName) ?></span>
    </div>
    <a href="logout.php" class="topbar-logout">Logout</a>
  </div>
</header>

<!-- SIDEBAR -->
<aside class="sidebar">
  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="admin_dashboard.php" class="nav-item active">
      <svg viewBox="0 0 24 24"><path d="M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z"/></svg>
      Dashboard
    </a>
    <a href="add_room.php" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
      Rooms
    </a>
    <a href="admin_dashboard.php?tab=bookings" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-1V1h-2zm3 18H5V8h14v11z"/></svg>
      Bookings
    </a>
    <a href="admin_dashboard.php?tab=tenants" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/></svg>
      Tenants
    </a>
    <div class="nav-section-label" style="margin-top:8px;">Management</div>
    <a href="add_room.php" class="nav-item">
      <svg viewBox="0 0 24 24"><path d="M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z"/></svg>
      Add Room
    </a>
    <a href="admin_login.php" class="nav-item" style="margin-top:auto;">
      <svg viewBox="0 0 24 24"><path d="M17 7l-1.41 1.41L18.17 11H8v2h10.17l-2.58 2.58L17 17l5-5zM4 5h8V3H4c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h8v-2H4V5z"/></svg>
      Logout
    </a>
  </nav>
  <div class="sidebar-bottom">
    <div class="sidebar-user-card">
      <div class="topbar-avatar" style="width:36px;height:36px;"><?= strtoupper(substr($adminName,0,1)) ?></div>
      <div>
        <div class="sidebar-user-name"><?= htmlspecialchars($adminName) ?></div>
        <div class="sidebar-user-role">Administrator</div>
      </div>
    </div>
  </div>
</aside>

<!-- MAIN -->
<main class="main-content">
  <div class="page-header-row">
    <div>
      <div class="page-title"><?= $greet ?>, <?= htmlspecialchars($adminName) ?> 👋</div>
      <div class="page-subtitle">Here's what's happening at your boarding house today — <?= date('l, F j, Y') ?></div>
    </div>
    <div style="display:flex;gap:10px;">
      <a href="add_room.php" class="btn btn-outline btn-sm">+ Add Room</a>
      <a href="book.php" class="btn btn-primary btn-sm">+ New Booking</a>
    </div>
  </div>

  <!-- STATS -->
  <div class="stat-grid">
    <div class="stat-card" style="border-top-color:var(--red)">
      <div class="stat-label">Total Rooms</div>
      <div class="stat-value"><?= $totalRooms ?></div>
      <div class="stat-sub"><?= $availRooms ?> available now</div>
    </div>
    <div class="stat-card" style="border-top-color:var(--green)">
      <div class="stat-label">Occupied</div>
      <div class="stat-value"><?= $occupiedRooms ?></div>
      <div class="stat-sub stat-up"><?= $occupancy ?>% occupancy rate</div>
    </div>
    <div class="stat-card" style="border-top-color:var(--amber)">
      <div class="stat-label">Total Bookings</div>
      <div class="stat-value"><?= $totalBookings ?></div>
      <div class="stat-sub stat-down"><?= $pendingBk ?> pending review</div>
    </div>
    <div class="stat-card" style="border-top-color:#5c6bc0">
      <div class="stat-label">Total Revenue</div>
      <div class="stat-value">₹<?= number_format($revenue) ?></div>
      <div class="stat-sub stat-up">Confirmed bookings</div>
    </div>
  </div>

  <!-- OCCUPANCY BAR + QUICK ACTIONS -->
  <div class="grid-2" style="margin-bottom:22px;">
    <div class="card">
      <div class="card-header">
        <div class="card-title">Occupancy Overview</div>
        <span class="pill <?= $occupancy >= 75 ? 'pill-success' : ($occupancy >= 50 ? 'pill-warning' : 'pill-danger') ?>"><?= $occupancy ?>% full</span>
      </div>
      <div class="card-body">
        <?php
        $floors = ['Floor 1'=>90,'Floor 2'=>75,'Floor 3'=>60,'Floor 4'=>45,'Floor 5'=>80];
        foreach($floors as $f=>$p): $c = $p>=75?'var(--red)':($p>=50?'var(--amber)':'var(--green)'); ?>
        <div class="occ-row">
          <div class="occ-label"><?= $f ?></div>
          <div class="occ-bg"><div class="occ-fill" style="width:<?= $p ?>%;background:<?= $c ?>"></div></div>
          <div class="occ-pct"><?= $p ?>%</div>
        </div>
        <?php endforeach; ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:16px;">
          <div style="background:var(--offwhite);border-radius:8px;padding:12px;text-align:center;">
            <div style="font-size:22px;font-weight:800;color:var(--green)"><?= $availRooms ?></div>
            <div style="font-size:11px;color:var(--muted);">Available</div>
          </div>
          <div style="background:var(--offwhite);border-radius:8px;padding:12px;text-align:center;">
            <div style="font-size:22px;font-weight:800;color:var(--red)"><?= $occupiedRooms ?></div>
            <div style="font-size:11px;color:var(--muted);">Occupied</div>
          </div>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-header"><div class="card-title">Quick Actions</div></div>
      <div class="card-body" style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
        <?php
        $actions = [
          ['add_room.php','M19 13h-6v6h-2v-6H5v-2h6V5h2v6h6v2z','Add New Room','List a new room with details'],
          ['book.php','M17 12h-5v5h5v-5zM16 1v2H8V1H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V5a2 2 0 0 0-2-2h-1V1h-2zm3 18H5V8h14v11z','New Booking','Create a booking manually'],
          ['rooms.php','M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z','View Rooms','See all rooms & status'],
          ['register.php','M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z','Add Tenant','Register a new tenant'],
        ];
        foreach($actions as $a): ?>
        <a href="<?= $a[0] ?>" style="background:var(--offwhite);border-radius:10px;padding:16px;text-decoration:none;display:flex;flex-direction:column;align-items:flex-start;gap:8px;border:1.5px solid transparent;transition:all .2s;" onmouseover="this.style.borderColor='var(--red)';this.style.background='#fff'" onmouseout="this.style.borderColor='transparent';this.style.background='var(--offwhite)'">
          <div style="width:36px;height:36px;background:var(--red-light);border-radius:8px;display:flex;align-items:center;justify-content:center;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="var(--red)"><path d="<?= $a[1] ?>"/></svg>
          </div>
          <div>
            <div style="font-size:13px;font-weight:700;color:var(--text)"><?= $a[2] ?></div>
            <div style="font-size:12px;color:var(--muted)"><?= $a[3] ?></div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- RECENT BOOKINGS -->
  <div class="card" style="margin-bottom:22px;">
    <div class="card-header">
      <div class="card-title">Recent Bookings</div>
      <a href="my_bookings.php" style="font-size:13px;color:var(--red);font-weight:600;">View All →</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead>
          <tr>
            <th>#</th><th>Tenant</th><th>Room</th><th>Type</th><th>Check-in</th><th>Check-out</th><th>Amount</th><th>Status</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if(empty($recentBookings)): ?>
          <tr><td colspan="9" style="text-align:center;color:var(--muted);padding:30px;">No bookings yet. <a href="book.php" style="color:var(--red)">Create the first one</a></td></tr>
          <?php else: foreach($recentBookings as $i=>$b):
            $statusClass = $b['status'] === 'confirmed' ? 'pill-success' : ($b['status'] === 'pending' ? 'pill-warning' : 'pill-danger');
            $initials = implode('', array_map(fn($w)=>$w[0], explode(' ', $b['tenant_name'])));
          ?>
          <tr>
            <td style="color:var(--muted);font-size:12px;">#<?= str_pad($b['id'],4,'0',STR_PAD_LEFT) ?></td>
            <td>
              <div style="display:flex;align-items:center;gap:8px;">
                <div class="avatar"><?= strtoupper(substr($initials,0,2)) ?></div>
                <?= htmlspecialchars($b['tenant_name']) ?>
              </div>
            </td>
            <td style="font-weight:600;">Rm <?= htmlspecialchars($b['room_number']) ?></td>
            <td style="color:var(--muted);"><?= htmlspecialchars($b['room_type']) ?></td>
            <td style="color:var(--muted);font-size:12px;"><?= date('d M Y', strtotime($b['check_in'])) ?></td>
            <td style="color:var(--muted);font-size:12px;"><?= date('d M Y', strtotime($b['check_out'])) ?></td>
            <td style="font-weight:700;color:var(--red);">₹<?= number_format($b['amount']) ?></td>
            <td><span class="pill <?= $statusClass ?>"><?= ucfirst($b['status']) ?></span></td>
            <td>
              <div style="display:flex;gap:6px;">
                <a href="update_booking.php?id=<?= $b['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                <?php if($b['status']==='pending'): ?>
                <a href="update_booking.php?id=<?= $b['id'] ?>&action=confirm" class="btn btn-sm" style="background:var(--green-light);color:#1b5e20;border:1px solid #a5d6a7;">Confirm</a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- ROOMS TABLE -->
  <div class="card">
    <div class="card-header">
      <div class="card-title">Rooms Overview</div>
      <a href="add_room.php" class="btn btn-primary btn-sm">+ Add Room</a>
    </div>
    <div class="table-wrap">
      <table class="data-table">
        <thead><tr><th>Room No.</th><th>Type</th><th>Floor</th><th>Rent/Month</th><th>Amenities</th><th>Current Tenant</th><th>Status</th><th>Actions</th></tr></thead>
        <tbody>
          <?php if(empty($rooms)): ?>
          <tr><td colspan="8" style="text-align:center;color:var(--muted);padding:30px;">No rooms added yet. <a href="add_room.php" style="color:var(--red)">Add your first room</a></td></tr>
          <?php else: foreach($rooms as $r):
            $sc = $r['status']==='available' ? 'pill-success' : ($r['status']==='occupied' ? 'pill-danger' : 'pill-warning');
          ?>
          <tr>
            <td style="font-weight:700;">Rm <?= htmlspecialchars($r['room_number']) ?></td>
            <td style="color:var(--muted);"><?= htmlspecialchars($r['room_type']) ?></td>
            <td style="color:var(--muted);">Floor <?= htmlspecialchars($r['floor'] ?? 1) ?></td>
            <td style="font-weight:700;color:var(--red);">₹<?= number_format($r['price']) ?></td>
            <td style="font-size:12px;color:var(--muted);"><?= htmlspecialchars($r['amenities'] ?? '—') ?></td>
            <td><?= $r['tenant_name'] ? '<div style="display:flex;align-items:center;gap:6px;"><div class="avatar" style="width:26px;height:26px;font-size:10px;">'.strtoupper(substr($r['tenant_name'],0,2)).'</div>'.htmlspecialchars($r['tenant_name']).'</div>' : '<span style="color:var(--muted);">—</span>' ?></td>
            <td><span class="pill <?= $sc ?>"><?= ucfirst($r['status']) ?></span></td>
            <td>
              <div style="display:flex;gap:6px;">
                <a href="edit_room.php?id=<?= $r['id'] ?>" class="btn btn-ghost btn-sm">Edit</a>
                <a href="delete_room.php?id=<?= $r['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Delete this room?')">Delete</a>
              </div>
            </td>
          </tr>
          <?php endforeach; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>
</div>
</body>
</html>
