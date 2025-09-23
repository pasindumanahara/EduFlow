

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
