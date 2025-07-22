<?php

    $conn = mysqli_connect("localhost", "root", "1234","login_test");

    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }

    // Handle form submit
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];

        // Insert into DB
        $sql = "INSERT INTO users(username, password) VALUES ('$username', '$password')";

        if (mysqli_query($conn, $sql)) {
            echo "<script>window.alert('User Registered!');</script>";
            $message = "User registered!";
            header("Location: hello.html"); 
        } else {
            $message = "Error: " . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
?>

?>