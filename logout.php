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

session_destroy();
header("location: https://house-778.theorangecow.org");
?>
