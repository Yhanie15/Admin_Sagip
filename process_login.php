<?php
include('dbcon.php');
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $password = trim($_POST['password']);

    try {
        // Fetch admin data from Firebase
        $adminRef = $database->getReference('admin');
        $admins = $adminRef->getValue();

        if ($admins) {
            foreach ($admins as $key => $admin) {
                if ($admin['email'] === $email) {
                    if (password_verify($password, $admin['password'])) {
                        // Save admin details in session
                        $_SESSION['admin_id'] = $key;
                        $_SESSION['admin_name'] = $admin['name'];
                        $_SESSION['admin_email'] = $admin['email'];

                        // Redirect to dashboard
                        header("Location: dashboard.php");
                        exit;
                    } else {
                        // Invalid password
                        header("Location: login.php?error=1");
                        exit;
                    }
                }
            }
        }

        // Admin not found
        header("Location: login.php?error=2");
        exit;
    } catch (Exception $e) {
        die("Error logging in: " . $e->getMessage());
    }
} else {
    header("Location: login.php");
    exit;
}
?>