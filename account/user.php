<?php
include "../../base/chech.php";
include "../../base/main.php";
session_start();
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

if ($_SESSION["username"] !== "house-778") {
    header("https://house-778.theorangecow.org");
    exit();
}

$resolt = "";
if (file_exists("/var/www/house-778/.env")) {
    $lines = file("/var/www/house-778/.env", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        putenv($line);
    }
}

$host = "127.0.0.1:3306";
$user = getenv('db_user');
$pass = getenv('db_pass');
$db = "users";

$mysqli = new mysqli($host, $user, $pass, $db);

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

$userId = $_GET['user_id'] ?? null;
$lastLoginTimestamp = null;
$users = [];
$suspendedUsers = [];
$loginLogs = [];

if ($userId) {
    $stmt = $mysqli->prepare("SELECT user_id, username, email, password FROM users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $userResult = $stmt->get_result();
    
    if ($userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
    } else {
        die('User not found.');
    }
    $stmt->close();

    $stmt = $mysqli->prepare("SELECT user_id FROM suspended_users WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $suspendedResult = $stmt->get_result();
    
    if ($suspendedResult->num_rows > 0) {
        $suspendedUsers[] = $userId;
    }

    $stmt->close();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['suspend_user'])) {
            $suspensionReason = $_POST['suspension_reason'] ?? 'No reason provided';
            $stmt = $mysqli->prepare("INSERT INTO suspended_users (user_id, reason, timestamp) VALUES (?, ?, ?)");
            $stmt->bind_param("iss", $userId, $suspensionReason, date('Y-m-d H:i:s'));
            $stmt->execute();
            $stmt->close();
            $resolt = "User with ID '$userId' has been suspended for: $suspensionReason";
        }

        if (isset($_POST['unsuspend_user'])) {
            $stmt = $mysqli->prepare("DELETE FROM suspended_users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();
            $stmt->close();
            $resolt = "User with ID '$userId' has been unsuspended.";
        }

        if (isset($_POST['edit_username'])) {
            $newUsername = $_POST['new_username'] ?? null;
            if ($newUsername) {
                $stmt = $mysqli->prepare("UPDATE users SET username = ? WHERE user_id = ?");
                $stmt->bind_param("si", $newUsername, $userId);
                $stmt->execute();
                $stmt->close();
                $resolt = "Username updated to '$newUsername'.";
            } else {
                $resolt = "New username cannot be empty.";
            }
        }

        if (isset($_POST['edit_password'])) {
            $newPassword = $_POST['new_password'] ?? null;
            if ($newPassword) {
                $stmt = $mysqli->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->bind_param("si", base64_encode($newPassword), $userId);
                $stmt->execute();
                $stmt->close();
                $resolt = "Password updated successfully.";
            } else {
                $resolt = "New password cannot be empty.";
            }
        }

        if (isset($_POST['delete_user'])) {
            $stmt = $mysqli->prepare("DELETE FROM users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $stmt = $mysqli->prepare("DELETE FROM suspended_users WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $stmt = $mysqli->prepare("DELETE FROM login_logs WHERE user_id = ?");
            $stmt->bind_param("i", $userId);
            $stmt->execute();

            $stmt->close();
            $resolt = "User with ID '$userId' has been deleted.";
            exit;
        }
    }
} else {
    die('User ID not found.');
}



$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://house-778.theorangecow.org/base/style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Comfortaa:wght@300..700&display=swap" rel="stylesheet">
    <link rel="icon" href="https://house-778.theorangecow.org/base/icon.ico" type="image/x-icon">
</head>
<body>
    <canvas class="back" id="canvas"></canvas>
    <?php include '../../base/sidebar.php'; ?>
    <div class="con">
        <button class="circle-btn" onclick="openNav()">☰</button>
        <h1>Manage User - <?php echo htmlspecialchars($user['username']); ?></h1>
        <a href="index.php">Home</a>
        <p><?php echo htmlspecialchars($resolt); ?></p>
        
        
        <h2>Edit Username</h2>
        <form method="POST">
            <label for="new_username">New Username:</label>
            <input type="text" name="new_username" id="new_username">
            <br>
            <button type="submit" name="edit_username">Update Username</button>
        </form>
        
        <h2>Edit Password</h2>
        <form method="POST">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password">
            <br>
            <button type="submit" name="edit_password">Update Password</button>
        </form>
        
        <h2>Suspend or Unsuspend User</h2>
        <?php if (!in_array($userId, $suspendedUsers)): ?>
            <form method="POST">
                <label for="suspension_reason">Reason for Suspension:</label>
                <input type="text" name="suspension_reason" id="suspension_reason" required>
                <br>
                <button type="submit" name="suspend_user" onclick="return confirm('Are you sure you want to suspend this user?');">Suspend User</button>
            </form>
        <?php else: ?>
            <form method="POST">
                <button type="submit" name="unsuspend_user" onclick="return confirm('Are you sure you want to unsuspend this user?');">Unsuspend User</button>
            </form>
        <?php endif; ?>
        
        <h2>Delete User Account</h2>
        <form method="POST">
            <button type="submit" name="delete_user" onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.');">Delete User</button>
        </form>
    </div>
</body>
<script src="https://theme.house-778.theorangecow.org/background.js"></script>
<script src="https://house-778.theorangecow.org/base/main.js"></script>
<script src="https://auth.house-778.theorangecow.org/account/track.js"></script>
<script src="https://house-778.theorangecow.org/base/sidebar.js"></script>
</html>
