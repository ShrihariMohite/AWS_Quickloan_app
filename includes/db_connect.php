<?php
$servername = "quickloan.cnc4qmwionnz.ap-northeast-1.rds.amazonaws.com";
$username = "admin";
$password = "vaivii1234";
$dbname = "quickloan_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>