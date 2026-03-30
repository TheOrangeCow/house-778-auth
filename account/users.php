<?php
include "../../base/chech.php";
include "../../base/main.php";

if ($_SESSION["username"] !== "house-778") {
    header("https://house-778.theorangecow.org");
    exit();
}

if (file_exists("/var/www/house-778/.env")) {
    $lines = file(__DIR__ . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
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

$searchTerm = '';
$users = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['search'])) {
    $searchTerm = trim($_POST['search_term']);
    $stmt = $mysqli->prepare("SELECT user_id, username, password FROM users WHERE username LIKE ?");
    $searchTerm = "%$searchTerm%";
    $stmt->bind_param("s", $searchTerm);
} else {
    $stmt = $mysqli->prepare("SELECT user_id, username, password FROM users");
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $users[] = $row;
}

$stmt->close();
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
            <h1>Welcome, house</h1>
            <h3>User List</h3>
        
            <form method="POST">
                <input type="text" name="search_term" placeholder="Search for a user..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button type="submit" name="search">Search</button>
            </form>
        
            <ul class="users">
                <?php foreach ($users as $user): ?>
                    <?php if ($searchTerm === '' || stripos($user['username'], $searchTerm) !== false): ?>
                        <li>
                            <a href="user.php?user_id=<?php echo htmlspecialchars($user['user_id']); ?>">
                                <?php echo htmlspecialchars($user['username']); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

    </body>
    <script src="https://theme.house-778.theorangecow.org/background.js"></script>
    <script src="https://house-778.theorangecow.org/base/main.js"></script>
    <script src="https://auth.house-778.theorangecow.org/account/track.js"></script>
    <script src="https://house-778.theorangecow.org/base/sidebar.js"></script>
</html>
