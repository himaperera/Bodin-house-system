<?php
session_start();
// ලොග් වී නොමැති නම් Login පිටුවට යවන්න
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once 'db.php';

$error = '';
$success = '';

// විශ්වවිද්‍යාල ලැයිස්තුව Dropdown එකට ලබා ගැනීම
$stmt_uni = $pdo->query("SELECT * FROM universities ORDER BY name ASC");
$universities = $stmt_uni->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_number = trim($_POST['room_number']);
    $university_id = $_POST['university_id'];
    $distance = $_POST['distance_from_uni_km'];
    $room_type = trim($_POST['room_type']);
    $floor = $_POST['floor'];
    $capacity = $_POST['capacity'];
    $price = $_POST['price'];
    $amenities = trim($_POST['amenities']);
    $description = trim($_POST['description']);
    $submitted_by = $_SESSION['user_id'];
    $status = 'pending_approval'; // Admin approve කරනකම් pending

    // පින්තූර Upload කිරීමේ ක්‍රියාවලිය
    $uploaded_images = [];
    $upload_dir = 'uploads/'; // අනිවාර්යයෙන්ම මේ ෆෝල්ඩරය හදලා තියෙන්න ඕනේ

    if (!empty($_FILES['room_images']['name'][0])) {
        $file_count = count($_FILES['room_images']['name']);

        if ($file_count > 4) {
            $error = 'You can only upload a maximum of 4 images.';
        } else {
            for ($i = 0; $i < $file_count; $i++) {
                $file_tmp = $_FILES['room_images']['tmp_name'][$i];
                $file_name = time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "", basename($_FILES['room_images']['name'][$i]));
                $file_path = $upload_dir . $file_name;

                // Allow only images
                $file_type = strtolower(pathinfo($file_path, PATHINFO_EXTENSION));
                $allowed_types = ['jpg', 'jpeg', 'png', 'webp'];

                if (in_array($file_type, $allowed_types)) {
                    if (move_uploaded_file($file_tmp, $file_path)) {
                        $uploaded_images[] = $file_path;
                    }
                }
            }
        }
    }

    $images_json = json_encode($uploaded_images); // පින්තූර වල paths JSON විදිහට DB එකේ save කරනවා

    if (empty($error)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO rooms (room_number, university_id, distance_from_uni_km, room_type, floor, capacity, price, amenities, description, submitted_by, status, images) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$room_number, $university_id, $distance, $room_type, $floor, $capacity, $price, $amenities, $description, $submitted_by, $status, $images_json]);
            $success = "Your Ad has been submitted successfully! It will be visible after admin approval.";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) { // Duplicate Room Number
                $error = "This Room Number already exists in the system.";
            } else {
                $error = "Something went wrong. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post an Ad — BoardingRooms</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --navy-deep: #020617;
            --navy-tint: rgba(2, 6, 23, 0.85);
            --glass-bg: rgba(255, 255, 255, 0.05);
            --glass-border: rgba(255, 255, 255, 0.12);
            --text-main: #f8fafc;
            --text-muted: #cbd5e1;
            --red: #ef4444;
            --blue-accent: #38bdf8;
            --green-accent: #10b981;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            color: var(--text-main);
            background-color: var(--navy-deep);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        body::before {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://images.unsplash.com/photo-1554995207-c18c203602cb?auto=format&fit=crop&q=80&w=1600');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            z-index: -2;
        }

        body::after {
            content: "";
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--navy-tint);
            backdrop-filter: blur(10px);
            z-index: -1;
        }

        /* Navbar */
        .navbar {
            background: rgba(2, 6, 23, 0.6);
            backdrop-filter: blur(15px);
            border-bottom: 1px solid var(--glass-border);
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 70px;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .navbar-logo {
            font-size: 22px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            color: #fff;
        }

        .logo-pin {
            width: 32px;
            height: 32px;
            background: var(--red);
            border-radius: 50% 50% 50% 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
        }

        .btn-outline-nav {
            border: 1px solid var(--glass-border);
            color: #fff;
            padding: 8px 16px;
            border-radius: 12px;
            text-decoration: none;
            transition: 0.3s;
            font-size: 14px;
            font-weight: 600;
        }

        .btn-outline-nav:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Form Container */
        .form-container {
            max-width: 900px;
            margin: 50px auto;
            padding: 0 20px;
            width: 100%;
            box-sizing: border-box;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-box {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.5);
        }

        .form-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .form-header h2 {
            font-size: 28px;
            font-weight: 900;
            margin: 0 0 10px 0;
            color: #fff;
        }

        .form-header h2 span {
            color: var(--blue-accent);
        }

        .form-header p {
            color: var(--text-muted);
            font-size: 15px;
            margin: 0;
        }

        /* Inputs */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--text-muted);
        }

        .form-control,
        .form-select {
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--glass-border);
            color: #fff;
            border-radius: 12px;
            padding: 14px 16px;
            width: 100%;
            box-sizing: border-box;
            font-size: 15px;
            font-family: 'Inter', sans-serif;
            transition: all 0.3s ease;
        }

        .form-select option {
            background: var(--navy-deep);
            color: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--blue-accent);
            outline: none;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.2);
            background: rgba(0, 0, 0, 0.5);
        }

        /* File Upload Styling */
        .file-upload-wrapper {
            border: 2px dashed var(--blue-accent);
            background: rgba(56, 189, 248, 0.05);
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            position: relative;
            transition: 0.3s;
        }

        .file-upload-wrapper:hover {
            background: rgba(56, 189, 248, 0.1);
        }

        .file-upload-wrapper input[type="file"] {
            position: absolute;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            opacity: 0;
            cursor: pointer;
        }

        .file-upload-content i {
            font-size: 40px;
            color: var(--blue-accent);
            margin-bottom: 10px;
        }

        .file-upload-content p {
            color: var(--text-main);
            margin: 0;
            font-weight: 600;
        }

        .file-upload-content span {
            color: var(--text-muted);
            font-size: 12px;
        }

        .btn-submit {
            background: var(--blue-accent);
            color: #000;
            border: none;
            border-radius: 12px;
            padding: 16px;
            width: 100%;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            margin-top: 20px;
            transition: 0.3s;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            background: #fff;
            box-shadow: 0 10px 20px rgba(56, 189, 248, 0.3);
        }

        .alert {
            padding: 14px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            color: #ff8a8a;
            border: 1px solid var(--red);
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.15);
            color: var(--green-accent);
            border: 1px solid var(--green-accent);
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }
        }
    </style>
