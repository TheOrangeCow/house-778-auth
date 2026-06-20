<?php
$COW_ACCOUNTS    = "https://theorangecow.org";
$COW_CLIENT_ID   = "house-778";
$COW_REDIRECT_URI = "https://auth.house-778.theorangecow.org/cow_callback.php";

header("Location: " . $COW_ACCOUNTS . "/sso/authorize?client_id=" . urlencode($COW_CLIENT_ID) . "&redirect_uri=" . urlencode($COW_REDIRECT_URI));
exit;