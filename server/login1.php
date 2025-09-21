<?php

    // ../server/login.php
    session_start();
    require_once __DIR__ . '/db.php';

    // Simple rate-limiting per session (adjust as needed)
    if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
    if ($_SESSION['login_attempts'] >= 7) {
        // lock out for a short time (simple)
        http_response_code(429);
        exit('Too many attempts. Try again later.');
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $_SESSION['login_error'] = 'Username and password are required.';
        header('Location: login.php'); // adjust to actual login page path
        exit;
    }

    try {
        $stmt = $pdo->prepare('SELECT user_id, password_hash, role FROM users WHERE username = :u LIMIT 1');
        $stmt->execute([':u' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            // successful login
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int)$user['user_id'];
            $_SESSION['role'] = $user['role'];
            // reset attempts
            $_SESSION['login_attempts'] = 0;

            // role-based redirect (update paths if needed)
            if ($user['role'] === 'student') {
                header('Location: student_dashboard.php');
                exit;
            } elseif ($user['role'] === 'lecturer') {
                header('Location: lecturer_dashboard.php');
                exit;
            } elseif ($user['role'] === 'admin') {
                header('Location: admin.php');
                exit;
            } else {
                // fallback
                header('Location: index.php');
                exit;
            }
        } else {
            $_SESSION['login_attempts']++;
            $_SESSION['login_error'] = 'Invalid username or password.';
            header('Location: login.php'); // adjust path to your login page
            exit;
        }
    } catch (Exception $e) {
        // log error in server logs; don't reveal details to user
        error_log('Login error: ' . $e->getMessage());
        $_SESSION['login_error'] = 'Login failed due to server error.';
        header('Location: login.php');
        exit;
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Page</title>
  <style>
    * {
      box-sizing: border-box;
    }

    body {
      margin: 0;
      padding: 0;
      background: linear-gradient(135deg, #2c3e50, #387881);
      height: 100vh;
      display: flex;
      justify-content: center;
      align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      overflow: hidden;
    }

      .login-wrapper {
      display: flex;
      flex-direction: column;
      width: 100%;
      max-width: 360px; /* change this to 320px */
      background-color: #12121c;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.5);
    }

    .logo-section {
      display: flex;
      justify-content: center;
      align-items: center;
      padding: 5px 0;
      margin-bottom: 0px;
      width: 100%;      
    }


    #logo {
      width: 175px;
      max-width: 80%;
      transition: width 0.3s ease;
    }

    .login-container {
      padding: 0px 24px 30px;
      width: 100%;
      text-align: center;
      animation: fadeIn 1s forwards;
      opacity: 0;
      transform: translateY(-30px);
    }

    @keyframes fadeIn {
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .login-container h2 {
      font-size: 1.8rem;
      margin-bottom: 20px;
      color: #f0f0f0;
      margin-top: 0;
    }

    .login-container input[type="text"],
    .login-container input[type="password"] {
      width: 100%;
      padding: 12px 10px;
      margin: 10px 0;
      background: #2c2c2e;
      border: 1px solid #555;
      border-radius: 6px;
      color: #eee;
    }

    .login-container input:focus {
      border-color: #3498db;
      background-color: #3a3a3c;
      box-shadow: 0 0 5px #3498db;
      outline: none;
    }

    .input-wrapper {
      position: relative;
    }

    .toggle-password {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      cursor: pointer;
      font-size: 14px;
      color: #888;
    }

    .login-container button {
      width: 100%;
      padding: 12px;
      margin-top: 16px;
      border: none;
      border-radius: 6px;
      background-color: #3498db;
      color: white;
      font-size: 16px;
      cursor: pointer;
    }

    .login-container button:hover {
      background-color: #2980b9;
    }

    .login-container .signup-btn {
      background-color: transparent;
      border: 1px solid #3498db;
      color: #3498db;
    }

    .login-container .signup-btn:hover {
      background-color: #3498db;
      color: white;
    }
    #message {
      margin-top: 10px;
      color: #e74c3c;
      font-size: 14px;
      height: 18px;
    }

    /* Desktop adjustments */
    @media (min-width: 768px) {
      .login-wrapper {
        flex-direction: row;
        height: 420px;
        max-width: 720px;
      }

      .logo-section {
        width: 50%;
        padding: 30px;
        border-right: 1px solid rgba(255, 255, 255, 0.05);
      }

      #logo {
        width: 200px;        
      }

      .login-container {
        width: 50%;
        padding: 40px 30px;
        margin-top: 30px;        
      }

      .login-container h2 {
        font-size: 2rem;
      }
    }
  </style>
</head>
<body>

<div class="login-wrapper">
  <div class="logo-section">
    <img src="../resources/logo.png" id="logo" alt="Logo">
  </div>
  <div class="login-container">
    <h2>Login</h2>
    <div class="input-wrapper">
      <input type="text" id="username" placeholder="Username" />
    </div>
    <div class="input-wrapper">
      <input type="password" id="password" placeholder="Password" />
      <span class="toggle-password" onclick="togglePassword()">Show</span>
    </div>
    <button onclick="login()">Login</button>
    <div id="message"></div>
  </div>
</div>

<script>
  function login() {
    const username = document.getElementById('username');
    const password = document.getElementById('password');
    const message = document.getElementById('message');

    if (username.value.trim() === "" || password.value.trim() === "") {
      message.textContent = "Please fill in both fields.";
      if (username.value.trim() === "") username.style.borderColor = "#e74c3c";
      if (password.value.trim() === "") password.style.borderColor = "#e74c3c";
      return;
    }

   // should be changed the dashboard depend on the user
    document.location = 'admin-dashboard.html';
  }

  function togglePassword() {
    const passwordField = document.getElementById('password');
    const toggleBtn = document.querySelector('.toggle-password');

    if (passwordField.type === 'password') {
      passwordField.type = 'text';
      toggleBtn.textContent = 'Hide';
    } else {
      passwordField.type = 'password';
      toggleBtn.textContent = 'Show';
    }
  }

  document.getElementById('username').addEventListener('input', function () {
    this.style.borderColor = '#555';
    document.getElementById('message').textContent = '';
  });

  document.getElementById('password').addEventListener('input', function () {
    this.style.borderColor = '#555';
    document.getElementById('message').textContent = '';
  });
  window.addEventListener('DOMContentLoaded', () => {
  document.getElementById('username').value = '';
  document.getElementById('password').value = '';
  document.getElementById('message').textContent = '';
});

</script>

</body>
</html>