</head>

<body>

    <nav class="navbar">
        <a href="index.php" class="navbar-logo">
            <div class="logo-pin">🏠</div>
            <span style="color:var(--red)">boarding</span><span>rooms</span>
        </a>
        <div style="display: flex; gap:15px; align-items:center;">
            <a href="my_bookings.php" class="btn-outline-nav"
                style="border:none; color:var(--text-muted);">Dashboard</a>
            <a href="logout.php" class="btn-outline-nav"><i class="fa-solid fa-arrow-right-from-bracket"></i> Logout</a>
        </div>
    </nav>

    <div class="form-container">
        <div class="form-box">

            <div class="form-header">
                <i class="fa-solid fa-bullhorn"
                    style="font-size: 40px; color: var(--blue-accent); margin-bottom: 15px;"></i>
                <h2>Post an Ad <span>for your Room</span></h2>
                <p>Fill out the details below to list your boarding room.</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger"><i class="fa-solid fa-triangle-exclamation"></i>
                    <?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fa-solid fa-circle-check"></i> <?= $success ?>
                    <a href="my_bookings.php"
                        style="margin-left: auto; color: #fff; font-weight:bold; text-decoration:none;">Go to Dashboard
                        ➔</a>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Room Number / Reference</label>
                        <input type="text" name="room_number" class="form-control" placeholder="e.g. A-102 or NSBM-1"
                            required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nearest University</label>
                        <select name="university_id" class="form-select" required>
                            <option value="" disabled selected>Select a University</option>
                            <?php foreach ($universities as $uni): ?>
                                <option value="<?= $uni['id'] ?>"><?= htmlspecialchars($uni['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Distance from Uni (KM)</label>
                        <input type="number" step="0.1" name="distance_from_uni_km" class="form-control"
                            placeholder="e.g. 1.5" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Room Type</label>
                        <select name="room_type" class="form-select" required>
                            <option value="Single Room">Single Room</option>
                            <option value="Double Room">Double Room (Shared)</option>
                            <option value="Triple Room">Triple Room (Shared)</option>
                            <option value="Suite">Studio / Suite</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Floor</label>
                        <input type="number" name="floor" class="form-control" placeholder="e.g. 1" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Capacity (Persons)</label>
                        <input type="number" name="capacity" class="form-control" placeholder="e.g. 2" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Monthly Price (LKR)</label>
                        <input type="number" name="price" class="form-control" placeholder="e.g. 15000" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Amenities (Comma separated)</label>
                        <input type="text" name="amenities" class="form-control"
                            placeholder="e.g. WiFi, Attached Bathroom, AC">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="4"
                        placeholder="Describe the room, rules, and nearby facilities..."></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Upload Photos (Max 4)</label>
                    <div class="file-upload-wrapper">
                        <input type="file" name="room_images[]" multiple accept="image/*" id="file-input"
                            onchange="updateFileCount()">
                        <div class="file-upload-content">
                            <i class="fa-regular fa-images"></i>
                            <p id="file-text">Click or drag images here</p>
                            <span>JPG, PNG, WEBP (Maximum 4 images)</span>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-submit">Submit Ad for Approval <i class="fa-solid fa-paper-plane"
                        style="margin-left: 5px;"></i></button>
            </form>

        </div>
    </div>

    <script>
        // Simple script to show how many files are selected
        function updateFileCount() {
            const fileInput = document.getElementById('file-input');
            const fileText = document.getElementById('file-text');
            const files = fileInput.files;

            if (files.length > 4) {
                alert('You can only upload up to 4 images. Please re-select.');
                fileInput.value = ''; // Reset
                fileText.innerText = "Click or drag images here";
            } else if (files.length > 0) {
                fileText.innerText = files.length + " image(s) selected";
                fileText.style.color = "var(--green-accent)";
            } else {
                fileText.innerText = "Click or drag images here";
                fileText.style.color = "var(--text-main)";
            }
        }
    </script>

</body>

</html>