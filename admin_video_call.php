<?php
// Include your Firebase DB connection
include('dbcon.php');

// Fetch image reports from Firebase
$ref = 'reports_image';
$reports = $database->getReference($ref)->getValue();

// Count statistics for the reports
$totalReports = $reports ? count($reports) : 0;
$resolvedReports = 0;
$inProgressReports = 0;
$pendingReports = 0;

if ($reports) {
    foreach ($reports as $report) {
        $status = strtolower($report['status'] ?? 'pending');
        if ($status === 'resolved') {
            $resolvedReports++;
        } elseif ($status === 'in progress') {
            $inProgressReports++;
        } elseif ($status === 'pending') {
            $pendingReports++;
        }
    }
}

// --- Video Call Logic ---
$callId = $_GET['callId'] ?? null;
if (!$callId) {
    die('Invalid call ID. Redirect back to the dashboard.');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Images Report & Video Call</title>
  
  <!-- Bootstrap CSS -->
  <link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css"
    rel="stylesheet"
  />
  <!-- Material Icons -->
  <link
    href="https://fonts.googleapis.com/icon?family=Material+Icons"
    rel="stylesheet"
  />
  <!-- Mapbox CSS -->
  <link
    href='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.css'
    rel='stylesheet'
  />
  <!-- Agora RTC SDK -->
  <script src="https://download.agora.io/sdk/release/AgoraRTC_N.js"></script>

  <style>
    /* Main Layout Styling */
    body, html {
      height: 100%;
      margin: 0;
      padding: 0;
      overflow-x: hidden;
    }
    #sidebar {
      width: 250px;
      position: fixed;
      top: 0;
      left: 0;
      height: 100%;
      background-color: #2b3e50;
      overflow-y: auto;
    }
    #content {
      margin-left: 250px; /* Same width as the sidebar */
      width: calc(100% - 250px);
      padding: 15px;
      min-height: 100vh;
      overflow-y: auto; /* Enable scrolling for the main content */
    }
    /* Responsive Sidebar */
    @media (max-width: 768px) {
      #sidebar {
        width: 200px;
      }
      #content {
        margin-left: 200px;
        width: calc(100% - 200px);
      }
    }
    @media (max-width: 576px) {
      #sidebar {
        width: 150px;
      }
      #content {
        margin-left: 150px;
        width: calc(100% - 150px);
      }
    }
    @media (max-width: 400px) {
      #sidebar {
        display: none; /* Hide the sidebar */
      }
      #content {
        margin-left: 0;
        width: 100%;
      }
    }

    /* Video Call Styling */
    #remote-video {
      width: 100%;
      height: 400px;
      background-color: black; /* A black backdrop for the video feed */
    }
    #controls {
      margin-top: 15px;
      display: flex;
      gap: 10px;
    }
    #controls button {
      padding: 8px 16px;
      border: none;
      border-radius: 4px;
      background-color: #007bff;
      color: white;
      cursor: pointer;
    }
    #controls .end-call {
      background-color: #dc3545;
    }
    #controls button:disabled {
      background-color: #cccccc;
      cursor: not-allowed;
    }
  </style>
