<?php
// count.php
include 'dp.php'; // contains $conn (mysqli connection)

header('Content-Type: application/json');

// Check if table name is provided
if (!isset($_GET['table'])) {
    echo json_encode(['error' => 'No table specified']);
    exit;
}

$table = $_GET['table'];

// Whitelist tables for safety
$allowed_tables = ['users', 'lecturers', 'students']; // add yours here
if (!in_array($table, $allowed_tables)) {
    echo json_encode(['error' => 'Invalid table']);
    exit;
}

$sql = "SELECT COUNT(*) AS count FROM `$table`";
$result = $conn->query($sql);

if ($result) {
    $row = $result->fetch_assoc();
    echo json_encode(['count' => $row['count']]);
} else {
    echo json_encode(['error' => 'Query failed']);
}

$conn->close();
?>
