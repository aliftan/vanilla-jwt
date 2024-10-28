<?php
session_start();
require_once 'auth/JWT.php';

function isLoggedIn() {
    return isset($_SESSION['token']) && isset($_SESSION['user_email']);
}

// Handle logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_destroy();
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Home</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 20px auto; padding: 0 20px; }
        .nav { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 8px; }
        button { padding: 10px 20px; cursor: pointer; }
        #debugPanel {
            background: #f5f5f5;
            padding: 15px;
            margin-top: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        #debugPanel pre {
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .debug-btn {
            background: #4CAF50;
            color: white;
            border: none;
            padding: 5px 10px;
            cursor: pointer;
            margin: 5px;
        }
    </style>
</head>
<body>
    <div class="nav">
        <h1>My App</h1>

        <a href="test.php">Test</a>

        <?php if (isLoggedIn()): ?>
            <div>
                <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_email']); ?></span>
                <a href="?action=logout"><button>Logout</button></a>
            </div>
        <?php endif; ?>
    </div>

    <?php if (isLoggedIn()): ?>
        <div>
            <h2>Welcome to Dashboard</h2>
            <p>You are logged in as: <?php echo htmlspecialchars($_SESSION['user_email']); ?></p>
        </div>
    <?php else: ?>
        <div id="loginRegisterForms">
            <h2>Login</h2>
            <form id="loginForm">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">Login</button>
            </form>

            <h2>Register</h2>
            <form id="registerForm">
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required>
                </div>
                <div class="form-group">
                    <label>Password:</label>
                    <input type="password" name="password" required>
                </div>
                <button type="submit">Register</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Debug Panel -->
    <div id="debugPanel">
        <h3>Debug Information</h3>
        <button class="debug-btn" onclick="refreshDebugInfo()">Refresh Debug Info</button>
        <button class="debug-btn" onclick="toggleTokenFormat()">Toggle Token Format</button>
        <div id="debugInfo">
            <h4>Session Data:</h4>
            <pre><?php print_r($_SESSION); ?></pre>
        </div>
    </div>

    <script>
        // Function to refresh debug info via AJAX
        async function refreshDebugInfo() {
            const response = await fetch('debug.php');
            const data = await response.json();
            document.querySelector('#debugInfo').innerHTML = `
                <h4>Session Data:</h4>
                <pre>${JSON.stringify(data, null, 2)}</pre>
            `;
        }

        // Function to toggle between raw and decoded token
        function toggleTokenFormat() {
            const debugInfo = document.querySelector('#debugInfo');
            const token = <?php echo isset($_SESSION['token']) ? json_encode($_SESSION['token']) : 'null'; ?>;
            
            if (token) {
                try {
                    // Split the token and decode the payload
                    const [header, payload, signature] = token.split('.');
                    const decodedPayload = JSON.parse(atob(payload.replace(/-/g, '+').replace(/_/g, '/')));
                    
                    debugInfo.innerHTML = `
                        <h4>Decoded Token:</h4>
                        <pre>Header: ${atob(header)}
Payload: ${JSON.stringify(decodedPayload, null, 2)}
Signature: ${signature}</pre>
                    `;
                } catch (e) {
                    debugInfo.innerHTML = `<pre>Error decoding token: ${e.message}</pre>`;
                }
            }
        }

        // Handle Login Form
        document.getElementById('loginForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'login');
            
            try {
                const response = await fetch('auth/auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                if (data.status === 'success') {
                    console.log('JWT Token:', data.token); // Debug log
                    window.location.reload();
                } else {
                    alert(data.message || 'Login failed');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        });

        // Handle Register Form
        document.getElementById('registerForm')?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            formData.append('action', 'register');
            
            try {
                const response = await fetch('auth/auth.php', {
                    method: 'POST',
                    body: formData
                });
                const data = await response.json();
                
                alert(data.message);
                if (data.status === 'success') {
                    e.target.reset();
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred');
            }
        });
    </script>
</body>
</html>