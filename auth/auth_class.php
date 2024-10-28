<?php
class Auth {
    private $db = null;
    private $db_file = __DIR__ . '/../database/auth.sqlite';

    public function __construct() {
        try {
            // Create database directory if it doesn't exist
            if (!is_dir(dirname($this->db_file))) {
                mkdir(dirname($this->db_file), 0777, true);
            }

            // Connect to SQLite database
            $this->db = new SQLite3($this->db_file);
            
            // Create users table if it doesn't exist
            $this->db->exec('
                CREATE TABLE IF NOT EXISTS users (
                    id INTEGER PRIMARY KEY AUTOINCREMENT,
                    email TEXT UNIQUE NOT NULL,
                    password TEXT NOT NULL,
                    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
                )
            ');
        } catch (Exception $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function login($email, $password) {
        // Sanitize inputs
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        
        // Prepare statement
        $stmt = $this->db->prepare("SELECT id, email, password FROM users WHERE email = :email");
        $stmt->bindValue(':email', $email, SQLITE3_TEXT);
        $result = $stmt->execute();
        
        if ($user = $result->fetchArray(SQLITE3_ASSOC)) {
            if (password_verify($password, $user['password'])) {
                // Generate payload for JWT
                $payload = [
                    'user_id' => $user['id'],
                    'email' => $user['email']
                ];

                // Generate token
                $token = JWT::generate_token($payload);

                return [
                    'status' => 'success',
                    'token' => $token
                ];
            }
        }

        return [
            'status' => 'error',
            'message' => 'Invalid credentials'
        ];
    }

    public function register($email, $password) {
        // Validate email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return [
                'status' => 'error',
                'message' => 'Invalid email format'
            ];
        }

        // Validate password strength
        if (strlen($password) < 8) {
            return [
                'status' => 'error',
                'message' => 'Password must be at least 8 characters long'
            ];
        }

        try {
            // Check if user exists
            $stmt = $this->db->prepare("SELECT id FROM users WHERE email = :email");
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $result = $stmt->execute();
            
            if ($result->fetchArray()) {
                return [
                    'status' => 'error',
                    'message' => 'Email already exists'
                ];
            }

            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert new user
            $stmt = $this->db->prepare("INSERT INTO users (email, password) VALUES (:email, :password)");
            $stmt->bindValue(':email', $email, SQLITE3_TEXT);
            $stmt->bindValue(':password', $hashed_password, SQLITE3_TEXT);
            
            if ($stmt->execute()) {
                return [
                    'status' => 'success',
                    'message' => 'Registration successful'
                ];
            }
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => 'Registration failed: ' . $e->getMessage()
            ];
        }

        return [
            'status' => 'error',
            'message' => 'Registration failed'
        ];
    }
}
?>