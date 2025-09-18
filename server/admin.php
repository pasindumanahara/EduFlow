<?php
    session_start();

    if (!isset($_SESSION['username'])) {
        // user not logged in → redirect back
        header("Location: login.php");
        exit;
    }    
    echo "<script>window.alert('" . strtoupper(addslashes($_SESSION['username'])) . "');</script>";
    include ("../front-end/admin-dashboard.html");

?>

<script>
  // PHP passes session username into JS
  let user = "<?php echo strtoupper(addslashes($_SESSION['username'])); ?>";
  document.getElementById("adminName").innerHTML = user;
</script>