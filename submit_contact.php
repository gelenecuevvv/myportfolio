<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include database and Contact class
include_once 'config/database.php';
include_once 'classes/Contact.php';

// Response array
$response = array();

// Check if request is POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    // Get posted data
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $message = isset($_POST['message']) ? trim($_POST['message']) : '';
    
    // Validate required fields
    if (empty($name) || empty($email) || empty($message)) {
        $response['success'] = false;
        $response['message'] = 'All fields are required.';
        echo json_encode($response);
        exit;
    }
    
    // Validate name length
    if (strlen($name) < 2 || strlen($name) > 100) {
        $response['success'] = false;
        $response['message'] = 'Name must be between 2 and 100 characters.';
        echo json_encode($response);
        exit;
    }
    
    // Validate message length
    if (strlen($message) < 10 || strlen($message) > 1000) {
        $response['success'] = false;
        $response['message'] = 'Message must be between 10 and 1000 characters.';
        echo json_encode($response);
        exit;
    }
    
    // Database connection
    $database = new Database();
    $db = $database->getConnection();
    
    if ($db) {
        // Create contact object
        $contact = new Contact($db);
        
        // Validate email
        if (!$contact->validateEmail($email)) {
            $response['success'] = false;
            $response['message'] = 'Please enter a valid email address.';
            echo json_encode($response);
            exit;
        }
        
        // Set contact properties
        $contact->name = $name;
        $contact->email = $email;
        $contact->message = $message;
        $contact->ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        
        // Check for spam
        if ($contact->isSpam()) {
            $response['success'] = false;
            $response['message'] = 'Your message appears to be spam. Please try again.';
            echo json_encode($response);
            exit;
        }
        
        // Rate limiting: Check if same IP submitted in last 5 minutes
        $check_query = "SELECT COUNT(*) as count FROM contacts 
                       WHERE ip_address = :ip AND submitted_at > DATE_SUB(NOW(), INTERVAL 5 MINUTE)";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->bindParam(':ip', $contact->ip_address);
        $check_stmt->execute();
        $result = $check_stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 2) {
            $response['success'] = false;
            $response['message'] = 'Too many submissions. Please wait 5 minutes before submitting again.';
            echo json_encode($response);
            exit;
        }
        
        // Try to create the contact
        if ($contact->create()) {
            $response['success'] = true;
            $response['message'] = 'Thank you for your message! I will get back to you soon.';
            
            // Optional: Send email notification to yourself
            $to = 'cuevomac@gmail.com'; // Your email
            $subject = 'New Contact Form Submission from Portfolio';
            $email_message = "
                New contact form submission:\n\n
                Name: $name\n
                Email: $email\n
                Message: $message\n
                IP Address: {$contact->ip_address}\n
                Submitted: " . date('Y-m-d H:i:s') . "\n
            ";
            $headers = "From: noreply@yourportfolio.com\r\n";
            $headers .= "Reply-To: $email\r\n";
            
            // Uncomment the line below to enable email notifications
            // mail($to, $subject, $email_message, $headers);
            
        } else {
            $response['success'] = false;
            $response['message'] = 'Sorry, there was an error saving your message. Please try again.';
        }
        
    } else {
        $response['success'] = false;
        $response['message'] = 'Database connection failed. Please try again later.';
    }
    
} else {
    $response['success'] = false;
    $response['message'] = 'Invalid request method.';
}

echo json_encode($response);
?> 