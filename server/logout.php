<?php
// ../server/logout.php
session_start();

if (isset($_SESSION['user_id'])) {
    try {
        // 🔹 Connect to DB (use your real credentials here)
        $pdo = new PDO("mysql:host=localhost;dbname=yourdbname", "dbuser", "dbpass");
        $stmt = $pdo->prepare("DELETE FROM user_activity WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
    } catch (Exception $e) {
        // Optional: log the error
    }
}

// Clear session variables
$_SESSION = [];

// Clear session cookie if it exists
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_unset();
session_destroy();

// 🚀 Redirect to login (no JSON noise)
header('Location: login.php');
exit;
