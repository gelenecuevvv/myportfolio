<?php
// Include database and Contact class
include_once '../config/database.php';
include_once '../classes/Contact.php';

// Check if ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: contacts.php?error=No contact ID provided');
    exit;
}

// Database connection
$database = new Database();
$db = $database->getConnection();

// Create contact object
$contact = new Contact($db);
$contact->id = $_GET['id'];

// Get contact details first to show in confirmation
if (!$contact->readOne()) {
    header('Location: contacts.php?error=Contact not found');
    exit;
}

// Handle deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_delete'])) {
    if ($contact->delete()) {
        header('Location: contacts.php?success=Contact deleted successfully');
        exit;
    } else {
        $error_message = "Failed to delete contact. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Contact - Admin</title>
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
            max-width: 600px;
            margin: 0 auto;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        h1 {
            color: #dc3545;
            margin-bottom: 0.5rem;
        }
        
        .subtitle {
            color: #6c757d;
        }
        
        .warning-card {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .warning-icon {
            font-size: 3rem;
            color: #f39c12;
            margin-bottom: 1rem;
        }
        
        .contact-preview {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .contact-preview h3 {
            color: #c4915c;
            margin-bottom: 1rem;
        }
        
        .preview-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }
        
        .preview-item:last-child {
            border-bottom: none;
        }
        
        .preview-label {
            font-weight: bold;
            color: #6c757d;
        }
        
        .preview-value {
            color: #4a3429;
        }
        
        .message-preview {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }
        
        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
            display: inline-block;
            text-align: center;
        }
        
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c82333;
        }
        
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            .actions {
                flex-direction: column;
            }
            
            .preview-item {
                flex-direction: column;
                gap: 0.25rem;
            }
            
            .message-preview {
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>⚠️ Delete Contact</h1>
            <p class="subtitle">This action cannot be undone</p>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-error"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <div class="warning-card">
            <div class="warning-icon">⚠️</div>
            <h3>Are you sure you want to delete this contact?</h3>
            <p>This will permanently remove the contact and all associated data from the database.</p>
        </div>
        
        <div class="contact-preview">
            <h3>Contact Details</h3>
            <div class="preview-item">
                <span class="preview-label">ID:</span>
                <span class="preview-value">#<?php echo htmlspecialchars($contact->id); ?></span>
            </div>
            <div class="preview-item">
                <span class="preview-label">Name:</span>
                <span class="preview-value"><?php echo htmlspecialchars($contact->name); ?></span>
            </div>
            <div class="preview-item">
                <span class="preview-label">Email:</span>
                <span class="preview-value"><?php echo htmlspecialchars($contact->email); ?></span>
            </div>
            <div class="preview-item">
                <span class="preview-label">Message:</span>
                <span class="preview-value message-preview" title="<?php echo htmlspecialchars($contact->message); ?>">
                    <?php echo htmlspecialchars(substr($contact->message, 0, 100)) . (strlen($contact->message) > 100 ? '...' : ''); ?>
                </span>
            </div>
            <div class="preview-item">
                <span class="preview-label">Submitted:</span>
                <span class="preview-value"><?php echo date('M j, Y H:i', strtotime($contact->submitted_at)); ?></span>
            </div>
        </div>
        
        <div class="actions">
            <form method="POST" style="display: inline;">
                <button type="submit" name="confirm_delete" class="btn btn-danger">
                    🗑️ Yes, Delete Contact
                </button>
            </form>
            <a href="contacts.php" class="btn btn-secondary">
                ← Cancel & Go Back
            </a>
        </div>
    </div>
</body>
</html> 