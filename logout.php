<?php
session_set_cookie_params(0, '/', 'house-778.org');
session_start();
define('BASE_PATH', __DIR__ . '/../');

session_destroy();
header("location: https://house-778.theorangecow.org");
?>