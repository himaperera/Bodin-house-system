<?php
session_start();
if(!isset($_SESSION['user_id'])) { header('Location: login.php'); exit; }
require_once 'db.php';
$room_id = $_GET['room_id'] ?? null;
$room = null;
if($room_id) {
  $stmt = $pdo->prepare("SELECT * FROM rooms WHERE id = ? AND status = 'available'");
  $stmt->execute([$room_id]);
  $room = $stmt->fetch();
}
$error = ''; $success = '';
if($_SERVER['REQUEST_METHOD'] === 'POST') {
  $rid = (int)$_POST['room_id'];
  $check_in  = $_POST['check_in'] ?? '';
  $check_out = $_POST['check_out'] ?? '';
  $r = $pdo->prepare("SELECT * FROM rooms WHERE id = ? AND status = 'available'");
  $r->execute([$rid]);
  $selectedRoom = $r->fetch();
  if(!$selectedRoom) { $error = 'Room is no longer available.'; }
  elseif(!$check_in || !$check_out) { $error = 'Please select check-in and check-out dates.'; }
  elseif(strtotime($check_out) <= strtotime($check_in)) { $error = 'Check-out date must be after check-in date.'; }
  else {
    $months = max(1, round((strtotime($check_out) - strtotime($check_in)) / (30*24*3600)));
    $amount = $selectedRoom['price'] * $months;
    $ins = $pdo->prepare("INSERT INTO bookings (user_id, room_id, check_in, check_out, amount, status, created_at) VALUES (?,?,?,?,?,'pending',NOW())");
    $ins->execute([$_SESSION['user_id'], $rid, $check_in, $check_out, $amount]);
    $upd = $pdo->prepare("UPDATE rooms SET status='reserved' WHERE id=?");
    $upd->execute([$rid]);
    $success = 'Booking submitted successfully! Your booking is pending admin confirmation.';
  }
}
$allRooms = $pdo->query("SELECT * FROM rooms WHERE status='available' ORDER BY room_number")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book a Room — BoardingHouse</title>
<link rel="stylesheet" href="style.css">
<style>
body { background: var(--offwhite); }
.page-navbar { background: #fff; border-bottom: 1px solid var(--border); padding: 0 40px; height: 64px; display: flex; align-items: center; justify-content: space-between; }
.nav-logo { display: flex; align-items: center; gap: 8px; font-size: 19px; font-weight: 800; text-decoration: none; }
.logo-pin { width: 28px; height: 28px; background: var(--red); border-radius: 50% 50% 50% 0; display: flex; align-items: center; justify-content: center; }
.logo-pin svg { width: 13px; height: 13px; fill: #fff; }
.book-page { max-width: 980px; margin: 36px auto; padding: 0 24px; display: grid; grid-template-columns: 1fr 380px; gap: 28px; align-items: start; }
.book-form-card { background: #fff; border-radius: 14px; border: 1px solid var(--border); padding: 32px; }
.book-form-card h1 { font-size: 22px; font-weight: 800; color: var(--navy); margin-bottom: 6px; }
.book-form-card .sub { font-size: 14px; color: var(--muted); margin-bottom: 24px; }
.room-select-card { border: 1.5px solid var(--border); border-radius: 10px; padding: 14px 16px; cursor: pointer; margin-bottom: 8px; transition: all .15s; display: flex; align-items: center; gap: 12px; }
.room-select-card:hover, .room-select-card.selected { border-color: var(--red); background: #fff5f5; }
.room-select-card input[type=radio] { accent-color: var(--red); width: 16px; height: 16px; }
.date-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.price-summary { background: #fff; border-radius: 14px; border: 1px solid var(--border); padding: 24px; }
.price-summary h3 { font-size: 16px; font-weight: 700; margin-bottom: 18px; }
.summary-row { display: flex; justify-content: space-between; font-size: 14px; padding: 8px 0; border-bottom: 1px solid var(--light); }
.summary-row:last-of-type { border: none; }
.summary-total { display: flex; justify-content: space-between; font-size: 17px; font-weight: 800; color: var(--red); padding-top: 12px; margin-top: 4px; border-top: 2px solid var(--border); }
.room-info-box { background: var(--offwhite); border-radius: 10px; padding: 16px; margin-bottom: 20px; }
.room-info-box h4 { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
</style>
</head>
<body>

<nav class="page-navbar">
  <a href="index.php" class="nav-logo">
    <div class="logo-pin"><svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5S10.62 6.5 12 6.5s2.5 1.12 2.5 2.5S13.38 11.5 12 11.5z"/></svg></div>
    <span style="color:var(--red)">boarding</span><span style="color:var(--navy)">house</span>
  </a>
  <div style="display:flex;gap:8px;">
    <a href="rooms.php" class="btn btn-ghost btn-sm">← Back to Rooms</a>
    <a href="my_bookings.php" class="btn btn-outline btn-sm">My Bookings</a>
  </div>
</nav>

<div class="book-page">
  <div class="book-form-card">
    <h1>Book a Room</h1>
    <p class="sub">Select your room and preferred dates to submit a booking request.</p>

    <?php if($error): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
    <?php if($success): ?>
    <div class="alert alert-success">
      <?= $success ?>
      <a href="my_bookings.php" style="font-weight:700;margin-left:8px;">View My Bookings →</a>
    </div>
    <?php endif; ?>

    <?php if(!$success): ?>
    <form method="POST" id="bookForm">
      <div class="form-group">
        <label class="form-label">Select Room</label>
        <?php foreach($allRooms as $r): ?>
        <label class="room-select-card <?= ($room && $room['id']==$r['id']) ? 'selected' : '' ?>" onclick="selectRoom(this, <?= $r['price'] ?>, '<?= htmlspecialchars($r['room_number']) ?>', '<?= htmlspecialchars($r['room_type']) ?>')">
          <input type="radio" name="room_id" value="<?= $r['id'] ?>" <?= ($room && $room['id']==$r['id']) ? 'checked' : '' ?> required>
          <div style="flex:1;">
            <div style="font-weight:700;">Room <?= htmlspecialchars($r['room_number']) ?> — <?= htmlspecialchars($r['room_type']) ?></div>
            <div style="font-size:12px;color:var(--muted);">Floor <?= $r['floor']??1 ?> · <?= htmlspecialchars($r['amenities']??'') ?></div>
          </div>
          <div style="font-weight:800;color:var(--red);">₹<?= number_format($r['price']) ?>/mo</div>
        </label>
        <?php endforeach; ?>
        <?php if(empty($allRooms)): ?>
        <p style="color:var(--muted);font-size:14px;">No available rooms at the moment. <a href="rooms.php" style="color:var(--red)">Check back soon</a>.</p>
        <?php endif; ?>
      </div>

      <div class="date-row">
        <div class="form-group">
          <label class="form-label">Check-in Date</label>
          <input type="date" name="check_in" id="checkIn" class="form-control" min="<?= date('Y-m-d') ?>" required onchange="calcTotal()">
        </div>
        <div class="form-group">
          <label class="form-label">Check-out Date</label>
          <input type="date" name="check_out" id="checkOut" class="form-control" min="<?= date('Y-m-d', strtotime('+1 month')) ?>" required onchange="calcTotal()">
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Special Requests (optional)</label>
        <textarea name="notes" class="form-control" placeholder="Any special requirements or notes for the admin..."></textarea>
      </div>

      <button type="submit" class="btn btn-primary btn-full" style="padding:13px;font-size:15px;">Submit Booking Request</button>
      <p style="font-size:12px;color:var(--muted);text-align:center;margin-top:10px;">Your booking will be reviewed and confirmed by admin within 24 hours.</p>
    </form>
    <?php endif; ?>
  </div>

  <!-- SIDEBAR SUMMARY -->
  <div>
    <div class="price-summary">
      <h3>Booking Summary</h3>
      <div class="room-info-box">
        <div style="font-size:12px;color:var(--muted);margin-bottom:4px;">Selected Room</div>
        <h4 id="summRoomNum"><?= $room ? 'Room '.$room['room_number'] : 'Not selected' ?></h4>
        <div style="font-size:13px;color:var(--muted)" id="summRoomType"><?= $room ? $room['room_type'] : '' ?></div>
      </div>
      <div class="summary-row"><span>Room Rate</span><span id="summRate"><?= $room ? '₹'.number_format($room['price']).'/mo' : '—' ?></span></div>
      <div class="summary-row"><span>Duration</span><span id="summDuration">—</span></div>
      <div class="summary-row"><span>Check-in</span><span id="summIn">—</span></div>
      <div class="summary-row"><span>Check-out</span><span id="summOut">—</span></div>
      <div class="summary-total"><span>Total Estimate</span><span id="summTotal"><?= $room ? '₹'.number_format($room['price']) : '—' ?></span></div>
    </div>

    <div style="background:var(--offwhite);border-radius:12px;padding:18px;margin-top:16px;font-size:13px;color:var(--muted);line-height:1.7;">
      <div style="font-weight:700;color:var(--text);margin-bottom:8px;">How it works</div>
      <div>1. Submit your booking request</div>
      <div>2. Admin reviews and confirms within 24h</div>
      <div>3. You receive a confirmation notification</div>
      <div>4. Pay on check-in day</div>
    </div>
  </div>
</div>

<script>
var roomPrice = <?= $room ? $room['price'] : 0 ?>;
function selectRoom(el, price, num, type) {
  document.querySelectorAll('.room-select-card').forEach(c => c.classList.remove('selected'));
  el.classList.add('selected');
  roomPrice = price;
  document.getElementById('summRoomNum').textContent = 'Room ' + num;
  document.getElementById('summRoomType').textContent = type;
  document.getElementById('summRate').textContent = '₹' + price.toLocaleString() + '/mo';
  calcTotal();
}
function calcTotal() {
  var ci = document.getElementById('checkIn').value;
  var co = document.getElementById('checkOut').value;
  if(ci && co && roomPrice) {
    var ms = new Date(co) - new Date(ci);
    var months = Math.max(1, Math.round(ms / (30*24*3600*1000)));
    document.getElementById('summDuration').textContent = months + ' month' + (months>1?'s':'');
    document.getElementById('summIn').textContent = new Date(ci).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
    document.getElementById('summOut').textContent = new Date(co).toLocaleDateString('en-IN',{day:'numeric',month:'short',year:'numeric'});
    document.getElementById('summTotal').textContent = '₹' + (roomPrice * months).toLocaleString();
  }
}
</script>
</body>
</html>
