<?php
    session_start();

    echo "hello";    
    //echo "<script>window.alert('" . strtoupper(addslashes($_SESSION['username'])) . "');</script>";
    //include ("../front-end/html/admin-dashboard.html");


?>
<form method="POST" action="logout.php">
  <button type="submit">Run PHP Script</button>
</form>


<script>
  // PHP passes session username into JS
  let user = "<?php echo strtoupper(addslashes($_SESSION['username'])); ?>";
  document.getElementById("adminName").innerHTML = user;
</script>