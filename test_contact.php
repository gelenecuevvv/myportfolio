<?php
echo "<h2>Testing Contact Form Submission</h2>";

// Simulate a POST request
$_POST['name'] = 'Test User';
$_POST['email'] = 'test@example.com';
$_POST['message'] = 'This is a test message to verify the contact form is working correctly.';

echo "<p>Simulating form submission with:</p>";
echo "<pre>";
print_r($_POST);
echo "</pre>";

// Include the contact submission script
echo "<h3>Result:</h3>";
ob_start();
include 'submit_contact.php';
$result = ob_get_clean();

echo "<pre>$result</pre>";

// Also test database connection separately
echo "<h3>Database Test:</h3>";
try {
    include_once 'config/database.php';
    $database = new Database();
    $db = $database->getConnection();
    
    if($db) {
        echo "<p>✅ Database connection successful!</p>";
        
        // Test reading contacts
        include_once 'classes/Contact.php';
        $contact = new Contact($db);
        $stmt = $contact->read();
        $contacts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<p>📊 Total contacts in database: " . count($contacts) . "</p>";
        
        if(count($contacts) > 0) {
            echo "<h4>Latest contact:</h4>";
            echo "<pre>";
            print_r($contacts[0]);
            echo "</pre>";
        }
    } else {
        echo "<p>❌ Database connection failed!</p>";
    }
} catch(Exception $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
}
?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f7f3ed; }
h2, h3 { color: #c4915c; }
pre { background: #f0e9dc; padding: 10px; border-radius: 5px; }
p { margin: 10px 0; }
</style> 