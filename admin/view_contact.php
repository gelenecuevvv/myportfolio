<?php
// Include database and Contact class
include_once '../config/database.php';
include_once '../classes/Contact.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: contacts.php');
    exit;
}

// Database connection
$database = new Database();
$db = $database->getConnection();

// Create contact object
$contact = new Contact($db);
$contact->id = $_GET['id'];

// Get contact details
if (!$contact->readOne()) {
    header('Location: contacts.php?error=Contact not found');
    exit;
}

// Handle status update
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $contact->status = $_POST['status'];
    if ($contact->updateStatus()) {
        $success_message = "Status updated successfully!";
        // Refresh contact data
        $contact->readOne();
    } else {
        $error_message = "Failed to update status.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Contact - Admin</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f7f3ed;
            color: #4a3429;
            padding: 2rem;
            line-height: 1.6;
        }
        
        .container {
            max-width: 800px;
            margin: 0 auto;
        }
        
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        
        h1 {
            color: #c4915c;
        }
        
        .back-btn {
            background: #6c757d;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
        }
        
        .back-btn:hover {
            background: #5a6268;
        }
        
        .contact-card {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .contact-info {
            display: grid;
            gap: 1.5rem;
        }
        
        .info-group {
            border-bottom: 1px solid #eee;
            padding-bottom: 1rem;
        }
        
        .info-group:last-child {
            border-bottom: none;
        }
        
        .info-label {
            font-weight: bold;
            color: #c4915c;
            margin-bottom: 0.5rem;
        }
        
        .info-value {
            background: #f8f9fa;
            padding: 0.75rem;
            border-radius: 5px;
            border-left: 4px solid #c4915c;
        }
        
        .message-content {
            min-height: 100px;
            white-space: pre-wrap;
        }
        
        .status-form {
            background: #f0e9dc;
            padding: 1.5rem;
            border-radius: 10px;
            margin-top: 2rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            font-weight: bold;
            color: #c4915c;
            margin-bottom: 0.5rem;
        }
        
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            background: white;
        }
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #c4915c;
            color: white;
        }
        
        .btn-primary:hover {
            background: #b8860b;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .status-new {
            background-color: #28a745;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .status-read {
            background-color: #007bff;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .status-replied {
            background-color: #6c757d;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            display: inline-block;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Contact Details</h1>
            <a href="contacts.php" class="back-btn">← Back to Contacts</a>
        </div>
        
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success"><?php echo $success_message; ?></div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="contact-card">
            <div class="contact-info">
                <!-- <div class="info-group">
                    <div class="info-label">Contact ID</div>
                    <div class="info-value">#<?php echo htmlspecialchars($contact->id); ?></div>
                </div> -->
                
                <div class="info-group">
                    <div class="info-label">Name</div>
                    <div class="info-value"><?php echo htmlspecialchars($contact->name); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Email</div>
                    <div class="info-value">
                        <a href="mailto:<?php echo htmlspecialchars($contact->email); ?>" style="color: #c4915c;">
                            <?php echo htmlspecialchars($contact->email); ?>
                        </a>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Message</div>
                    <div class="info-value message-content"><?php echo htmlspecialchars($contact->message); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Status</div>
                    <div class="info-value">
                        <span class="status-<?php echo $contact->status; ?>">
                            <?php echo ucfirst($contact->status); ?>
                        </span>
                    </div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">Submitted</div>
                    <div class="info-value"><?php echo date('F j, Y \a\t g:i A', strtotime($contact->submitted_at)); ?></div>
                </div>
                
                <div class="info-group">
                    <div class="info-label">IP Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($contact->ip_address); ?></div>
                </div>
            </div>
        </div>
        
        <div class="status-form">
            <h3 style="color: #c4915c; margin-bottom: 1rem;">Update Status</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="status">Change Status:</label>
                    <select name="status" id="status" required>
                        <option value="new" <?php echo ($contact->status == 'new') ? 'selected' : ''; ?>>New</option>
                        <option value="read" <?php echo ($contact->status == 'read') ? 'selected' : ''; ?>>Read</option>
                        <option value="replied" <?php echo ($contact->status == 'replied') ? 'selected' : ''; ?>>Replied</option>
                    </select>
                </div>
                <button type="submit" name="update_status" class="btn btn-primary">Update Status</button>
            </form>
        </div>
        
        <div class="actions">
            <a href="mailto:<?php echo htmlspecialchars($contact->email); ?>?subject=Re: Your Contact Form Message&body=Hi <?php echo htmlspecialchars($contact->name); ?>,%0A%0AThank you for your message. " class="btn btn-primary">
                Reply via Email
            </a>
            <a href="delete_contact.php?id=<?php echo $contact->id; ?>" class="btn btn-danger" 
               onclick="return confirm('Are you sure you want to delete this contact? This action cannot be undone.')">
                Delete Contact
            </a>
        </div>
    </div>
</body>
</html> 