</head>
<body>
  <!-- Sidebar -->
  <div id="sidebar">
    <?php include 'sidebar.php'; ?>
  </div>

  <!-- Main Content -->
  <div id="content" class="d-flex flex-column">
    <!-- Topbar -->
    <?php include 'topbar.php'; ?>

    <!-- Container for the UI -->
    <div class="container-fluid mt-4">
      <div class="row g-3">
        <!-- LEFT COLUMN: Video Call Section -->
        <div class="col-md-6">
          <!-- Remote video feed -->
          <div id="remote-video"></div>
          
          <!-- Video call controls -->
          <div id="controls">
            <button id="muteAudio">Mute Audio</button>
            <button id="endCall" class="end-call">End Call</button>
          </div>
        </div>

        <!-- RIGHT COLUMN: Caller Information + Map -->
        <div class="col-md-6">
          <h4>Caller Information</h4>
          <div class="mb-3">
            <label for="callerName" class="form-label">Name</label>
            <input
              type="text"
              class="form-control"
              id="callerName"
              placeholder="Value"
            />
          </div>
          <div class="mb-3">
            <label for="contactNumber" class="form-label">Contact Number</label>
            <input
              type="text"
              class="form-control"
              id="contactNumber"
              placeholder="Value"
            />
          </div>
          <div class="mb-3">
            <label class="form-label">Location</label>
            <!-- Map container -->
            <div
              id="map"
              style="width: 100%; height: 250px; border: 1px solid #ccc;"
            ></div>
          </div>
          <button 
            class="btn btn-danger mt-2"
            onclick="checkNearbyFiretruck()"
          >
            Check Nearby Firetruck
          </button>
        </div>
      </div>
    </div>
    <!-- End Container -->
  </div> <!-- End #content -->

  <!-- Bootstrap JS -->
  <script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"
  ></script>

  <!-- Mapbox JS -->
  <script src='https://api.mapbox.com/mapbox-gl-js/v2.15.0/mapbox-gl.js'></script>
  <script>
    // ========================
    // MAPBOX Initialization
    // ========================
    mapboxgl.accessToken = 'pk.eyJ1IjoieWhhbmllMTUiLCJhIjoiY2x5bHBrenB1MGxmczJpczYxbjRxbGxsYSJ9.DPO8TGv3Z4Q9zg08WhfoCQ'; 
    const map = new mapboxgl.Map({
      container: 'map',
      style: 'mapbox://styles/mapbox/streets-v11',
      center: [120.9842, 14.5995], // Example: Manila coordinates
      zoom: 12
    });

    function checkNearbyFiretruck() {
      // Replace with your logic for checking/pinging nearby firetrucks
      alert("Checking nearby firetrucks...");
    }

    // ========================
    // AGORA VIDEO CALL LOGIC
    // ========================
    const APP_ID = '9a8a11d5ff0a4f388d69ff7b5f803392';
    const TOKEN = '007eJxTYLAM39eqtaJjvr5miv20t1zMV39Om61c/TLhjbTcsvwtWU8VGCwTLRINDVNM09IMEk3SjC0sUsws09LMk0zTLAyMjS2N8ssK0xsCGRnWVQuzMDJAIIjPw1CcmJ5ZEF+cmZ2TmMTAAAA7NyIp';
    const CHANNEL_NAME = 'sagip_siklab';

    const client = AgoraRTC.createClient({ mode: "rtc", codec: "vp8" });

    let remoteStream;
    let localAudioTrack;
    let isMuted = false;

    // Join the channel
    async function joinChannel() {
        try {
            await client.join(APP_ID, CHANNEL_NAME, TOKEN, null);

            // Listen for remote user publishing media
            client.on("user-published", async (user, mediaType) => {
                await client.subscribe(user, mediaType);

                if (mediaType === "video") {
                    const remoteVideoTrack = user.videoTrack;
                    remoteVideoTrack.play("remote-video");
                }

                if (mediaType === "audio") {
                    const remoteAudioTrack = user.audioTrack;
                    remoteAudioTrack.play();
                }
            });

            // Listen for remote user un-publishing
            client.on("user-unpublished", (user) => {
                document.getElementById("remote-video").innerHTML = "";
            });

            // Listen for remote user leaving (this triggers when the resident ends the call)
            client.on("user-left", (user) => {
                document.getElementById("remote-video").innerHTML = "";
                // Redirect to call.php when the resident ends the call
                window.location.href = "calls.php";
            });

            // Publish local audio track
            localAudioTrack = await AgoraRTC.createMicrophoneAudioTrack();
            await client.publish([localAudioTrack]);
        } catch (error) {
            console.error("Failed to join channel:", error);
        }
    }

    // Mute/Unmute audio
    document.getElementById("muteAudio").addEventListener("click", () => {
        isMuted = !isMuted;
        localAudioTrack.setEnabled(!isMuted);
        document.getElementById("muteAudio").textContent = isMuted ? "Unmute Audio" : "Mute Audio";
    });

    // End call (admin ends the call)
    document.getElementById("endCall").addEventListener("click", async () => {
        try {
            await client.leave();
            localAudioTrack.close();
            document.getElementById("remote-video").innerHTML = "";
            alert("Call ended.");
            // Redirect to call.php when admin ends the call
            window.location.href = "calls.php";
        } catch (error) {
            console.error("Error ending call:", error);
        }
    });

    // Initialize the call on page load
    joinChannel();
  </script>
</body>
</html>
