<?php
    $conn = mysqli_connect("localhost", "root", "1234", "eduflow");
    $password = password_hash("1234",PASSWORD_DEFAULT);
    /*
    $sql = "insert into users (username,password_hash,role) values('test','$password','admin');";
    
    try {
        mysqli_query($conn,$sql);
    } catch (mysqli_sql_exception) {
        echo "error";
    }

    */
    
    echo "$password";
?>