<?php
class Ticket {
    private $conn;
    private $table = 'tickets';

    public $id;
    public $title;
    public $description;
    public $status;
    public $priority;
    public $client_id;
    public $agent_id;
    public $category;
    public $type;
    public $created_at;
    public $updated_at;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create a new ticket
    public function create() {
        $query = "INSERT INTO " . $this->table . " (title, description, status, priority, client_id, agent_id, category) VALUES (:title, :description, :status, :priority, :client_id, :agent_id, :category)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':client_id', $this->client_id);
        $stmt->bindParam(':agent_id', $this->agent_id);
        $stmt->bindParam(':category', $this->category);

        return $stmt->execute();
    }

    // Read all tickets
    public function read() {
        $query = "SELECT * FROM " . $this->table;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }
    

    // Read tickets assigned to a specific agent
    public function readAssignedToAgent($agent_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE agent_id = :agent_id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':agent_id', $agent_id);
        $stmt->execute();
        return $stmt;
    }

    // Read a single ticket by ID
    public function readSingle() {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        $stmt->execute();
        return $stmt;
    }

    // Update a ticket
    public function update() {
        $query = "UPDATE " . $this->table . " SET type= :type, title = :title, description = :description, status = :status, priority = :priority, agent_id = :agent_id WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':title', $this->title);
        $stmt->bindParam(':description', $this->description);
        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':priority', $this->priority);
        $stmt->bindParam(':agent_id', $this->agent_id);
        $stmt->bindParam(':id', $this->id);
        $stmt->bindParam(':type', $this->type);

        return $stmt->execute();
    }

    // Delete a ticket
    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
    public function updateType() {
        $query = "UPDATE " . $this->table . " SET type = :type WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':type', $this->type);
        $stmt->bindParam(':id', $this->id);
        return $stmt->execute();
    }
    

    // Add a comment to a ticket
    public function addComment($comment) {
        $query = "INSERT INTO comments (ticket_id, comment) VALUES (:ticket_id, :comment)";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':ticket_id', $this->id);
        $stmt->bindParam(':comment', $comment);
        return $stmt->execute();
    }

    // Change the status of a ticket
    public function changeStatus($status) {
            $query = "UPDATE " . $this->table . " SET status = :status WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':status', $status);
            $stmt->bindParam(':id', $this->id);
            return $stmt->execute();
       
        return false;
 }
}
?>
