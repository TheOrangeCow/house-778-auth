<?php
include "../../base/chech.php"; 
include "../../base/main.php";
include "alert.php";
session_start();
include '../connect.php'; 

$currentUsername = $_SESSION['username'];

function generateVerificationCode($length = 6) {
    return strtoupper(substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789'), 0, $length));
}

function sendVerificationEmail($toEmail, $code) {
    $subject = "Email Verification Code";
    $message = "Your verification code is: $code\n\nPlease enter this code to confirm your email change.";
    $headers = "From: coworange9@gmail.com\r\n";
    return mail($toEmail, $subject, $message, $headers);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_email'])) {
    $newEmail = $_POST['new_email'] ?? null;
    if ($newEmail) {
        $checkEmailQuery = "SELECT * FROM users WHERE email = '$newEmail'";
        $result = $conn->query($checkEmailQuery);
        if ($result->num_rows > 0) {
            echo "Email already exists.";
            exit;
        }

        $verificationCode = generateVerificationCode();
        
        if (sendVerificationEmail($newEmail, $verificationCode)) {
            $_SESSION['pending_email'] = $newEmail;
            $_SESSION['email_verification_code'] = $verificationCode;
            echo "<h2>A verification code has been sent to your email. Please enter the code below:</h2>";
            echo '
            <form method="POST">
                <label for="verification_code">Verification Code:</label>
                <input type="text" name="verification_code" required>
                <br>
                <button type="submit" name="verify_email_code">Verify Email</button>
            </form>';
        } else {
            echo "Failed to send verification email.";
        }
        exit;
    }
    echo "Invalid email.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_email_code'])) {
    $userCode = $_POST['verification_code'] ?? null;
    $storedCode = $_SESSION['email_verification_code'] ?? null;
    
    if ($userCode && $storedCode) {
        if ($userCode === $storedCode) {
            $newEmail = $_SESSION['pending_email'];
            $updateEmailQuery = "UPDATE users SET email = '$newEmail' WHERE username = '$currentUsername'";
            if ($conn->query($updateEmailQuery) === TRUE) {
                unset($_SESSION['pending_email']);
                unset($_SESSION['email_verification_code']);
                echo "Email updated successfully.";
            } else {
                echo "Error updating email: " . $conn->error;
            }
        } else {
            echo "Incorrect verification code. Please try again.";
        }
    } else {
        echo "Verification code is required.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_username'])) {
        $newUsername = $_POST['new_username'] ?? null;
        if ($newUsername) {
            $checkUsernameQuery = "SELECT * FROM users WHERE username = '$newUsername'";
            $result = $conn->query($checkUsernameQuery);
            if ($result->num_rows > 0) {
                echo "Username already exists.";
                exit;
            }
            $updateUsernameQuery = "UPDATE users SET username = '$newUsername' WHERE username = '$currentUsername'";
            if ($conn->query($updateUsernameQuery) === TRUE) {
                $_SESSION['username'] = $newUsername;
                echo "Username updated successfully.";
                exit;
            } else {
                echo "Error updating username: " . $conn->error;
                exit;
            }
        }
        echo "Invalid username.";
        exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {
    $newPassword = $_POST['new_password'] ?? null;
    if ($newPassword) {
        $hashedPassword = base64_encode($newPassword);
        $updatePasswordQuery = "UPDATE users SET password = '$hashedPassword' WHERE username = '$currentUsername'";
        if ($conn->query($updatePasswordQuery) === TRUE) {
            echo "Password updated successfully.";
            exit;
        } else {
            echo "Error updating password: " . $conn->error;
            exit;
        }
    }
    echo "Invalid password.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_account'])) {
    $deleteAccountQuery = "DELETE FROM users WHERE username = '$currentUsername'";
    if ($conn->query($deleteAccountQuery) === TRUE) {
        session_destroy();
        echo "Account deleted successfully.";
        exit;
    }
    echo "Error deleting account: " . $conn->error;
    exit;
}
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
            <h1>Welcome, <?php echo $_SESSION["username"]; ?> to your account</h1>

            <h1>Update Your Username</h1>
            <form method="POST">
                <label for="new_username">New Username:</label>
                <input type="text" name="new_username" id="new_username" required>
                <br>
                <button type="submit" name="update_username">Update Username</button>
            </form>

            <h1>Update Your Password</h1>
            <form method="POST">
                <label for="new_password">New Password:</label>
                <input type="password" name="new_password" id="new_password" required>
                <br>
                <button type="submit" name="update_password">Update Password</button>
            </form>

            <h1>Update Your Email</h1>
            <form method="POST">
                <label for="new_email">New Email:</label>
                <input type="email" name="new_email" id="new_email" required>
                <br>
                <button type="submit" name="update_email">Update Email</button>
            </form>

            <h1>Delete Your Account</h1>
            <form method="POST">
                <button type="submit" name="delete_account" onclick="return confirm('Are you sure you want to delete your account? This action cannot be undone.');">Delete Account</button>
            </form>
            
            <?php 
            if ($_SESSION["username"] == "house-778") {
                echo "<br><a href='https://auth.house-778.theorangecow.org/account/users.php'>Manage other users</a>";
            }
            ?>
        </div>
        
    </body>
    <script src="https://theme.house-778.theorangecow.org/background.js"></script>
    <script src="https://house-778.theorangecow.org/base/main.js"></script>
    <script src="https://auth.house-778.theorangecow.org/account/track.js"></script>
    <script src="https://house-778.theorangecow.org/base/sidebar.js"></script>
</html>
