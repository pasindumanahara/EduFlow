<?php
    require_once "db.php";

    // send the data to the server
    $message = $_POST['message'];
    // fix the role for admin to only be able to send
    $role = "admin";
    $sql = "insert into table notices(notices,role) values($message,$role)";
    
?>