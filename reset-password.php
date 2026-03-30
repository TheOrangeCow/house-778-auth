<?php
include 'connect.php';
$error = "";
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    $checkTokenQuery = "SELECT * FROM users WHERE reset_token=?";
    $stmt = $conn->prepare($checkTokenQuery);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        if (isset($_POST['submit'])) {
            $newPassword = $_POST['newPassword'];

            $hashedPassword = base64_encode($newPassword);

            $updatePasswordQuery = "UPDATE users SET password=?, reset_token=NULL WHERE reset_token=?";
            $updateStmt = $conn->prepare($updatePasswordQuery);
            $updateStmt->bind_param("ss", $hashedPassword, $token);

            if ($updateStmt->execute()) {
                $error = "Your password has been reset successfully. You can now <a href='index.php'>login</a>.";
            } else {
                $error = "There was an error resetting your password. Please try again.";
            }
        }
    } else {
        $error = "Invalid token.";
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://house-778.theorangecow.org/image.ico" type="image/x-icon">
    <title>Reset Password</title>
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <canvas class="back" id="canvas"></canvas>
    <div class="container">
        <h2>Reset Password</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . '?token=' . $_GET['token']; ?>">
            <label for="newPassword">New Password:</label>
            <input type="password" id="newPassword" name="newPassword" required>
            <input type="submit" name="submit" value="Reset Password">
            <p><?php echo $error?></p>
        </form>
    </div>
</body>
<script src="https://house-778.theorangecow.org/script.js"></script>
</html>
