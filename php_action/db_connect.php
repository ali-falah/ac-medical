<?php
require_once 'security_check.php';

$localhost = "localhost";
$username = "root";
$password = "";
$dbname = "medical";
// $ip_address = $_SERVER['SERVER_ADDR'];
$url = (isset($_SERVER['HTTP_HOST'])) ? "http://" . $_SERVER['HTTP_HOST'] . "/ac-medical/" : "";
// db connection


$connect = new mysqli($localhost, $username, $password, $dbname);
mysqli_set_charset($connect, "utf8");
// mysqli_set_charset($connect, "utf8mb4");
// check connection
if ($connect->connect_error) {
  die("Connection Failed : " . $connect->connect_error);
}
else {
// echo "Successfully connected";
}

?>