<?php
$servername = "localhost";
$username = "pingui_sql";
$password = "/zPXQ8RR.zJ]u4Dm";

try {
  $conn = new PDO("mysql:host=$servername;dbname=pingui", $username, $password);
  // set the PDO error mode to exception
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  echo "Connected successfully";
} catch(PDOException $e) {
  echo "Connection failed: " . $e->getMessage();
}
?>