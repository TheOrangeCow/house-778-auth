<?php

$host = "127.0.0.1:3306";
$user = getenv('db_user');
$pass = getenv('db_pass');
$db = "user";

$conn=new mysqli($host,$user,$pass,$db);
if($conn->connect_error){
    echo "Failed to connect DB".$conn->connect_error;
}
?>