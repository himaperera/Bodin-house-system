<?php
// ============================================================
//  db.php — Database Connection
//  BoardingHouse Management System
//  Uses PDO with error handling
// ============================================================

// ---- Configuration — change these to match your server ----
define('DB_HOST', 'localhost');
define('DB_NAME', 'boarding_system');
define('DB_USER', 'root');        // your MySQL username
define('DB_PASS', '');            // your MySQL password
define('DB_CHARSET', 'utf8mb4');
define('DB_PORT', '3307');        // ✅ custom port added
// -----------------------------------------------------------

$dsn = "mysql:host=" . DB_HOST
  . ";port=" . DB_PORT        // ✅ port added to DSN
  . ";dbname=" . DB_NAME
  . ";charset=" . DB_CHARSET;

$options = [
  PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES => false,
];

try {
  $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
  http_response_code(500);
  ?>
  <!DOCTYPE html>
  <html lang="en">

  <head>
    <meta charset="UTF-8">
    <title>Database Error — BoardingHouse</title>
    <style>
      * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
      }

      body {
        font-family: 'Segoe UI', sans-serif;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100vh;
      }

      .box {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #dee2e6;
        padding: 40px;
        max-width: 480px;
        text-align: center;
      }

      .icon {
        width: 56px;
        height: 56px;
        background: #fdecea;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
      }

      .icon svg {
        width: 28px;
        height: 28px;
        fill: #b72b37;
      }

      h1 {
        font-size: 20px;
        font-weight: 800;
        color: #1a1a2e;
        margin-bottom: 10px;
      }

      p {
        font-size: 14px;
        color: #6c757d;
        line-height: 1.7;
        margin-bottom: 16px;
      }

      code {
        background: #f1f3f5;
        border-radius: 6px;
        padding: 10px 14px;
        display: block;
        font-size: 12px;
        text-align: left;
        color: #495057;
        word-break: break-all;
      }

      .steps {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 16px;
        text-align: left;
        margin-top: 16px;
      }

      .steps li {
        font-size: 13px;
        color: #495057;
        margin-bottom: 8px;
        margin-left: 16px;
      }
    </style>
  </head>

  <body>
    <div class="box">
      <div class="icon"><svg viewBox="0 0 24 24">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z" />
        </svg></div>
      <h1>Database Connection Failed</h1>
      <p>Could not connect to the MySQL database. Please check your configuration in <strong>db.php</strong>.</p>
      <code><?= htmlspecialchars($e->getMessage()) ?></code>
      <div class="steps">
        <strong style="font-size:13px;display:block;margin-bottom:8px;">Steps to fix:</strong>
        <ol>
          <li>Open <code style="display:inline;padding:2px 6px;">db.php</code> and update <code
              style="display:inline;padding:2px 6px;">DB_USER</code> and <code
              style="display:inline;padding:2px 6px;">DB_PASS</code></li>
          <li>Import <code style="display:inline;padding:2px 6px;">boarding_system.sql</code> into phpMyAdmin or run:<br>
            <code
              style="display:inline-block;margin-top:4px;padding:4px 8px;">mysql -u root -p &lt; boarding_system.sql</code>
          </li>
          <li>Make sure MySQL/MariaDB service is running on port <strong>3307</strong></li>
        </ol>
      </div>
    </div>
  </body>

  </html>
  <?php
  exit;
}