<?php
// Include database connection
$host = "localhost";
$user = "root";
$password = "";
$dbname = "cedoc_fiveres";

$conn = new mysqli($host, $user, $password, $dbname);

// Check if the connection is successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
