<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAGIP-SIKLAB | Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* Fullscreen background */
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: url('img/firetruck.jpg') no-repeat center center fixed; /* Add your image path here */
            background-size: cover;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Notification styles */
        #notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050; /* Ensure it appears on top of other elements */
        }

        /* Center the card */
        .login-card {
            max-width: 400px;
            width: 100%;
        }

        /* Shadow for better visibility */
        .card {
            box-shadow: 0px 4px 15px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
    <div id="notification" class="d-none"></div> <!-- Placeholder for Notification -->

    <!-- Centered login form -->
    <div class="login-card">
        <div class="card">
            <div class="card-header bg-danger text-white text-center">
                <h3>SAGIP-SIKLAB LogIn</h3>
            </div>
            <div class="card-body">
                <form action="process_login.php" method="POST" id="loginForm">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    <button type="submit" class="btn btn-danger w-100">Login</button>
                </form>
            </div>
            <div class="card-footer text-center">
                <small>Don't have an account? <a href="signup.php">Sign up here</a>.</small>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Handle notification visibility on page load (using PHP query params)
        document.addEventListener("DOMContentLoaded", () => {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get("error");

            if (error === "1") {
                showNotification("Invalid email or password!", "danger");
            } else if (error === "2") {
                showNotification("User not found!", "warning");
            }
        });

        // Function to show notification
        function showNotification(message, type) {
            const notification = document.getElementById("notification");
            notification.className = `alert alert-${type} alert-dismissible fade show`;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            notification.classList.remove("d-none");
        }
    </script>
</body>
</html>
