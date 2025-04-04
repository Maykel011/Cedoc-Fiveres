<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sjcdrrmo";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$today = date("Y-m-d");
$sql_today = "SELECT COUNT(*) AS count FROM vehicle_runs WHERE DATE(dispatch_time) = '$today'";
$result_today = $conn->query($sql_today);
$row_today = $result_today->fetch_assoc();
$today_count = $row_today['count'];

// Fetch this week's vehicle runs
$week_start = date("Y-m-d", strtotime("last sunday"));
$week_end = date("Y-m-d", strtotime("next saturday"));
$sql_week = "SELECT COUNT(*) AS count FROM vehicle_runs WHERE DATE(dispatch_time) BETWEEN '$week_start' AND '$week_end'";
$result_week = $conn->query($sql_week);
$row_week = $result_week->fetch_assoc();
$week_count = $row_week['count'];

// Fetch this month's vehicle runs
$month = date("Y-m");
$sql_month = "SELECT COUNT(*) AS count FROM vehicle_runs WHERE DATE_FORMAT(dispatch_time, '%Y-%m') = '$month'";
$result_month = $conn->query($sql_month);
$row_month = $result_month->fetch_assoc();
$month_count = $row_month['count'];

$conn->close();
?>