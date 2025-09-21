<?php
    // lecturerReg.php
    require_once 'db.php';
    require_once 'helpers.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        exit('Method not allowed');
    }

    $fname = trim($_POST['fname'] ?? '');
    $lname = trim($_POST['lname'] ?? '');
    $contact = trim($_POST['contactInfo'] ?? '');
    $nic = trim($_POST['nicNumber'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $gender = $_POST['gender_select'] ?? '';
    $username = trim($_POST['username'] ?? '');  // if you include username/password in lecturer form
    $password = $_POST['password'] ?? '';

    if (!$fname || !$lname || !$contact || !$nic || !$gender) {
        exit('Required fields missing.');
    }

    // If your lecturer form doesn't include login creation, you can create users later
    try {
        $pdo->beginTransaction();

        // if username/password provided -> create user account
        if ($username && $password) {
            $pwHash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (:u, :p, 'lecturer')");
            $stmt->execute([':u' => $username, ':p' => $pwHash]);
            $userId = (int)$pdo->lastInsertId();
        } else {
            // create a user row with randomly generated username? Or set to NULL and require admin to create account
            throw new Exception("Lecturer registration requires username and password (recommended).");
        }

        // map gender
        $stmt = $pdo->prepare("SELECT gender_id FROM genders WHERE gender_name = :g");
        $stmt->execute([':g' => $gender]);
        $g = $stmt->fetchColumn();
        if (!$g) throw new Exception("Invalid gender selected.");

        // generate lec code
        $lecCode = generateNextCode($pdo, 'lecturers', 'lec_id', 'LEC', 4);

        // insert lecturer
        $stmt = $pdo->prepare("INSERT INTO lecturers (lec_id, user_id, fname, lname, contact, nic, email, address, gender_id)
            VALUES (:lec, :uid, :fn, :ln, :ct, :nic, :em, :addr, :gid)");
        $stmt->execute([
            ':lec' => $lecCode,
            ':uid' => $userId,
            ':fn' => $fname,
            ':ln' => $lname,
            ':ct' => $contact,
            ':nic' => $nic,
            ':em' => $email,
            ':addr' => $address,
            ':gid' => $g
        ]);

        $pdo->commit();
        header('Location: login.php?registered=1');
        exit;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        exit('Lecturer registration failed: ' . htmlspecialchars($e->getMessage()));
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Lecturer Registration Form</title>
</head>
<body class="dark">
  <div class="form-container">
    <h2>Lecturer Registration Form</h2>
    <form id="lecturerForm">
      <div class="form-group" style="display: flex; gap: 12px;">
        <div style="flex: 1;">
          <label for="fname">First Name</label>
          <input type="text" id="fname" name="fname" placeholder="John" required />
        </div>
        <div style="flex: 1;">
          <label for="lname">Last Name</label>
          <input type="text" id="lname" name="lname" placeholder="Doe" required />
        </div>
      </div>

      <div class="form-group" style="display: flex; gap: 12px;">
        <div style="flex: 1;">
            <label for="contactInfo">Contact Information</label>
            <input type="tel" id="contactInfo" name="contactInfo" placeholder="+94 123-4567" required />
        </div>
        <div style="flex: 1;">
            <label for="nicNumber">NIC Number</label>
            <input type="text" id="nicNumber" name="nicNumber" placeholder="xxxxxxxxxxxx" required />
        </div>
      </div>

      <div class="form-group">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" placeholder="john@example.com" />
      </div>

      <div class="form-group">
        <label for="address">Address</label>
        <textarea id="address" name="address" placeholder="123 Main St, City, Country"></textarea>
      </div>
      <div class="form-group">
        <label for="gender">Gender</label>
        <select id="gender" name="gender_select" required>
          <option value="" disabled selected>Select Gender</option>
          <option value="male">Male</option>
          <option value="female">Female</option>
          <option value="other">Other</option>
        </select>
      </div>
      <button type="submit">Submit</button>
    </form>
  </div>

  <!-- Loading screen -->
  <div class="container" id="loading-screen">
    <div class="spinner1"></div>
    <div class="spinner2"></div>
  </div>

  <!-- Popup screen -->
  <div id="success-popup">
    <h1>Registration Successful!</h1> 
    <p id="username"></p>
    <p id="password"></p>
    <button onclick="window.location.href='student-form.html'">Send Activation Email</button>
  </div>

  <script>
    const formElement = document.getElementById('lecturerForm');
    const container = document.querySelector('.form-container');
    const loadingScreen = document.getElementById('loading-screen');
    const successPopup = document.getElementById('success-popup');
    const username = document.getElementById('username');
    const password = document.getElementById('password');

    formElement.addEventListener('submit', function (e) {
      e.preventDefault();

      const fname = document.getElementById('fname').value;
      const lname = document.getElementById('lname').value;
      const contactInfo = document.getElementById('contactInfo').value;
      const email = document.getElementById('email').value;
      const address = document.getElementById('address').value;
      const nicNumber = document.getElementById('nicNumber').value;
      const userName = `${fname}${lname}@eduflow.com`;

      // Debug log
      console.log({ fname, lname, contactInfo, email, address, nicNumber });

      // Update popup content
      username.textContent = `Username: ${userName}`;
      password.textContent = `Password: NIC Number`;

      // Blur form and show loading
      container.classList.add('blurred');
      loadingScreen.style.display = 'flex';

      setTimeout(() => {
        loadingScreen.style.display = 'none';
        successPopup.style.display = 'flex';
      }, 3000);
    });
  </script>

  <style>   
    * {
    box-sizing: border-box;
    font-family: Arial, sans-serif;
    }

    body {
      background-color: #2e2e3e;
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 100vh;
      margin: 0;
    }

    .form-container {
    background-color: #e6e6ec;
    border: 1px solid #d1d1dc;
    border-radius: 16px;
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
    padding: 32px;
    width: 100%;
    max-width: 800px;
    min-width: 400px;
    }

    h2 {
    font-size: 24px;
    font-weight: 600;
    color: #2e2e3e;
    margin-bottom: 24px;
    text-align: center;
    }

    h4 {
    font-size: 18px;
    color: #3d3d4f;
    margin-top: 32px;
    margin-bottom: 16px;
    }

    label {
    display: block;
    font-size: 14px;
    font-weight: 500;
    color: #333345;
    margin-bottom: 6px;
    }
    
    input,
    textarea,
    select {
    width: 100%;
    padding: 10px 12px;
    font-size: 14px;
    background-color: #f2f2f7;
    color: #1e1e2f;
    border: 1px solid #c2c2d0;
    border-radius: 8px;
    outline: none;
    transition: border-color 0.3s, box-shadow 0.3s;
    }

    input:focus,
    textarea:focus,
    select:focus {
    border-color: #5b73f2;
    box-shadow: 0 0 0 1px rgba(91, 115, 242, 0.3);
    }

    .form-group {
    margin-bottom: 16px;
    }

    button {
    margin-top: 20px;
    width: 100%;
    background-color: #5b73f2;
    color: white;
    border: none;
    padding: 12px;
    font-size: 16px;
    border-radius: 8px;
    cursor: pointer;
    transition: background-color 0.3s;
    }

    button:hover {
    background-color: #465ad8;
    }

    select {
    background-size: 12px;
    }
    #contactInfo, #nicNumber,#fname, #lname {
        width: 90%;
    }
    #gender{
      width: 50%;
    }
    
    /* pop up and loading */
    /* Add at the bottom of your existing <style> */

      .blurred {
      filter: blur(5px);
      transition: filter 0.3s ease;
    }

    #loading-screen,
    #success-popup {      
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.85);
      color: white;
      display: none;
      justify-content: center;
      align-items: center;
      z-index: 1000;
      flex-direction: column;
    }

    /* Spinner animation */
    .container {
    position: relative;
    width: 70px; /* match largest spinner */
    height: 70px;
    }

    .spinner1,
    .spinner2 {
    position: absolute;
    top: 50%;
    left: 50%;
    border-radius: 50%;
    transform: translate(-50%, -50%);
    }

    /* Spinner 1: Half-rounded donut */
    .spinner1 {
    width: 50px;
    height: 50px;
    border-top: 6px solid #408c96;
    border-right: 6px solid #408c96;
    animation: spin 1.2s linear infinite;
    }

    /* Spinner 2: Cut donut */
    .spinner2 {
    width: 70px;
    height: 70px;
    border-top: 6px solid #3498db;
    border-right: 6px solid #3498db;
    animation: spinreverse 1s linear infinite;
    }

    @keyframes spin {
    0% { transform: translate(-50%, -50%) rotate(0deg); }
    100% { transform: translate(-50%, -50%) rotate(360deg); }
    }

    @keyframes spinreverse {
    0% { transform: translate(-50%, -50%) rotate(360deg); }
    100% { transform: translate(-50%, -50%) rotate(0deg); }
    }

    #success-popup {
      animation: fadeIn 0.5s ease-in;
    }

    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.95); }
      to { opacity: 1; transform: scale(1); }
    }

    #success-popup button {
      margin-top: 20px;
      padding: 10px 20px;
      border: none;
      background-color: #3498db;
      color: white;
      font-size: 16px;
      cursor: pointer;
      border-radius: 5px;
      width: 300px;
    }

    #success-popup button:hover {
      background-color: #3d52d2;
    }
 
  </style>
  </style>
</body>
</html>
