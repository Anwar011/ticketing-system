<?php
class User {
    private $conn;
    private $table = 'users';

    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $category;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Register a new user
    public function register() {
        $query = "INSERT INTO " . $this->table . " (name, email, password, role, category) VALUES (:name, :email, :password, :role, :category)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':name', $this->name);
        $stmt->bindParam(':email', $this->email);
        $stmt->bindParam(':password', $this->password);
        $stmt->bindParam(':role', $this->role);
        $stmt->bindParam(':category', $this->category);
        return $stmt->execute();
    }

    // User login
    public function login() {
        $query = "SELECT * FROM " . $this->table . " WHERE email = :email";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':email', $this->email);
        $stmt->execute();
        return $stmt;
    }

    // Get all users
    public function getAll() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function assignTicket($ticketId, $agentId) {
        $query = "UPDATE tickets SET assigned_to = :agentId WHERE id = :ticketId";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':agentId', $agentId);
        $stmt->bindParam(':ticketId', $ticketId);
        return $stmt->execute();
}
}
?>
