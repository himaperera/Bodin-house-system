<?php
session_start();
$wasAdmin = isset($_SESSION['admin_id']);
session_destroy();
header('Location: ' . ($wasAdmin ? 'admin_login.php' : 'login.php'));
exit;
