<?php 
require __DIR__.'/vendor/autoload.php';

use Kreait\Firebase\Factory;

$factory = (new Factory)
    ->withServiceAccount('capstone-sagip-siklab-firebase-adminsdk-mo00s-bc1514721b.json')
    ->withDatabaseUri('https://capstone-sagip-siklab-default-rtdb.firebaseio.com/');

$database = $factory->createDatabase();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calls Report</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
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
            /*background-color: #2b3e50;*/
            overflow-y: auto;
        }

        #content {
            margin-left: 250px;
            width: calc(100% - 250px);
            padding: 15px;
            /*padding: 0 15px 15px 15px; /* Removed unnecessary top padding */
            min-height: 100vh;
            overflow-y: auto;
        }

        /* Stat boxes layout */
        .stat-box {
            display: flex;
            justify-content: center;
            align-items: center;
            flex-direction: column;
            width: 200px;
            height: 100px;
            text-align: center;
            border-radius: 8px;
            font-size: 14px;
            font-weight: bold;
            margin: 10px;
        }

        .gap-3 {
            gap: 1rem;
        }

        /* Color boxes */
                /* Background color styles for each type */
                .bg-primary {
            background-color: #007bff;
            color: white;
        }

        .bg-warning {
            background-color: #ffc107;
            color: black;
        }

        .bg-success {
            background-color: #28a745;
            color: white;
        }

        .bg-danger {
            background-color: #dc3545;
            color: white;
        }

        /* Adjusting the list view */
        .list-group-item {
            font-size: 14px;
            padding: 12px;
        }

        .list-group-item button {
            font-size: 14px;
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

        /* Full width content for very small screens */
        @media (max-width: 400px) {
            #sidebar {
                display: none; /* Hide the sidebar */
            }
            #content {
                margin-left: 0;
                width: 100%;
            }
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

        <!-- Stat Boxes -->
        <div class="d-flex justify-content-center align-items-center flex-wrap gap-3 mt-3">
            <div class="stat-box bg-primary">
                <strong>Total Calls<br>Today</strong>
                <div>20</div>
            </div>
            <div class="stat-box bg-warning">
                <strong>Missed Calls</strong>
                <div>5</div>
            </div>
            <div class="stat-box bg-success">
                <strong>Answered Calls</strong>
                <div>15</div>
            </div>
            <div class="stat-box bg-danger">
                <strong>Ongoing Calls</strong>
                <div id="ongoing-count">0</div>
            </div>
        </div>

        <!-- Calls Section -->
        <div class="row mt-3">
            <div class="col-md-6">
                <h5>Incoming Calls</h5>
                <div class="list-group" id="incoming-calls">
                    <div class="list-group-item">
                        Loading incoming calls...
                    </div>
                </div>
            </div>

            <!-- Call Log Section -->
            <div class="col-md-6">
                <h5>Call Log</h5>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Caller Name</th>
                                <th>Location</th>
                                <th>Time</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody id="call-log">
                            <tr>
                                <td colspan="4">No call logs available.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.1/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        async function fetchCalls() {
            try {
                const response = await fetch('fetch_calls.php');
                const data = await response.json();

                const incomingCalls = data.incoming || {};
                const callLog = data.callLog || [];

                // Update Incoming Calls Section
                const incomingCallsContainer = document.getElementById('incoming-calls');
                incomingCallsContainer.innerHTML = '';
                let ongoingCount = 0;

                for (const [key, call] of Object.entries(incomingCalls)) {
                    if (call.status === 'Ongoing') {
                        ongoingCount++;
                        incomingCallsContainer.innerHTML += `
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>Name:</strong> ${call.residentName}<br>
                                    <strong>Location:</strong> ${call.address || 'N/A'}
                                </div>
                                <button class="btn btn-warning" onclick="answerCall('${key}')">ANSWER</button>
                            </div>
                        `;
                    }
                }

                if (ongoingCount === 0) {
                    incomingCallsContainer.innerHTML = '<div class="list-group-item">No incoming calls at the moment.</div>';
                }

                // Update Ongoing Call Count
                document.getElementById('ongoing-count').textContent = ongoingCount;

                // Update Call Log Section
                const callLogContainer = document.getElementById('call-log');
                callLogContainer.innerHTML = '';

                for (const call of callLog) {
                    callLogContainer.innerHTML += `
                        <tr>
                            <td>${call.residentName}</td>
                            <td>${call.address || 'N/A'}</td>
                            <td>${new Date(call.time).toLocaleTimeString()}</td>
                            <td>${call.status}</td>
                        </tr>
                    `;
                }
            } catch (error) {
                console.error('Error fetching calls:', error);
            }
        }

        async function answerCall(callId) {
            try {
                const response = await fetch('answer_call.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ callId })
                });

                const data = await response.json();
                if (data.success) {
                    alert('Call answered successfully.');
                    fetchCalls();
                } else {
                    alert('Failed to answer call.');
                }
            } catch (error) {
                console.error('Error answering call:', error);
            }
        }

        // Fetch calls initially and refresh every 5 seconds
        fetchCalls();
        setInterval(fetchCalls, 5000);
    </script>
</body>
</html>
