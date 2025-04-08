<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sjcdrrmo";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fetch dropdown options for Vehicle Team and Case Type
$vehicle_teams = ["Alpha", "Bravo", "Charlie"," Delta"];
$case_types = [ "Medical Case", "Medical Standby", "Trauma Case", "Patient Transportation"];

if ($_SERVER["REQUEST_METHOD"] == "POST" && (isset($_POST['submit']) || isset($_POST['update']))) {
    if (!isset($_SESSION['submitted'])) {
        $_SESSION['submitted'] = true;

        $vehicle_team = $_POST['vehicle_team'];
        $case_type = $_POST['case_type'];
        $transport_officer = $_POST['transport_officer'];
        $emergency_responders = $_POST['emergency_responders'];
        $location = $_POST['location'];

        $target_dir = "uploads/";
        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        if (isset($_POST['submit'])) { // Add New Record
            $dispatch_time = $_POST['dispatch_time'];
            $back_to_base_time = $_POST['back_to_base_time'];
            $case_image = $target_dir . basename($_FILES["case_image"]["name"]);
            move_uploaded_file($_FILES["case_image"]["tmp_name"], $case_image);

            $stmt = $conn->prepare("INSERT INTO vehicle_runs (vehicle_team, case_type, transport_officer, emergency_responders, location, dispatch_time, back_to_base_time, case_image) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssssss", $vehicle_team, $case_type, $transport_officer, $emergency_responders, $location, $dispatch_time, $back_to_base_time, $case_image);
            $stmt->execute();
        } elseif (isset($_POST['update'])) { // Update Existing Record
            $id = $_POST['id'];

            // Fetch the existing dispatch and back_to_base time
            $stmt = $conn->prepare("SELECT dispatch_time, back_to_base_time FROM vehicle_runs WHERE id=?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->bind_result($dispatch_time, $back_to_base_time);
            $stmt->fetch();
            $stmt->close();

            if (!empty($_FILES["case_image"]["name"])) {
                $case_image = $target_dir . basename($_FILES["case_image"]["name"]);
                move_uploaded_file($_FILES["case_image"]["tmp_name"], $case_image);
                $stmt = $conn->prepare("UPDATE vehicle_runs SET vehicle_team=?, case_type=?, transport_officer=?, emergency_responders=?, location=?, case_image=? WHERE id=?");
                $stmt->bind_param("ssssssi", $vehicle_team, $case_type, $transport_officer, $emergency_responders, $location, $case_image, $id);
            } else {
                $stmt = $conn->prepare("UPDATE vehicle_runs SET vehicle_team=?, case_type=?, transport_officer=?, emergency_responders=?, location=? WHERE id=?");
                $stmt->bind_param("sssssi", $vehicle_team, $case_type, $transport_officer, $emergency_responders, $location, $id);
            }
            $stmt->execute();
        }
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Handle single delete (GET request)
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $conn->prepare("DELETE FROM vehicle_runs WHERE id=?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error deleting record: " . $conn->error;
    }
}

// Handle multiple deletes (POST request)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_selected'])) {
    if (!empty($_POST['delete_cases'])) {
        $ids = implode(',', array_map('intval', $_POST['delete_cases'])); 
        $stmt = $conn->prepare("DELETE FROM vehicle_runs WHERE id IN ($ids)");
        if ($stmt->execute()) {
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Error deleting records: " . $conn->error;
        }
    }
}
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : '';
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : '';

$sql = "SELECT * FROM vehicle_runs WHERE 1";
if (!empty($start_date) && !empty($end_date)) {
    $sql .= " AND dispatch_time BETWEEN '$start_date 00:00:00' AND '$end_date 23:59:59'";
}
$sql .= " ORDER BY dispatch_time DESC";
$result = $conn->query($sql);



unset($_SESSION['submitted']);
?>