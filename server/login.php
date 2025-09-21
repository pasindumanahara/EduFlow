<?php
// ../server/login.php
session_start();
require_once __DIR__ . '/db.php'; // adjust path if needed

// Basic rate-limiting using session (adjust logic for production)
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if ($_SESSION['login_attempts'] >= 10) {
    // Too many attempts — block
    $resp = ['success' => false, 'message' => 'Too many login attempts. Try again later.'];
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;
    } else {
        $_SESSION['login_error'] = $resp['message'];
        $back = $_SERVER['HTTP_REFERER'] ?? '../public/index.php';
        header('Location: ' . $back);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// basic validation
if ($username === '' || $password === '') {
    $resp = ['success' => false, 'message' => 'Username and password are required.'];
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;
    } else {
        $_SESSION['login_error'] = $resp['message'];
        $back = $_SERVER['HTTP_REFERER'] ?? '../public/index.php';
        header('Location: ' . $back);
        exit;
    }
}

try {
    $stmt = $pdo->prepare('SELECT user_id, password_hash, role FROM users WHERE username = :u LIMIT 1');
    $stmt->execute([':u' => $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        // success
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['user_id'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_attempts'] = 0;

        // compute redirect target by role
        if ($user['role'] === 'student') {
            $redirect = '../student/student_dashboard.php';
        } elseif ($user['role'] === 'lecturer') {
            $redirect = '../lecturer/lecturer_dashboard.php';
        } elseif ($user['role'] === 'admin') {
            $redirect = '../admin/admin_dashboard.php';
        } else {
            $redirect = '../index.php';
        }

        // AJAX response
        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'redirect' => $redirect]);
            exit;
        }

        // Non-AJAX: redirect
        header('Location: ' . $redirect);
        exit;
    } else {
        // failure
        $_SESSION['login_attempts']++;
        $resp = ['success' => false, 'message' => 'Invalid username or password.'];

        if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
            strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            header('Content-Type: application/json');
            echo json_encode($resp);
            exit;
        } else {
            $_SESSION['login_error'] = $resp['message'];
            $back = $_SERVER['HTTP_REFERER'] ?? '../public/index.php';
            header('Location: ' . $back);
            exit;
        }
    }
} catch (Exception $e) {
    error_log('Login error: ' . $e->getMessage());
    $resp = ['success' => false, 'message' => 'Server error. Try again later.'];
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
        header('Content-Type: application/json');
        echo json_encode($resp);
        exit;
    } else {
        $_SESSION['login_error'] = $resp['message'];
        $back = $_SERVER['HTTP_REFERER'] ?? '../public/index.php';
        header('Location: ' . $back);
        exit;
    }
}
