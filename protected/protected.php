<?php
session_start();
require_once '../auth/JWT.php';

// Check if user is logged in
if (!isset($_SESSION['token']) || !isset($_SESSION['user_email'])) {
    header('Location: ../index.php');
    exit;
}

// Validate token
$payload = JWT::validate_token($_SESSION['token']);
if (!$payload) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

// User is authenticated, show protected content
?>
<!DOCTYPE html>
<html>
<head>
    <title>Protected Page</title>
</head>
<body>
    <h1>Protected Content</h1>
    <p>Welcome <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
    <p>This is a protected page.</p>
    <a href="../index.php">Back to Home</a>
</body>
</html>