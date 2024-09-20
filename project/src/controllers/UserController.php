<?php
require_once __DIR__ . '/../models/User.php';

class UserController {
    private $db;
    private $user;

    public function __construct($db) {
        $this->db = $db;
        $this->user = new User($db);
    }

    // Register a new user
    public function registerUser($data) {
        $this->user->name = $data['name'];
        $this->user->email = $data['email'];
        $this->user->password = password_hash($data['password'], PASSWORD_BCRYPT);
        $this->user->role = $data['role'];

        if ($this->user->register()) {
            return json_encode(['message' => 'User registered successfully.']);
        } else {
            return json_encode(['message' => 'User registration failed.']);
        }
    }

    // User login
    public function loginUser($data) {
        $this->user->email = $data['email'];
        $password = $data['password'];

        $stmt = $this->user->login();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            return json_encode(['message' => 'Login successful.', 'user' => $user]);
        } else {
            return json_encode(['message' => 'Login failed.']);
        }
    }

    // Get all users
    public function getAllUsers() {
        $stmt = $this->user->getAll();
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($users);
    }
    public function assignTicketToAgent($data) {
        $userRole = $data['role'];  // Assuming role is sent in the request data
        $ticketId = $data['ticketId'];
        $agentId = $data['agentId'];
    
        if ($userRole === 'support' || $userRole === 'admin') {
            if ($this->user->assignTicket($ticketId, $agentId)) {
                return json_encode(['message' => 'Ticket assigned successfully.']);
            } else {
                return json_encode(['message' => 'Ticket assignment failed.']);
            }
        } else {
            return json_encode(['message' => 'Unauthorized to assign tickets.']);
        }
    }
    public function getAgents() {
        $query = "SELECT id, name, email, category FROM users WHERE role = 'agent'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $agents = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($agents);
    }
    public function getSupports() {
        $query = "SELECT id, name, email, category FROM users WHERE role = 'support'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $supports = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($supports);
    }
    public function getAdmins() {
        $query = "SELECT id, name, email, category FROM users WHERE role = 'admin'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($admins);
    }
    public function getClients() {
        $query = "SELECT id, name, email FROM users WHERE role = 'client'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();

        $clients = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($clients);
    }
}
?>
