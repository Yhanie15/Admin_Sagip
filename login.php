<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | SAGIP-SIKLAB</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Body Styling with Muted Deep Red Gradient Background */
        body {
            margin: 0;
            padding: 0;
            height: 100vh;
            background: linear-gradient(135deg, #660000, #800000); /* Muted deep red gradient */
            display: flex;
            justify-content: center;
            align-items: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Login Container */
        .login-container {
            display: flex;
            width: 90%;
            max-width: 900px;
            border-radius: 15px;
            /* Updated Box-Shadow for Yellowish Red Glow */
            box-shadow: 0 0 20px rgba(255, 69, 0, 0.7), 0 0 40px rgba(255, 140, 0, 0.6);
            overflow: hidden;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        /* Left Side for Logo */
        .logo-side {
            flex: 1;
            display: flex;
            justify-content: center;
            align-items: center;
            background-color: #4d0000; /* Darker red for logo side */
        }

        .logo-side img {
            max-width: 80%;
            height: auto;
        }

        /* Right Side for Login Form */
        .login-form-side {
            flex: 1.5;
            padding: 60px 40px;
            background: rgba(255, 255, 255, 0.2); /* Slightly lighter for contrast */
            box-shadow: inset 0 0 10px rgba(0, 0, 0, 0.2);
            border-radius: 0 15px 15px 0;
        }

        /* Form Styling */
        .form-label {
            font-weight: bold;
            color: #e0e0e0; /* Light text for labels */
        }

        .form-control {
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid #ccc; /* Neutral gray border */
            color: #e0e0e0;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.3);
            border-color: #ccc; /* Consistent gray border on focus */
            color: #fff;
            box-shadow: none;
        }

        .btn-danger {
            background-color: #800000; /* Muted deep red */
            border-color: #800000;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .btn-danger:hover {
            background-color: #660000; /* Darker muted red on hover */
            border-color: #660000;
        }

        /* Eye Icon for Password Toggle */
        .password-container {
            position: relative;
        }

        .password-container button {
            position: absolute;
            top: 70%;
            right: 20px; /* Adjust the button closer inside */
            transform: translateY(-50%);
            border: none;
            background: transparent;
            cursor: pointer;
            padding: 0;
            color: #e0e0e0;
            z-index: 2; /* Ensures the button stays above the input */
        }

        .password-container button:focus {
            outline: none;
        }

        /* Notification Styles */
        #notification {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
                max-width: 500px;
            }

            .logo-side, .login-form-side {
                flex: none;
                width: 100%;
                border-radius: 0;
            }

            .login-form-side {
                padding: 40px 20px;
            }
        }

        @media (max-width: 480px) {
            .login-form-side {
                padding: 30px 15px;
            }
        }
    </style>
</head>
<body>

    <div id="notification" class="d-none"></div> <!-- Notification Placeholder -->

    <!-- Login Container -->
    <div class="login-container">
        <!-- Left Side for Logo -->
        <div class="logo-side">
            <img src="img/ADMIN_logo.png" alt="Admin Logo"> <!-- Ensure the path is correct -->
        </div>

        <!-- Right Side for Login Form -->
        <div class="login-form-side">
            <form action="process_login.php" method="POST" id="loginForm">
                <div class="mb-4">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="mb-4 password-container">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                    <button type="button" id="togglePassword" aria-label="Toggle Password Visibility">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
                <button type="submit" class="btn btn-danger w-100">Login</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Font Awesome JS (optional if needed for dynamic icons) -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/js/all.min.js"></script>
    <script>
        // Handle notification visibility on page load
        document.addEventListener("DOMContentLoaded", () => {
            const urlParams = new URLSearchParams(window.location.search);
            const error = urlParams.get("error");

            if (error === "1") {
                showNotification("Invalid email or password!", "danger");
            } else if (error === "2") {
                showNotification("User not found!", "warning");
            }
        });

        // Show notification function
        function showNotification(message, type) {
            const notification = document.getElementById("notification");
            notification.className = `alert alert-${type} alert-dismissible fade show`;
            notification.innerHTML = `
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            `;
            notification.classList.remove("d-none");
        }

        // Password visibility toggle
        const togglePassword = document.getElementById("togglePassword");
        const passwordInput = document.getElementById("password");
        const toggleIcon = togglePassword.querySelector("i");

        togglePassword.addEventListener("click", function () {
            // Toggle the type of the input between 'password' and 'text'
            const type = passwordInput.type === "password" ? "text" : "password";
            passwordInput.type = type;

            // Toggle the eye icon
            toggleIcon.classList.toggle("fa-eye");
            toggleIcon.classList.toggle("fa-eye-slash");
        });
    </script>
</body>
</html>
