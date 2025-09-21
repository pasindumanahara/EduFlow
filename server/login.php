<?php
// login.php
session_start();
require_once __DIR__ . '/db.php'; // Database connection

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    exit('Method not allowed');
}

// Collect form data
$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Simple validation
if ($username === '' || $password === '') {
    $_SESSION['login_error'] = 'Username and password are required.';
    header('Location: index.php'); // redirect back to login page
    exit;
}

try {
    // Query the user by username
    $stmt = $pdo->prepare('SELECT user_id, password_hash, role FROM users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    // Check if user exists and verify password
    if ($user && password_verify($password, $user['password_hash'])) {
        // Login successful
        session_regenerate_id(true); // Security: avoid session fixation
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['role'] = $user['role'];

        // Redirect based on role
        if ($user['role'] === 'student') {
            header('Location: student_dashboard.php');
        } elseif ($user['role'] === 'lecturer') {
            header('Location: lecturer_dashboard.php');
        } elseif ($user['role'] === 'admin') {
            header('Location: admin_dashboard.php');
        } else {
            header('Location: index.php'); // fallback if role unknown
        }
        exit;
    } else {
        // Invalid login
        $_SESSION['login_error'] = 'Invalid username or password.';
        header('Location: index.php');
        exit;
    }
} catch (Exception $e) {
    // Server/DB error
    error_log('Login error: ' . $e->getMessage());
    $_SESSION['login_error'] = 'Server error. Try again later.';
    header('Location: index.php');
    exit;
}
