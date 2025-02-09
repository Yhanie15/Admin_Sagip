<?php
// topbar.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$admin_name = isset($_SESSION['admin_name']) ? $_SESSION['admin_name'] : 'Admin';
?>

<!-- Topbar Container -->
<div class="d-flex justify-content-between align-items-center bg-light shadow-sm p-2 mb-3" style="position: relative;">
    <!-- Philippine Standard Time -->
    <div style="
        color: #000; 
        padding: 5px 10px; 
        border-radius: 5px; 
        margin-left: 15px;
        background-color: #f8d7da; 
        font-size: 1.1rem;">
        <span id="philippineTime"></span>
    </div>

    <!-- Right side: Notifications + Admin -->
    <div class="d-flex align-items-center me-3 position-relative">
        <!-- Notification Icon -->
        <span 
            class="material-icons notifications" 
            id="notificationIcon"
            style="cursor: pointer; position: relative;"
            onclick="toggleNotifications()">
            notifications
        </span>

        <!-- Notification Badge -->
        <span 
            id="notificationCount" 
            class="badge bg-danger"
            style="
                position: absolute; 
                top: 1px; 
                right: 142px; 
                transform: translate(50%, -50%);
                display: none;
            "
        >
            0
        </span>
        
        <!-- Notification Dropdown -->
        <div 
            id="notifDropdown" 
            class="position-absolute bg-white shadow rounded d-none"
            style="
                right: -10px; 
                top: 35px; 
                width: 300px; 
                max-height: 400px; 
                overflow-y: auto; 
                z-index: 2000;
                border: 1px solid #ccc;"
        >
            <h6 class="p-2 border-bottom m-0">Notifications</h6>
            <ul id="notifList" class="list-unstyled m-0 p-0">
                <!-- Items injected by JS -->
            </ul>
        </div>
        
        <!-- Account Icon + Admin Name -->
        <span class="material-icons ms-4 me-2">account_circle</span>
        <span><?= htmlspecialchars($admin_name) ?></span>
    </div>
</div>

<!-- Philippine Time Script -->
<script>
function updatePhilippineTime() {
    const now = new Date();
    const philippineTimeString = now.toLocaleString('en-US', {
        timeZone: 'Asia/Manila',
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric', 
        hour: '2-digit', 
        minute: '2-digit', 
        second: '2-digit', 
        hour12: true
    });
    document.getElementById('philippineTime').textContent = philippineTimeString;
}
updatePhilippineTime();
setInterval(updatePhilippineTime, 1000);
</script>

<!-- Firebase Compat Scripts -->
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-app-compat.js"></script>
<script src="https://www.gstatic.com/firebasejs/9.22.2/firebase-database-compat.js"></script>

<style>
/* Hide scrollbar but keep scrollable */
#notifDropdown::-webkit-scrollbar {
  display: none; /* Chrome, Safari, Opera */
}
#notifDropdown {
  scrollbar-width: none; /* Firefox */
}
</style>

<script>
/*
  We will store an array of notifications in localStorage, 
  each with { id, type, message, timestamp, read:boolean }
  so we can show them as bold if read=false, or normal if read=true.

  The unreadCount is simply the count of all items with read=false.
*/

// localStorage keys
const STORAGE_NOTIFS = 'myNotifications';

// In-memory array of notifications (each item has a .read property)
let notifications = [];

// =============================
// 1) localStorage Functions
// =============================
function loadNotificationsFromStorage() {
  const stored = localStorage.getItem(STORAGE_NOTIFS);
  if (stored) {
    try {
      notifications = JSON.parse(stored);
    } catch (err) {
      console.error("Error parsing notifications from storage:", err);
      notifications = [];
    }
  } else {
    notifications = [];
  }
}

function saveNotificationsToStorage() {
  localStorage.setItem(STORAGE_NOTIFS, JSON.stringify(notifications));
}

// Recompute how many are unread
function getUnreadCount() {
  return notifications.filter(n => n.read === false).length;
}

