<!--?php 
session_start();



include 'connect.php';
require '/home/u946651547/vendor/autoload.php'; 
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function send_confirmation_email($email, $code) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.hostinger.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'house-778@house-778.org';
        $mail->Password = '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        $mail->setFrom('house-778@house-778.org', 'House 778');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Your Email Confirmation Code';
        $mail->Body    = "Your confirmation code is: <b>$code</b>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

$error = "";

if (isset($_POST['signUp'])) {
    $user_name = $_POST['username'];
    $email     = $_POST['email'];
    $password  = $_POST['password'];

    $user_name = $conn->real_escape_string($user_name);
    $email     = $conn->real_escape_string($email);
    $password  = $conn->real_escape_string($password);

    $code = random_int(100000, 999999);
    $_SESSION['confirmation_code'] = $code;
    $_SESSION['temp_username'] = $user_name;
    $_SESSION['temp_email'] = $email;
    $_SESSION['temp_password'] = base64_encode($password);

    if (send_confirmation_email($email, $code)) {
        $_SESSION['awaiting_confirmation'] = true;
    } else {
        $error = "Failed to send confirmation email.";
    }
}

if (isset($_POST['confirmCode'])) {
    $input_code = $_POST['code'];
    
    if (isset($_SESSION['confirmation_code']) && $_SESSION['confirmation_code'] == $input_code) {
        $user_name = $_SESSION['temp_username'];
        $email     = $_SESSION['temp_email'];
        $password  = $_SESSION['temp_password'];

        $checkEmail = "SELECT * FROM users WHERE email='$email'";
        $result = $conn->query($checkEmail);

        if ($result->num_rows > 0) {
            $error = "Email Address Already Exists!";
        } else {
            $maxIdQuery = "SELECT MAX(CAST(user_id AS UNSIGNED)) AS max_user_id FROM users";
            $result = $conn->query($maxIdQuery);
            $row = $result->fetch_assoc();
            $max_user_id = $row['max_user_id'];

            if ($max_user_id === NULL) {
                $new_user_id = 1;
            } else {
                $new_user_id = $max_user_id + 1;
            }

            $insertQuery = "INSERT INTO users (user_id, username, email, password)
                            VALUES ('$new_user_id', '$user_name', '$email', '$password')";

            if ($conn->query($insertQuery) === TRUE) {
                unset($_SESSION['confirmation_code'], $_SESSION['temp_username'], $_SESSION['temp_email'], $_SESSION['temp_password'], $_SESSION['awaiting_confirmation']);
                header("Location: index.php");
                exit();
            } else {
                $error = "Error: " . $conn->error;
            }
        }
    } else {
        $error = "Invalid confirmation code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<canvas class="back" id="canvas"></canvas>

<div class="container">
    <h2>Sign Up</h2>

    <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!isset($_SESSION['awaiting_confirmation'])): ?>
    <form id="signup-form" method="post" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">

        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>

        <label for="email">Email:</label>
        <input type="email" id="email" name="email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">

        <input type="submit" name="signUp" value="Send Confirmation Code">
    </form>
    <?php else: ?>
    <form id="confirm-form" method="post" action="">
        <label for="code">Enter Confirmation Code:</label>
        <input type="number" id="code" name="code" required>

        <input type="submit" name="confirmCode" value="Confirm and Create Account">
    </form>
    <?php endif; ?>

    <p>By pressing "Sign Up" you agree to our <a href="https://house-778.theorangecow.org/tandc.php">Terms and Conditions</a><br><br>
    <a href="index.php">Already have an account?</a></p>
</div>

<script>
    <?php if ($error): ?>
        setTimeout(function() {
            var errorDiv = document.querySelector('.error');
            if (errorDiv) {
                errorDiv.style.display = 'none';
            }
        }, 3000);
    <?php endif; ?>
</script>

<script src="https://house-778.theorangecow.org/script.js"></script>
</body>
</html-->


<h1> This page isent owrking at the moment </h1>
