<?php
// Include database and Contact class
include_once '../config/database.php';
include_once '../classes/Contact.php';

// Database connection
$database = new Database();
$db = $database->getConnection();

// Create contact object
$contact = new Contact($db);

// Get all contacts
$stmt = $contact->read();
$contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Form Submissions - Admin</title>
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
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        h1 {
            color: #c4915c;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #f0e9dc;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }
        
        .stat-card h3 {
            color: #c4915c;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .contacts-table {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        th, td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background-color: #c4915c;
            color: white;
            font-weight: bold;
        }
        
        tr:hover {
            background-color: #f9f9f9;
        }
        
        .status-new {
            background-color: #28a745;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        
        .status-read {
            background-color: #007bff;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        
        .status-replied {
            background-color: #6c757d;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        
        .message-preview {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .btn {
            padding: 0.25rem 0.5rem;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            text-decoration: none;
            font-size: 0.8rem;
        }
        
        .btn-primary {
            background-color: #007bff;
            color: white;
        }
        
        .btn-danger {
            background-color: #dc3545;
            color: white;
        }
        
        .no-contacts {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            
            table {
                font-size: 0.8rem;
            }
            
            th, td {
                padding: 0.5rem;
            }
            
            .message-preview {
                max-width: 100px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Contact Form Submissions</h1>
        
        <?php
        // Display success/error messages
        if (isset($_GET['success'])) {
            echo '<div style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 5px; margin-bottom: 2rem; border: 1px solid #c3e6cb;">';
            echo htmlspecialchars($_GET['success']);
            echo '</div>';
        }
        
        if (isset($_GET['error'])) {
            echo '<div style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 5px; margin-bottom: 2rem; border: 1px solid #f5c6cb;">';
            echo htmlspecialchars($_GET['error']);
            echo '</div>';
        }
        ?>
        
        <?php
        // Calculate stats
        $total_contacts = count($contacts);
        $new_contacts = count(array_filter($contacts, function($c) { return $c['status'] == 'new'; }));
        $read_contacts = count(array_filter($contacts, function($c) { return $c['status'] == 'read'; }));
        $replied_contacts = count(array_filter($contacts, function($c) { return $c['status'] == 'replied'; }));
        ?>
        
        <div class="stats">
            <div class="stat-card">
                <h3><?php echo $total_contacts; ?></h3>
                <p>Total Contacts</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $new_contacts; ?></h3>
                <p>New Messages</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $read_contacts; ?></h3>
                <p>Read Messages</p>
            </div>
            <div class="stat-card">
                <h3><?php echo $replied_contacts; ?></h3>
                <p>Replied Messages</p>
            </div>
        </div>
        
        <div class="contacts-table">
            <?php if ($total_contacts > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <!-- <th>ID</th> -->
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>IP</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($contacts as $contact_item): ?>
                            <tr>
                                <!-- <td><?php echo htmlspecialchars($contact_item['id']); ?></td> -->
                                <td><?php echo htmlspecialchars($contact_item['name']); ?></td>
                                <td><?php echo htmlspecialchars($contact_item['email']); ?></td>
                                <td class="message-preview" title="<?php echo htmlspecialchars($contact_item['message']); ?>">
                                    <?php echo htmlspecialchars(substr($contact_item['message'], 0, 50)) . '...'; ?>
                                </td>
                                <td>
                                    <span class="status-<?php echo $contact_item['status']; ?>">
                                        <?php echo ucfirst($contact_item['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y H:i', strtotime($contact_item['submitted_at'])); ?></td>
                                <td><?php echo htmlspecialchars($contact_item['ip_address']); ?></td>
                                <td class="actions">
                                    <a href="view_contact.php?id=<?php echo $contact_item['id']; ?>" class="btn btn-primary">View</a>
                                    <a href="delete_contact.php?id=<?php echo $contact_item['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this contact?')">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="no-contacts">
                    <h3>No contact submissions yet</h3>
                    <p>Contact form submissions will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html> 