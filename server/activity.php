<?php
session_start();
header('Content-Type: application/json');

require 'db.php';

$action = $_GET['action'] ?? '';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "Not logged in"]);
    exit;
}

$user_id = $_SESSION['user_id'];

switch ($action) {
    case "update": // user is active (ping or page load)
        $stmt = $pdo->prepare("REPLACE INTO user_activity (user_id, last_activity) VALUES (?, NOW())");
        $stmt->execute([$user_id]);
        echo json_encode(["status" => "updated"]);
        break;

    case "count": // return active users count (last 5 min window)
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM user_activity WHERE last_activity > (NOW() - INTERVAL 5 MINUTE)");
        $stmt->execute();
        echo json_encode(["count" => $stmt->fetchColumn()]);
        break;

    case "logout": // remove user when logging out or tab closed
        $stmt = $pdo->prepare("DELETE FROM user_activity WHERE user_id = ?");
        $stmt->execute([$user_id]);
        echo json_encode(["status" => "removed"]);
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Invalid action"]);
}
