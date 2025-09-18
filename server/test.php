<?php
    $conn = mysqli_connect("localhost", "root", "1234", "login_test");
    $password = password_hash("1234",PASSWORD_DEFAULT);
    $sql = "insert into users (username,password) values('manahara','$password');";
    try {
        mysqli_query($conn,$sql);
    } catch (mysqli_sql_exception) {
        echo "error";
    }
    
?>