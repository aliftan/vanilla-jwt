<?php
session_start();
require_once 'JWT.php';
require_once 'auth_class.php';

header('Content-Type: application/json');

// Initialize Auth
$auth = new Auth();

// Handle requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch($action) {
        case 'register':
            if (!isset($_POST['email']) || !isset($_POST['password'])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Email and password are required'
                ]);
                exit;
            }
            
            $result = $auth->register($_POST['email'], $_POST['password']);
            echo json_encode($result);
            break;
            
        case 'login':
            if (!isset($_POST['email']) || !isset($_POST['password'])) {
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Email and password are required'
                ]);
                exit;
            }
            
            $result = $auth->login($_POST['email'], $_POST['password']);
            if ($result['status'] === 'success') {
                $_SESSION['token'] = $result['token'];
                $_SESSION['user_email'] = $_POST['email'];
            }
            echo json_encode($result);
            break;
            
        case 'logout':
            session_destroy();
            echo json_encode([
                'status' => 'success',
                'message' => 'Logged out successfully'
            ]);
            break;
            
        default:
            echo json_encode([
                'status' => 'error',
                'message' => 'Invalid action'
            ]);
    }
} else {
    echo json_encode([
        'status' => 'error',
        'message' => 'Invalid request method'
    ]);
}
?>