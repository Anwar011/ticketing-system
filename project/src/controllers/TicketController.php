<?php
require_once __DIR__ . '/../models/Ticket.php';

class TicketController {
    private $db;
    private $ticket;
    private $currentUser;

    public function __construct($db, $user) {
        $this->db = $db;
        $this->ticket = new Ticket($db);
        $this->currentUser = $user;
    }

    private function checkRole($role) {
        if ($this->currentUser['role'] !== $role) {
            return json_encode(['message' => 'Unauthorized']);
        }
        return null;
    }

    // Create a new ticket (clients only)
    public function createTicket($data) {
        $roleCheck = $this->checkRole(ROLE_CLIENT);
        if ($roleCheck) return $roleCheck;
    
        // Log the incoming data
        error_log('Creating ticket with data: ' . print_r($data, true));
    
        $this->ticket->title = $data['title'];
        $this->ticket->description = $data['description'];
        $this->ticket->status = 'open';
        $this->ticket->priority = $data['priority'];
        $this->ticket->client_id = $this->currentUser['id'];
        $this->ticket->agent_id = '1';
        $this->ticket->category = $data['category'];
    
        if ($this->ticket->create()) {
            error_log('Ticket created successfully.');
            return json_encode(['message' => 'Ticket created successfully.']);
        } else {
            error_log('Ticket creation failed.');
            return json_encode(['message' => 'Ticket creation failed.']);
        }
    }
    

    // Read all tickets
    public function readTickets() {
        $role = $this->currentUser['role'];
        if ($role === ROLE_SUPPORT || $role === ROLE_ADMIN) {
            $stmt = $this->ticket->read();
        } elseif ($role === ROLE_AGENT) {
            $stmt = $this->ticket->readAssignedToAgent($this->currentUser['id']);
        } else {
            return json_encode(['message' => 'Unauthorized']);
        }
        $tickets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return json_encode($tickets);
    }

    // Read a single ticket by ID
   
    public function readTicket($id) {
        $query = "SELECT * FROM tickets WHERE id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        if ($stmt->execute()) {
            $ticket = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($ticket) {
                // Return ticket details as JSON
                return json_encode($ticket);
            } else {
                // Return an error if no ticket is found
                http_response_code(404);
                return json_encode(['message' => 'Ticket not found']);
            }
        } else {
            // Return an error if query fails
            http_response_code(500);
            return json_encode(['message' => 'Error fetching ticket details']);
        }
    }
    
    // Update a ticket (agents only)
    public function updateTicket($data) {
        
        $this->ticket->id = $data['id'];
        $this->ticket->title = $data['title'];
        $this->ticket->description = $data['description'];
        $this->ticket->status = $data['status'];
        $this->ticket->priority = $data['priority'];
        $this->ticket->agent_id = $data['agent_id'];
        $this->ticket->type = $data['type'];

        if ($this->ticket->update()) {
            return json_encode(['message' => 'Ticket updated successfully.']);
        } else {
            return json_encode(['message' => 'Ticket update failed.']);
        }
    }

    // Delete a ticket (admins only)
    public function deleteTicket($id) {
        $roleCheck = $this->checkRole(ROLE_ADMIN);
        if ($roleCheck) return $roleCheck;

        $this->ticket->id = $id;

        if ($this->ticket->delete()) {
            return json_encode(['message' => 'Ticket deleted successfully.']);
        } else {
            return json_encode(['message' => 'Ticket deletion failed.']);
        }
    }

    // Add a comment (agents only)
    public function addComment($data) {
      
        $this->ticket->id = $data['ticket_id'];
        $comment = $data['comment'];
       

        if ($this->ticket->addComment($comment)) {
            return json_encode(['message' => 'Comment added successfully.']);
        } else {
            return json_encode(['message' => 'Comment addition failed.']);
        }
    }
    public function getComments($ticketId) {
        // Get ticket ID from the query parameter
        
        if ($ticketId) {
            // Fetch comments from the database
            $comments = $this->fetchCommentsFromDatabase($ticketId);
            return json_encode($comments);
        } else {
            header("HTTP/1.1 400 Bad Request");
            return json_encode(["message" => "Ticket ID is required."]);
        }
    }

    // Method to fetch comments from the database
    private function fetchCommentsFromDatabase($ticketId) {
        $query = "SELECT * FROM comments WHERE ticket_id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$ticketId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTicketType($data) {
        $roleCheck = $this->checkRole(ROLE_AGENT);
        if ($roleCheck) return $roleCheck;
    
        $this->ticket->id = $data['id'];
        $this->ticket->type = $data['type'];
    
        if ($this->ticket->updateType()) {
            return json_encode(['message' => 'Ticket type updated successfully.']);
        } else {
            return json_encode(['message' => 'Ticket type update failed.']);
        }
    }
    
    // Change the status of a ticket (admins and support staff)
    public function changeStatus($data) {
        
        $status = $data['status'];
        $this->ticket->id = $data['ticket_id'];
        
        if ($this->ticket->changeStatus($status)) {
            return json_encode(['message' => 'Status changed successfully.']);
        } else {
            return json_encode(['message' => 'Status change failed.']);
        }
    }

    public function assignTickets($data) {
        $ticketIds = $data['ticket_ids'];
        $agentId = $data['agent_id'];

        foreach ($ticketIds as $ticketId) {
            $query = "UPDATE tickets 
          SET agent_id = :agent_id, status = 'in_progress' 
          WHERE id = :ticket_id";
            $stmt = $this->db->prepare($query);
            $stmt->bindParam(':agent_id', $agentId);
            $stmt->bindParam(':ticket_id', $ticketId);
            $stmt->execute();
        }

        return json_encode(['message' => 'Tickets assigned successfully.']);
    }
}
?>
