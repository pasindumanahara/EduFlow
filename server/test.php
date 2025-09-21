<?php
    $conn = mysqli_connect("localhost", "root", "1234", "eduflow");
    $password = password_hash("1234",PASSWORD_DEFAULT);
    //$sql = "insert into login (username,password) values('admin','$password','admin');";
    /*
    try {
        mysqli_query($conn,$sql);
    } catch (mysqli_sql_exception) {
        echo "error";
    }
    */
    echo "$password";
?>