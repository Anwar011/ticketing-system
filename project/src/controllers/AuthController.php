<?php
require_once __DIR__ . '/../models/User.php';

class AuthController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    public function registerUser($data) {
        $this->user->name = $data['name'];
        $this->user->email = $data['email'];
        $this->user->password = password_hash($data['password'], PASSWORD_BCRYPT);
        $this->user->role = $data['role'];
        $this->user->category = $data['category'];

        if ($this->user->register()) {
            return json_encode(['message' => 'User registered successfully.']);
        } else {
            return json_encode(['message' => 'User registration failed.']);
        }
    }

    public function loginUser($data) {
        

        $this->user->email = $data['email'];
        $password = $data['password'];
        
        try {
            $stmt = $this->user->login();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Store user info in session
                $_SESSION['user'] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'role' => $user['role']
                ];

                return json_encode([
                    'message' => 'Login successful.',
                    'role' => $user['role'],
                    'user' => $user
                ]);
            } else {
                return json_encode(['message' => 'Invalid credentials.']);
            }
        } catch (Exception $e) {
            return json_encode(['message' => 'Error: ' . $e->getMessage()]);
        }
    }
    

    public function getUsers() {
        $stmt = $this->user->getAll();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($users);
    }
}
