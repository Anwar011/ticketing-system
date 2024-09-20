<?php
class AdminController
{
    private $conn;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function updateUserRole($data)
    {
        $query = "UPDATE users SET role = :role WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':role', $data['role']);
        $stmt->bindParam(':id', $data['id']);

        if ($stmt->execute()) {
            return json_encode(['message' => 'User role updated successfully']);
        } else {
            return json_encode(['message' => 'Failed to update user role']);
        }
    }

}
?>