// =============================
// 2) UI Updates
// =============================
function updateNotificationUI() {
  // Update the badge
  const unreadCount = getUnreadCount();
  const badge = document.getElementById('notificationCount');
  if (unreadCount > 0) {
    badge.textContent = unreadCount;
    badge.style.display = 'inline-block';
  } else {
    badge.textContent = '';
    badge.style.display = 'none';
  }

  // Build the list in #notifList
  const notifList = document.getElementById('notifList');
  notifList.innerHTML = '';

  // Show the last 10 notifications
  const recent = notifications.slice(0, 10);
  recent.forEach((notif) => {
    const li = document.createElement('li');
    li.className = 'p-2 border-bottom';
    li.style.cursor = 'pointer';
    // Bold if read=false
    li.style.fontWeight = notif.read ? 'normal' : 'bold';

    li.innerHTML = `
      <div>
        <strong>[${notif.type}]</strong> ${notif.message}<br>
        <small class="text-muted">${notif.timestamp}</small>
      </div>
    `;

    // On click, mark as read and redirect
    li.addEventListener('click', () => {
      if (!notif.read) {
        notif.read = true;
        saveNotificationsToStorage();
      }

      // Rebuild UI to unbold the item
      updateNotificationUI();

      // Redirect based on type
      if (notif.type.toLowerCase() === 'image report') {
        window.location.href = 'images.php';
      } else if (notif.type.toLowerCase() === 'call') {
        window.location.href = 'calls.php';
      }
    });

    notifList.appendChild(li);
  });

  // If we have more than 10 total, add a "View All" link
  if (notifications.length > 10) {
    const li = document.createElement('li');
    li.className = 'p-2 text-center';
    li.innerHTML = `<a href="#" class="text-primary">View All Notifications</a>`;
    notifList.appendChild(li);
  }
}

// Toggle the dropdown
function toggleNotifications() {
  const dropdown = document.getElementById('notifDropdown');
  dropdown.classList.toggle('d-none');
}

// =============================
// 3) Firebase Logic
// =============================
function setupFirebase() {
  const firebaseConfig = {
    apiKey: "AIzaSyBEzP4F_2vSvHqmHa3WdPgFxolLJYJ3i0I",
    authDomain: "your-project-id.firebaseapp.com",
    databaseURL: "https://capstone-sagip-siklab-default-rtdb.firebaseio.com/",
    projectId: "capstone-sagip-siklab",
    storageBucket: "your-project-id.appspot.com",
    messagingSenderId: "123456789012",
    appId: "1:247249927696:android:17ffe0adcbf0a673371495"
  };
  firebase.initializeApp(firebaseConfig);

  // Listen for new "reports_image" items
  const reportsRef = firebase.database().ref('reports_image');
  reportsRef.on('child_added', (snapshot) => {
    const data = snapshot.val();
    if (!data) return;

    // Make a unique ID (e.g. the snapshot key)
    const id = snapshot.key;

    // If this item is already in the array, skip
    if (notifications.find(n => n.id === id)) {
      return;
    }

    // Build a new notification
    const sender = data.senderName || 'Unknown';
    const time = data.timestamp || new Date().toISOString();
    const notif = {
      id,
      type: 'Image Report',
      message: `New image report from ${sender}`,
      timestamp: new Date(time).toLocaleString('en-US', {
        timeZone: 'Asia/Manila',
        hour12: true,
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }),
      read: false
    };
    notifications.unshift(notif);
    saveNotificationsToStorage();
    updateNotificationUI();
  });

  // Listen for new "Calls" items
  const callsRef = firebase.database().ref('Calls');
  callsRef.on('child_added', (snapshot) => {
    const data = snapshot.val();
    if (!data) return;

    const id = snapshot.key;
    if (notifications.find(n => n.id === id)) {
      return;
    }

    const caller = data.callerName || 'Unknown';
    const time = data.callTime || new Date().toISOString();
    const notif = {
      id,
      type: 'Call',
      message: `New call from ${caller}`,
      timestamp: new Date(time).toLocaleString('en-US', {
        timeZone: 'Asia/Manila',
        hour12: true,
        month: 'short',
        day: 'numeric',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }),
      read: false
    };
    notifications.unshift(notif);
    saveNotificationsToStorage();
    updateNotificationUI();
  });
}

// =============================
// 4) Initialization
// =============================
(function init() {
  // Load from localStorage, build UI
  loadNotificationsFromStorage();
  updateNotificationUI();

  // Set up Firebase watchers
  setupFirebase();
})();

// Close dropdown if user clicks outside
document.addEventListener('click', function(e) {
  const notifIcon = document.getElementById('notificationIcon');
  const dropdown = document.getElementById('notifDropdown');
  if (!dropdown) return;

  // If open, and click is outside both icon & dropdown, close it
  if (!dropdown.classList.contains('d-none')) {
    if (!notifIcon.contains(e.target) && !dropdown.contains(e.target)) {
      dropdown.classList.add('d-none');
    }
  }
});
</script>
