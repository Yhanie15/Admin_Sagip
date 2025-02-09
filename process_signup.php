<?php
include('dbcon.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = htmlspecialchars(trim($_POST['name']));
    $email = htmlspecialchars(trim($_POST['email']));
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    $userType = htmlspecialchars(trim($_POST['user_type']));

    $userData = [
        'name' => $name,
        'email' => $email,
        'password' => $password,
        'user_type' => $userType,
        'created_at' => date('Y-m-d H:i:s')
    ];

    try {
        $database->getReference('admin')->push($userData);
        // Redirect back to admin_users.php with success=true
        header("Location: admin_users.php?success=true");
        exit;
    } catch (Exception $e) {
        // Redirect back with error message
        header("Location: admin_users.php?success=false&error=" . urlencode($e->getMessage()));
        exit;
    }
} else {
    header("Location: admin_users.php");
    exit;
}
?>
