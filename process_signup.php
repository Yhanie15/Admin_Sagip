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
        'created_at' => date('Y-m-d H:i:s')
    ];

    try {
        $database->getReference('admin')->push($userData);
        header("Location: index.php?success=1"); // Add a success query param
        exit;
    } catch (Exception $e) {
        // In production, log this error instead of exposing it
        die("Error saving user: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>