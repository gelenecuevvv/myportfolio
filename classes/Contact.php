<?php
class Contact {
    private $conn;
    private $table_name = "contacts";

    public $id;
    public $name;
    public $email;
    public $message;
    public $submitted_at;
    public $ip_address;
    public $status;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Create new contact
    public function create() {
        $query = "INSERT INTO " . $this->table_name . " 
                  SET name=:name, email=:email, message=:message, ip_address=:ip_address";

        $stmt = $this->conn->prepare($query);

        // Sanitize input
        $this->name = htmlspecialchars(strip_tags($this->name));
        $this->email = htmlspecialchars(strip_tags($this->email));
        $this->message = htmlspecialchars(strip_tags($this->message));
        $this->ip_address = htmlspecialchars(strip_tags($this->ip_address));

        // Bind values
        $stmt->bindParam(":name", $this->name);
        $stmt->bindParam(":email", $this->email);
        $stmt->bindParam(":message", $this->message);
        $stmt->bindParam(":ip_address", $this->ip_address);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Read all contacts
    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY submitted_at DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    // Get contact by ID
    public function readOne() {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if($row) {
            $this->name = $row['name'];
            $this->email = $row['email'];
            $this->message = $row['message'];
            $this->submitted_at = $row['submitted_at'];
            $this->ip_address = $row['ip_address'];
            $this->status = $row['status'];
            return true;
        }

        return false;
    }

    // Update contact status
    public function updateStatus() {
        $query = "UPDATE " . $this->table_name . " SET status = :status WHERE id = :id";
        $stmt = $this->conn->prepare($query);

        $this->status = htmlspecialchars(strip_tags($this->status));
        $this->id = htmlspecialchars(strip_tags($this->id));

        $stmt->bindParam(':status', $this->status);
        $stmt->bindParam(':id', $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Delete contact
    public function delete() {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);

        $this->id = htmlspecialchars(strip_tags($this->id));
        $stmt->bindParam(1, $this->id);

        if($stmt->execute()) {
            return true;
        }

        return false;
    }

    // Validate email
    public function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Check for spam (basic validation)
    public function isSpam() {
        $spam_words = ['viagra', 'casino', 'lottery', 'winner', 'congratulations'];
        $text = strtolower($this->name . ' ' . $this->email . ' ' . $this->message);
        
        foreach($spam_words as $word) {
            if(strpos($text, $word) !== false) {
                return true;
            }
        }
        
        return false;
    }
}
?>