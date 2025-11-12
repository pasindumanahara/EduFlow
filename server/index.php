<?php
// index.php
session_start();

// If already logged in, redirect by role
if (isset($_SESSION['user_id'], $_SESSION['role'])) {
    $role = $_SESSION['role'];
    if ($role === 'student') {
        header('Location: student_dashboard.php');
        exit;
    } elseif ($role === 'lecturer') {
        header('Location: lecturer_dashboard.php');
        exit;
    } elseif ($role === 'admin') {
        header('Location: admin_dashboard.php');
        exit;
    }
}

// Show login error if any
$loginError = $_SESSION['login_error'] ?? null;
unset($_SESSION['login_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login Page</title>
  <style>
    * { box-sizing: border-box; }
    body {
      margin: 0; padding: 0;
      background: linear-gradient(135deg, #2c3e50, #387881);
      height: 100vh; display: flex; justify-content: center; align-items: center;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; overflow: hidden;
    }
    .login-wrapper { display: flex; flex-direction: column; width: 100%; max-width: 360px;
      background-color: #12121c; border-radius: 12px; overflow: hidden; box-shadow: 0 8px 20px rgba(0,0,0,.5); }
    .logo-section { display:flex; justify-content:center; align-items:center; padding:5px 0; margin-bottom:0; width:100%; }
    #logo { width: 175px; max-width:80%; transition: width .3s ease; }
    .login-container { padding: 0px 24px 30px; text-align:center; animation:fadeIn 1s forwards; opacity:0; transform:translateY(-30px); }
    @keyframes fadeIn { to { opacity:1; transform:translateY(0); } }
    .login-container h2 { font-size:1.8rem; margin-bottom:20px; color:#f0f0f0; margin-top:0; }
    .login-container input[type="text"], .login-container input[type="password"] {
      width:100%; padding:12px 10px; margin:10px 0; background:#2c2c2e; border:1px solid #555; border-radius:6px; color:#eee;
    }
    .login-container input:focus { border-color:#3498db; background-color:#3a3a3c; box-shadow:0 0 5px #3498db; outline:none; }
    .input-wrapper { position:relative; }
    .toggle-password { position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer; font-size:14px; color:#888; }
    .login-container button { width:100%; padding:12px; margin-top:16px; border:none; border-radius:6px; background-color:#3498db; color:white; font-size:16px; cursor:pointer; }
    .login-container button:hover { background-color:#2980b9; }
    #message { margin-top:10px; color:#e74c3c; font-size:14px; height:18px; }
    @media (min-width: 768px) {
      .login-wrapper { flex-direction:row; height:420px; max-width:720px; }
      .logo-section { width:50%; padding:30px; border-right:1px solid rgba(255,255,255,0.05); }
      #logo { width:200px; }
      .login-container { width:50%; padding:40px 30px; margin-top:30px; }
      .login-container h2 { font-size:2rem; }
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

    <!-- display server-side error if any -->
    <div id="message"><?= $loginError ? htmlspecialchars($loginError) : '' ?></div>

    <!-- Direct POST to login.php -->
    <form method="post" action="login.php" autocomplete="off" novalidate>
      <div class="input-wrapper">
        <input type="text" name="username" placeholder="Username" required />
      </div>

      <div class="input-wrapper">
        <input type="password" id="password" name="password" placeholder="Password" required />
        <span class="toggle-password" onclick="togglePassword()">Show</span>
      </div>

      <button type="submit">Login</button>
    </form>
  </div>
</div>

<script>
  // Toggle password visibility
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
</script>

</body>
</html>
