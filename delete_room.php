<?php
/* delete_room.php */
session_start();
if(!isset($_SESSION['admin_id'])) { header('Location: admin_login.php'); exit; }
require_once 'db.php';
$id = (int)($_GET['id'] ?? 0);
if($id) {
  $pdo->prepare("DELETE FROM bookings WHERE room_id = ?")->execute([$id]);
  $pdo->prepare("DELETE FROM rooms WHERE id = ?")->execute([$id]);
}
header('Location: admin_dashboard.php?deleted=1');
exit;
