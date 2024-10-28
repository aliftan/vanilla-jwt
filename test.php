<?php
require_once 'auth/JWT.php';
require_once 'auth/auth_class.php';

// Check if SQLite3 is enabled
if (!extension_loaded('sqlite3')) {
    die(json_encode(['error' => 'SQLite3 extension is not enabled']));
}

// Initialize Auth
$auth = new Auth();

// Initialize results array
$results = [];

// Test Registration
$token = null;
$results['registration'] = $auth->register('test@example.com', 'password123');

// Test Login
$loginResult = $auth->login('test@example.com', 'password123');
if ($loginResult['status'] === 'success') {
    $token = $loginResult['token'];
}
$results['login'] = $loginResult;

// Test Token Validation (if login was successful)
if ($token) {
    $results['token_validation'] = JWT::validate_token($token);
}

// Test Invalid Login
$results['invalid_login'] = $auth->login('test@example.com', 'wrongpassword');

// Test Duplicate Registration
$results['duplicate_registration'] = $auth->register('test@example.com', 'password123');

// Set JSON header
header('Content-Type: application/json');

// Output all results as JSON
echo json_encode($results, JSON_PRETTY_PRINT);
?>