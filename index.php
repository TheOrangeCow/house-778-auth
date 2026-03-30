<?php
$cookieParams = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => $cookieParams["lifetime"],
    'path' => $cookieParams["path"],
    'domain' => '.theorangecow.org',
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);

session_start();



include 'connect.php';


function generateUsername() {
    $randomNumber = rand(1000, 9999);
    return "Guest" . $randomNumber;
}

$error = "";

if (isset($_POST['signIn'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' && $password === '') {
        $username = generateUsername();
        $_SESSION["username"] = $username;
        $_SESSION['user_id'] = 1;
        header("Location: https://house-778.theorangecow.org/home.php");
        exit;
    }

    $password_encoded = base64_encode($password);

    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password_encoded'";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $_SESSION['username'] = $row['username'];
        $_SESSION['user_id'] = $row['user_id'];
        header("Location: https://house-778.theorangecow.org/home.php");
        exit();
    } else {
        $error = "Not Found, Incorrect Username or Password";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="https://house-778.theorangecow.org/image.ico" type="image/x-icon">
    <title>Sign In</title>
    <link rel="stylesheet" href="style.css"/>
</head>
<body>
    <canvas class="back" id="canvas"></canvas>
    <div class="container">
        <h2>Sign In</h2>
        <?php if ($error): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="loginForm">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" autocomplete="off">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" autocomplete="off">
            <input type="submit" name="signIn" id="submitBtn" value="Sign in as Guest"><br><br>
            <a href="signup.php">Don't have an account yet?</a><br>
            <a href="fogoten.php">Forgotten your password?</a>
        </form>
    </div>
    <div id="popup2" class="popup">
        <div class="popup-content">
            <span class="popup-close" id="closePopupBtn">&times;</span>
            <h2>Important Update</h2>
            <p>We recently moved our databases to a new system. As part of this update, all user data including login information, themes, and favorites has been reset.
You'll need to create a new account and set up your preferences again.
We’re sorry for the inconvenience and appreciate your understanding as we work to improve your experience!

</p>
        </div>

    </div>

    <script src="https://house-778.theorangecow.org/script.js"></script>
    <script src="popup.js"></script>
    <script>
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const submitBtn = document.getElementById('submitBtn');

    function updateButtonText() {
        if (usernameInput.value.trim() !== '' || passwordInput.value.trim() !== '') {
            submitBtn.value = 'Login';
        } else {
            submitBtn.value = 'Sign in as Guest';
        }
    }

    usernameInput.addEventListener('input', updateButtonText);
    passwordInput.addEventListener('input', updateButtonText);

    </script>
</body>
</html>
