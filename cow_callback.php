<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '.theorangecow.org', 
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Lax'
]);
session_start();
include 'connect.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);


$COW_ACCOUNTS      = "https://theorangecow.org";
$COW_CLIENT_ID     = "house-778";
$COW_CLIENT_SECRET = "dev-secret-house778"; //getenv('COW_CLIENT_SECRET');

$token = $_GET['token'] ?? null;

if (!$token) {
    header("Location: https://house-778.theorangecow.org/index.php?error=" . urlencode("Cow sign-in didn't send back a token."));
    exit;
}

$ch = curl_init($COW_ACCOUNTS . "/sso/verify");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    "client_id"     => $COW_CLIENT_ID,
    "client_secret" => $COW_CLIENT_SECRET,
    "token"         => $token,
]));
curl_setopt($ch, CURLOPT_TIMEOUT, 5);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$result = json_decode($response, true);

if ($httpCode !== 200 || !$result || !($result['ok'] ?? false)) {
    header("Location: https://house-778.theorangecow.org/index.php?error=" . urlencode("Cow sign-in could not be verified."));
    exit;
}

$cow_username = $result['username'] . "_cow";

$escaped = $conn->real_escape_string($cow_username);
$check = $conn->query("SELECT * FROM users WHERE username='$escaped'");

if ($check && $check->num_rows > 0) {
    $row = $check->fetch_assoc();
    $_SESSION['username'] = $row['username'];
    $_SESSION['user_id']  = $row['user_id'];
} else {
    $maxIdQuery = "SELECT MAX(CAST(user_id AS UNSIGNED)) AS max_user_id FROM users";
    $maxResult  = $conn->query($maxIdQuery);
    $maxRow     = $maxResult->fetch_assoc();
    $new_user_id = ($maxRow['max_user_id'] === NULL) ? 1 : $maxRow['max_user_id'] + 1;

    $password_encoded = base64_encode($password);
    $sql = "SELECT * FROM users WHERE username='$username' AND password='$password_encoded'";
    
    $conn->query($insertQuery);

    $_SESSION['username'] = $cow_username;
    $_SESSION['user_id']  = $new_user_id;
}

header("Location: https://house-778.theorangecow.org/home.php");
exit;