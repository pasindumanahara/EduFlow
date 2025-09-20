<?php 

    // Database connection
    $pdo = new PDO("mysql:host=localhost;dbname=eduflow", "root", "1234");

    // Get form data
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $contacts = $_POST['contactInfo'];
    $nic = $_POST['nicNumber'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $gender_g_id = ($_POST['gender_select'] == 'male') ? 1 : (($_POST['gender_select'] == 'female') ? 2 : 3);
    $education = $_POST['educationQualifications'];

    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1️⃣ Hash the password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    try {
        $pdo->beginTransaction();

        // 2️⃣ Insert into login
        $stmt = $pdo->prepare("INSERT INTO login (username, password, role) VALUES (?, ?, 'student')");
        $stmt->execute([$username, $hashedPassword]);
        $login_id = $pdo->lastInsertId();

        // 3️⃣ Generate student ID (example: STU + auto_increment padded)
        $stmt = $pdo->query("SELECT AUTO_INCREMENT FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_NAME = 'student' AND TABLE_SCHEMA = DATABASE()");
        $nextId = $stmt->fetchColumn();
        $stu_id = 'STU' . str_pad($nextId, 4, '0', STR_PAD_LEFT);

        // 4️⃣ Insert into student table
        $stmt = $pdo->prepare("INSERT INTO student (stu_id, fname, lname, contacts, email, address, nic, gender_g_id, role, user_id) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'student', ?)");
        $stmt->execute([$stu_id, $fname, $lname, $contacts, $email, $address, $nic, $gender_g_id, $login_id]);

        $pdo->commit();

        echo "Student registered successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        echo "Error: " . $e->getMessage();
    }

?>



<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Student Registration Form</title>
  
</head>
<body class="dark">
    <div class="form-container">
        <h2>Student Registration Form</h2>
        <form method="POST" action="studentReg.php">
        <div class="form-group" style="display: flex; gap: 12px;">
            <div style="flex: 1;">
            <label for="fname">First Name</label>
            <input type="text" id="fname" name="fname" placeholder="John" required/>
            </div>
            <div style="flex: 1;">
            <label for="lname">Last Name</label>
            <input type="text" id="lname" name="lname" placeholder="Doe" required/>
            </div>
        </div>

        <div class="form-group" style="display: flex; gap: 12px;">
            <div style="flex: 1;">
                <label for="contactInfo">Contact Information</label>
                <input type="tel" id="contactInfo" name="contactInfo" placeholder="+94 123-4567" required/>
            </div>
            <div style="flex: 1;">
                <label for="nicNumber">NIC Number</label>
                <input type="text" id="nicNumber" name="nicNumber" placeholder="xxxxxxxxxxxx" required/>
            </div>
        </div>

        <div class="form-group">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="john@example.com" required/>
        </div>

        <div class="form-group">
            <label for="address">Address</label>
            <textarea id="address" name="address" placeholder="123 Main St, City, Country" required></textarea>
        </div>

        <div class="form-group">
            <label for="gender_select">Gender</label>
            <select id="gender" name="gender_select" required>
            <option value="" disabled selected>Select Gender</option>
            <option value="male">Male</option>
            <option value="female">Female</option>
            <option value="other">Other</option>
            </select>
        </div>

        <h4>Login Credentials</h4>
        <div class="form-group">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Username" required/>
        </div>

        <div class="form-group">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Password" required/>
        </div>

        <h4>Education Information</h4>
        <div class="form-group">
            <label for="educationQualifications">Maximum Qualification</label>
            <select id="educationQualifications" name="educationQualifications" required>
            <option value="" disabled selected>Select Qualification</option>
            <optgroup label="Ordinary Level">
                <option value="ol">Ordinary Level Only</option>
            </optgroup>
            <optgroup label="Advanced Level Qualified">
                <option value="al_bio">AL - Bio Scheme</option>
                <option value="al_maths">AL - Maths Scheme</option>
                <option value="al_arts">AL - Arts Scheme</option>
                <option value="al_comm">AL - Commerce Scheme</option>
                <option value="al_tech">AL - Technology Scheme</option>
            </optgroup>
            <optgroup label="University Level">
                <option value="uni_general">University - General Degree</option>
                <option value="uni_msc">University - MSc Degree</option>
                <option value="uni_mphil">University - MPhil Degree</option>
                <option value="uni_phd">University - PhD Degree</option>
            </optgroup>
            </select>
        </div>

        <button type="submit">Submit</button>
        </form>
    </div>

  <!--loading screen-->
  <div class="container" id="loading-screen">
    <div class="spinner1"></div>
    <div class="spinner2"></div>
  </div>
  <!--Popup screen-->
  <div id="success-popup">
    <h1>Registration Successful!</h1> 
    <p id="username"></p>
    <p id="password"></p>
    <button onclick="window.location.href='student-form.html'">Send Activation Email</button>
  </div>

  <script>

      const formElement = document.querySelector('form');
      const container = document.querySelector('.form-container');
      const loadingScreen = document.getElementById('loading-screen');
      const successPopup = document.getElementById('success-popup');
      

      formElement.addEventListener('submit', function (e) {
        e.preventDefault();

        const fname = document.getElementById('fname').value;
        const lname = document.getElementById('lname').value;
        const contactInfo = document.getElementById('contactInfo').value;
        const email = document.getElementById('email').value;
        const address = document.getElementById('address').value;
        const qualification = document.getElementById('educationQualifications').value;
        const userName = `${fname}${lname}@eduflow.com`;

        console.log({ fname, lname, contactInfo, email, address, qualification });

        // Update popup message
        username.textContent = `Username: ${userName}`;
        password.textContent = 'Password: NIC Number';
        // Show loading screen
        container.classList.add('blurred');
        loadingScreen.style.display = 'flex';

        setTimeout(() => {
          loadingScreen.style.display = 'none';
          successPopup.style.display = 'flex';
        }, 3000);
      });
      // need to setup send activation email


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
    #educationQualifications{
        width: 50%;
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
  
</body>
</html>
