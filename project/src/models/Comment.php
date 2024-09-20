<?php
class Comment
{
    private $conn;
    private $table_name = "comments";

    public $id;
    public $ticket_id;
    public $user_id;
    public $comment;
    public $created_at;

    public function __construct($db)
    {
        $this->conn = $db;
    }

    public function create()
    {
        $query = "INSERT INTO " . $this->table_name . " SET ticket_id=:ticket_id, user_id=:user_id, comment=:comment, created_at=:created_at";

        $stmt = $this->conn->prepare($query);

        $this->ticket_id = htmlspecialchars(strip_tags($this->ticket_id));
        $this->user_id = htmlspecialchars(strip_tags($this->user_id));
        $this->comment = htmlspecialchars(strip_tags($this->comment));
        $this->created_at = htmlspecialchars(strip_tags($this->created_at));

        $stmt->bindParam(":ticket_id", $this->ticket_id);
        $stmt->bindParam(":user_id", $this->user_id);
        $stmt->bindParam(":comment", $this->comment);
        $stmt->bindParam(":created_at", $this->created_at);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function read()
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE ticket_id = ? ORDER BY created_at DESC";

        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->ticket_id);
        $stmt->execute();

        return $stmt;
    }
}
?>
