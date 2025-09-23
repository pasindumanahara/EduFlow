<?php
    session_start();    
    require 'db.php';
    if (!isset($_SESSION['user_id'])) {
      header("Location: login.php");
      exit;
    }
    $username = $_SESSION['username'];
    // student count
    $stmt1 = $pdo->prepare("SELECT COUNT(*) FROM students");
    $stmt1->execute();
    $studentsCount = $stmt1->fetchColumn();
    // lecturers count
    $stmt2 = $pdo->prepare("SELECT COUNT(*) FROM lecturers");
    $stmt2->execute();
    $lecturersCount = $stmt2->fetchColumn();  
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard - Student Management</title>  
</head>
<body class="dark">

  <button class="mobile-toggle" onclick="toggleSidebar()">☰</button>

  <div class="sidebar" id="sidebar">
    <div class="logo">
      <img src="../resources/logo.png">
    </div>

    <div class="menu-section">
      <div class="menu-title" onclick="toggleMenu(this)">Student Management</div>
      <div class="menu-content">
        <a href="student-form.html" target="_blank">Student Registration Form</a>
        <a href="data-table-students.html">Student List</a>
        <a href="#">Test Link 3</a>
      </div>
    </div>

    <div class="menu-section">
      <div class="menu-title" onclick="toggleMenu(this)">Lecturer Management</div>
      <div class="menu-content">
        <a href="lecturerForm.html" target = "_blank">Lecturer Registration Form</a>
        <a href="#">Test Link B</a>
      </div>
    </div>

    <div class="static-links">
      <a href="">Static Link 1</a>
      <a href="#">Static Link 2</a>
      <a href="#">Static Link 3</a>
    </div>

    <div class="sidebar-footer">
      <a href="logout.php">Logout</a> 
      <br>
    </div>
  </div>

  <div class="main-content">
    
    <div class="top-bar">
      <h1 id="test">Dashboard</h1>
      <div class="admin" id = 'username'></div>
    </div>
  
    <div class="cards">
      <div class="card">
        <div class="innerCard">
          <img src="../resources/logo.png" alt="">
          <div>
            <h3>Students</h3>
            <p id="students-count"></p>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="innerCard">
          <img src="../resources/logo.png" alt="">
          <div>
            <h3>Lecturers</h3>
            <p id="lecturers-count"></p>
          </div>
        </div>
      </div>
      <div class="card">
        <div class="innerCard">
          <img src="../resources/logo.png" alt="">
          <div>
            <h3>Active Users</h3>
            <p style="display: inline; color: rgb(0, 168, 0);">●</p>
            <p id="activeUsers" style="display: inline;">ERROR</p>
          </div>
        </div>
      </div>      
    </div> 
    <!--chart-->
    
      <div class="chart-container" style="transition: 0.2s;">
        <div class="charts">      
          <canvas id="donutChart" width="300" height="300"></canvas>
          <div class="legend">
            <div class="legend-item" >
              <div class="legend-color" id="legend-color-boy" ></div>
              <div class="legend-label"><h3>Boys</h3></div>
            </div>
            <div class="legend-item">
              <div class="legend-color" id="legend-color-girl"></div>
              <div class="legend-label"><h3>Girls</h3></div>
            </div>
          </div>
        </div>  
      </div> 
    </div>     
  

  <script>
    // loading the username to the admin-dasboard
    const username = "<?php echo addslashes($username); ?>";
    document.getElementById("username").innerHTML = `Logged in as <h3 style = " display : inline;">${username}</h3>`;

    // loading student and lecturers count to the page
    const students = "<?php echo addslashes($studentsCount); ?>";
    document.getElementById("students-count").innerHTML = `${students}`;
    const lecturers = "<?php echo addslashes($lecturersCount); ?>";
    document.getElementById("lecturers-count").innerHTML = `${lecturers}`;

  
    function toggleMenu(element) {
      const content = element.nextElementSibling;
      content.style.display = content.style.display === "block" ? "none" : "block";
    }

    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      const toggleButton = document.querySelector('.mobile-toggle');
      const content = document.querySelector('.main-content');

      sidebar.classList.toggle('show');

      const isSmallScreen = window.innerWidth <= 768;
      
      if (isSmallScreen) {
        const sidebarOpen = sidebar.classList.contains('show');

        if (sidebarOpen) {
          toggleButton.style.left = '260px';
          content.style.opacity = '0.3';
          toggleButton.textContent = '×';
        } else {
          toggleButton.style.left = '15px';
          content.style.opacity = '1';
          toggleButton.textContent = '☰';
          handleResize(); // CALLING
        }
      } 
    // ERROR OCCURED IN REZISING ADDED THESE LISTNER TO FIX WINDOW RESIZING ERRORS
    function handleResize() {
    if (window.innerWidth >= 1024) {        
        content.style.opacity = '1';         // Reset content opacity
        }
    }
    handleResize();
    window.addEventListener('resize', handleResize);      
    }
    

      // draw the donut chart
    const canvas = document.getElementById("donutChart");
    const ctx = canvas.getContext("2d");

    // Replace these with dynamic values later if needed
    const boysCount = 65;
    const girlsCount = 35;

    function getColors(isDark) {
      // Pick one theme only since toggle is gone
      return isDark
        ? ['#2176aa', '#85c3ee']   // Dark mode colors
        : ['#2f2219', '#9b6e4d'];  // Light mode colors
    }

    function drawDonutChart(ctx, data, colors, x, y, radius, cutout) {
      ctx.clearRect(0, 0, canvas.width, canvas.height);
      const total = data.reduce((sum, value) => sum + value, 0);
      let startAngle = -0.5 * Math.PI;

      data.forEach((value, index) => {
        const sliceAngle = (value / total) * 2 * Math.PI;
        ctx.beginPath();
        ctx.arc(x, y, radius, startAngle, startAngle + sliceAngle);
        ctx.arc(x, y, radius - cutout, startAngle + sliceAngle, startAngle, true);
        ctx.closePath();
        ctx.fillStyle = colors[index];
        ctx.fill();
        startAngle += sliceAngle;
      });
    }

    // legend colors
    function updateLegendColors(isDark) {
      const [boyColor, girlColor] = getColors(isDark);
      const boyLegend = document.getElementById("legend-color-boy");
      const girlLegend = document.getElementById("legend-color-girl");
      if (boyLegend) boyLegend.style.backgroundColor = boyColor;
      if (girlLegend) girlLegend.style.backgroundColor = girlColor;
    }

    // Force one theme (dark or light)
    const savedTheme = localStorage.getItem("theme") || "dark";
    const isDark = savedTheme === "dark";

    // Apply theme class
    document.body.classList.add(savedTheme);

    // Draw chart
    const colors = getColors(isDark);
    const data = [boysCount, girlsCount];
    updateLegendColors(isDark);
    drawDonutChart(ctx, data, colors, 150, 150, 100, 40);

      /* -- Fetch must send cookies so PHP can read the session -- */
      const ACTIVITY_URL = 'activity.php'; 

      // load current count
      async function loadActiveUsers(){
        try {
          const res = await fetch(`${ACTIVITY_URL}?action=count`, { credentials: 'same-origin' });
          const data = await res.json();
          document.getElementById('activeUsers').textContent = data.count ?? 0;
        } catch (err) {
          console.error('loadActiveUsers error', err);
        }
      }

      // heartbeat: tell server we're still active
      function sendHeartbeat(){
        fetch(`${ACTIVITY_URL}?action=update`, {
          method: 'POST',
          credentials: 'same-origin'
        }).catch(()=>{/* ignore network errors */});
      }

      // logout / tab close handler
      function sendLogoutBeacon(){
        // sendBeacon uses POST and is good for unload
        const url = `${ACTIVITY_URL}?action=logout`;
        if (navigator.sendBeacon) {
          navigator.sendBeacon(url);
        } else {
          // fallback: synchronous XHR (not ideal but works)
          try {
            var xhr = new XMLHttpRequest();
            xhr.open('POST', url, false); // false => synchronous
            xhr.send(null);
          } catch(e){}
        }
      }
      /* start things */
      loadActiveUsers();
      setInterval(loadActiveUsers, 10000); // update visible count every 10s
      sendHeartbeat();
      setInterval(sendHeartbeat, 60000); // ping every 60s

      window.addEventListener('beforeunload', sendLogoutBeacon);
      
  </script>

  <style>     
        
       
        body {
          margin: 0;
          font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
          background-color: #1e1e2f;
          color: #eee;
          display: flex;
          min-height: 100vh;
        }

        .sidebar {
          width: 250px;
          background-color: #12121c;
          transition: transform 0.3s ease;
          display: flex;
          flex-direction: column;
          position: fixed;
          height: 100%;
          overflow-y: auto;
          z-index: 1000;     
          border-right: 1px solid black; 
        }
        .sidebar h2 {
          color: #fff;
          text-align: center;
          margin-bottom: 30px;
        }

        .sidebar a {
          display: block;
          padding: 12px 20px;
          color: #aaa;
          text-decoration: none;
          border-left: 4px solid transparent;
          transition: 0.3s;
        }

        .sidebar a:hover,
        .sidebar a.active {
          background-color: #282840;
          border-left: 4px solid #3498db;
          color: #fff;
        }

        .sidebar.collapsed {
          transform: translateX(-100%);
        }

        .logo {  
          text-align: center;
          border-bottom: 1px solid #333;
          background-color:  #12121c;
        }

        .logo img{
          width: 80%;      
        }

        .menu-section {
          border-bottom: 1px solid #333;
        }

        .menu-title {
          padding: 12px 20px;
          cursor: pointer;
          font-weight: bold;
          background-color: hsl(240, 22%, 12%);
          transition: background-color 0.2s;
        }

        .menu-title:hover,
        .menu-title a.active {
          background-color: #282840;
          border-left: 4px solid #3498db;
          color: #fff;
        }

        .menu-content {
          display: none;
          background-color: #1f1f30;
          
        }

        .menu-content a {
          display: block;
          padding: 10px 30px;
          text-decoration: none;
          color: #bbb;
          font-size: 14px;
          transition: background-color 0.s;
        }

        .menu-content a:hover {
          background-color: #33334d;
        }

        .static-links a {
          display: block;
          padding: 12px 20px;
          color: #ccc;
          text-decoration: none;
          border-bottom: 1px solid #333;
        }

        .static-links a:hover {
          background-color: #33334d;
        }

        .sidebar-footer {
          margin-top: auto;
          border-top: 1px solid #333;      
          text-align: center;
        }

        .sidebar-footer a {
          display: block;
          padding: 12px 20px;
          text-decoration: none;
          color: #aaa;          
        }

        .sidebar-footer a:hover {
          background-color: #2a2a3a;
        }

        .mobile-toggle {
          display: none;
          position: fixed;
          top: 15px;
          left: 15px;
          background: #12121c;
          font-weight: bold;
          font-size: 2rem;
          color: rgba(255, 255, 255, 0.938);
          border: 1px solid rgba(255, 255, 255, 0.432);
          padding: 4px 8px;
          font-size: 20px;
          border-radius: 5px;
          z-index: 1100;
          cursor: pointer;
          overflow: hidden;
          transition: left 0.3s ease;
        }

        .content {
          transition: opacity 0.3s ease;
        }

        @media (max-width: 768px) {
          .sidebar {
            transform: translateX(-100%);
          }

          .sidebar.show {
            transform: translateX(0);
          }

          .mobile-toggle {
            display: block;
          }
        }

        .main-content {
          color: rgb(160, 160, 160);
          margin-left: 250px;
          margin-top: 27px;
          padding: 20px;
          flex: 1;
        }

        @media (max-width: 768px) {
          .main-content {
            margin-left: 0;
            padding: 10px;
          }
        }

        .top-bar {
          display: flex;
          justify-content: space-between;
          align-items: center;
          margin-bottom: 30px;
        }

        .top-bar h1 {
          font-size: 24px;
          color: #f0f0f0;
        }
        .top-bar .user {
            overflow: hidden;
        }
        .top-bar .admin {
          float: left;
          font-size: 14px;
          color: #aaa;
          margin-right: 10px;
        }

        .cards {
          display: flex;
          gap: 20px;
          flex-wrap: wrap;
          margin-bottom: 30px;          
          justify-content: center;            
        }

        .card {
          background-color: #2c2c3e;
          border-radius: 8px;
          padding: 20px;
          flex: 1;
          float: left;
          max-width: 400px;
          min-width: 150px;
          box-shadow: 0 4px 8px rgba(0,0,0,0.3);
          
          display: flex;
          justify-content: center;
        }
        .cards img{
          width: 30%;
          display: inline;
          
        }
        .innerCard {
          display: flex;
          align-items: center;
          gap: 15px;
        }

        .innerCard img {
          width: 100px;
          height: 100px;
          object-fit: contain;
        }

        .card h3 {
          margin: 0 0 10px;
          font-size: 18px;
          color: #ccc;
        }

        .card p {
          font-size: 24px;
          font-weight: bold;
          color: #3498db;
        }

        .card h3 {
          margin: 0 0 10px;
          font-size: 18px;
          color: #ccc;
        }

        .card p {
          font-size: 24px;
          font-weight: bold;
          color: #3498db;
        }

        table {
          width: 100%;
          border-collapse: collapse;
          background-color: #2a2a3a;
          border-radius: 8px;
          overflow: hidden;
        }

        th, td {
          padding: 12px;
          text-align: left;
          color: #ccc;
        }

        th {
          background-color: #1c1c2b;
          font-weight: bold;
        }

        tr:nth-child(even) {
          background-color: #2f2f44;
        }

        tr:hover {
          background-color: #3b3b55;
        }


        /* dark light switch */
        /* Light Theme: Brown + Dimmed White Palette */

        /* Light Theme: Soft White + Light Blue Palette */
        body.light {
          background-color: #f5f8fc; /* very light bluish white */
          color: #1e2a3a; /* dark slate text */
        }

        body.light .sidebar {
          background-color: #e6ecf5; /* pale bluish gray */
          border-right: 1px solid #cbd5e0;
        }

        body.light .top-bar h1 {
          color: #1e2a3a;
        }
        body.light .top-bar {
          color: #1e2a3a;
        }
        body.light .top-bar .admin {
          color: #2f3d4d;
        }

        body.light .sidebar h2 {
          color: #2f3d4d;
        }

        body.light .sidebar a {
          color: #3d4f65;
        }

        body.light .sidebar a:hover,
        body.light .sidebar a.active {
          background-color: #d4e2f5; /* soft blue hover */  
          border-left: 4px solid #3498db;
          color: #0f172a;
        }

        /* Logo background - subtle bluish tint */
        body.light .logo {
          background-color: #3a3a5aff; /* soft light blue */
          border-bottom: 1px solid #b7c9e4;
        }

        /* Card background - light bluish panel */
        body.light .card {
          background-color: #3a3a5aff ; /* pale blue card */
          color: #1e2a3a;
          border-radius: 12px;
          box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
        }

        body.light .card h3 {
          color: #e6ecf5 ;
        }

        body.light .card p {
          color: #3498db; /* keep accent */
        }


        body.light .menu-title {
          background-color: #dbe4f1;
          color: #1e2a3a;
        }

        body.light .menu-title:hover,
        body.light .menu-title a.active {
          background-color: #c8daef;
          border-left: 4px solid #3498db;
          color: #0f172a;
        }

        body.light .menu-content {
          background-color: #edf2fa;
        }

        body.light .menu-content a {
          color: #334155;
        }

        body.light .menu-content a:hover {
          background-color: #d4e2f5;
        }

        body.light .static-links a {
          color: #2f3d4d;
          border-bottom: 1px solid #cbd5e0;
        }

        body.light .static-links a:hover {
          background-color: #d4e2f5;
        }

        body.light .sidebar-footer {
          border-top: 1px solid #cbd5e0;
        }

        body.light .sidebar-footer a {
          color: #3d4f65;
        }

        body.light .sidebar-footer a:hover {
          background-color: #d4e2f5;
        }


        body.light .mobile-toggle {
          background-color: #3498db;
          color: #ffffff;
        }

        body.light .charts {
          color: #1e2a3a;
          border: 2px solid #cbd5e0;
        }

                

        /*************END OF THE LIGHT STYLE ********/
        body.dark {
          background: #1e1e2f;
          color: #f9f9f9;
          transition: background 0.5s, color 0.5s;
        }

        .toggle-wrapper {
          text-align: right;  
        }

        .switch {
          position: relative;
          display: inline-block;
          width: 60px;
          height: 34px;
        }

        .switch input {
          opacity: 0;
          width: 0;
          height: 0;
        }

        .slider {
          position: absolute;
          top: 0; left: 0;
          right: 0; bottom: 0;
          background: #9d7a62; /* soft light brown */
          border-radius: 40px;
          cursor: pointer;
          transition: background 0.4s ease-in-out;
        }

        .slider::before {
          content: "☀︎"; /* Light Mode Symbol */
          position: absolute;
          height: 27px; width: 27px;
          left: 4px; top: 3.5px;
          color: rgb(61, 61, 61);
          background-color: hsl(24, 23%, 85%);          
          border-radius: 50%;
          display: flex;          
          align-items: center;
          justify-content: center;
          font-size: 1.2rem;
          transition: transform 0.4s ease-in-out, background 0.4s;
          box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        }

        input:checked + .slider {
          background: hsl(222, 43%, 46%); /* deep blue */
        }

        input:checked + .slider::before {
          content: "☾"; /* Dark Mode Symbol */
          color: black;
          transform: translateX(26px);
          background-color: hsl(222, 100%, 80%);
        }

        /* chart */
        /*here*/
        /* center and manage all the charts */ 
        .chart-container{
          display: flex;
          justify-content: center;
        }
        .charts{
          display: flex;
          flex-direction: column;
          justify-content: center;
          align-items: center;
          position: relative;   
          color: #fff;
          font-size: 1.2rem; 
          border: 2px solid hsla(221, 29%, 50%, 0.644) ;
          border-radius: 5px;
          padding: 10px;
        }
        .legend {
          display: flex;
          margin-top: 20px;
          
        }

        .legend-item {
          display: flex;
          align-items: center;
          margin-right: 20px;
          
        }

        .legend-color {
          width: 20px;
          height: 20px;
          border-radius: 50%;
          margin-right: 10px;
          margin-bottom: 7px;
        }        

        .legend-label {
          font-size: 16px;
        }

  </style>
</body>
</html>
