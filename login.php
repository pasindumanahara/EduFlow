<?php
    session_start();
    // Connect to DB
    $conn = mysqli_connect("localhost", "root", "1234", "login_test");

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = filter_input(INPUT_POST, "username", FILTER_SANITIZE_EMAIL);
        $password = filter_input(INPUT_POST, "password", FILTER_SANITIZE_SPECIAL_CHARS);

        if (empty($username)) {
            echo "<script>window.alert('Please enter the Username');</script>";
        } elseif (empty($password)) {
            echo "<script>window.alert('Please enter the Password');</script>";
        } else {
            // Use prepared statement
            $stmt = mysqli_prepare($conn, "SELECT password FROM users WHERE username = ?");
            mysqli_stmt_bind_param($stmt, "s", $username);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_store_result($stmt);
            if (mysqli_stmt_num_rows($stmt) == 1) {
                mysqli_stmt_bind_result($stmt, $hashed_password);
                mysqli_stmt_fetch($stmt);

                if (password_verify($password, $hashed_password)) {    
                    $_SESSION['username'] = $username; 
                    $firstLetter = strtoupper($username[0]);
                    if ($firstLetter === 'S') {
                      header("Location: student.php");                    
                      exit(); 
                    } elseif ($firstLetter === 'L') {
                      header("Location: lecturer.php");                    
                      exit();
                    } elseif ($firstLetter === 'A') {
                      header("Location: admin.php");                    
                      exit();
                    } else {
                      // Optional: handle other cases
                      echo "<script>alert('Username does not match student or lecturer pattern');</script>";
                    }
                } else {
                    echo "<script>window.alert('Invalid Password');</script>";
                }
            } else {
                echo "<script>window.alert('Username not found');</script>";
            }

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($conn);
?>