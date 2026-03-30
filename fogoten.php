<!--?php
require '/home/u946651547/vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

include 'connect.php'; 

function send_confirmation_email($email, $token) {
    $mail = new PHPMailer(true);
    try {
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'house-778@house-778.org';
        $mail->Password = 'pitb4c&sO';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('house-778@house-778.org', 'Reset your house password');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Reset your password';
        $mail->Body    = "Please reset your password by clicking on the following link: 
                          <a href='https://auth.house-778.theorangecow.org/reset-password.php?token=$token'>Reset Password</a>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$error = "";

if (isset($_POST['resetPassword'])) {
    $email = $_POST['email'];

    $checkEmail = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmail);

    if ($result->num_rows > 0) {
        $token = bin2hex(random_bytes(50));

    
        $updateTokenQuery = "UPDATE users SET reset_token='$token' WHERE email='$email'";
        if ($conn->query($updateTokenQuery) === TRUE) {
            
            if (send_confirmation_email($email, $token)) {
                $error = "An email has been sent with instructions to reset your password.";
            } else {
                $error = "There was an error sending the email. Please try again.";
            }
        } else {
            $error = "Failed to update the token in the database.";
        }
    } else {
        $error = "No user found with that email address.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://house-778.theorangecow.org/image.ico" type="image/x-icon">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <canvas class="back" id="canvas"></canvas>
    <div class="container">
        <h2>Forgot Password</h2>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>
            <input type="submit" name="resetPassword" value="Reset Password">
            <p><a href="index.php">What to go back home?</a></p>
            <p><?php echo $error?></p>
        </form>

    </div>
</body>
<script src="https://house-778.theorangecow.org/script.js"></script>
</html-->

<h1>This page is not working at the moment</h1>
