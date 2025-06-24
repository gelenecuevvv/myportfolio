<?php
echo "<h2>Portfolio Database Setup</h2>";

// Database configuration
$host = 'localhost';
$username = 'root';  // Default XAMPP username
$password = '';      // Default XAMPP password (empty)

try {
    // Create database connection
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<p>✅ Connected to MySQL server successfully!</p>";
    
    // Create database
    $pdo->exec("CREATE DATABASE IF NOT EXISTS portfolio_db");
    echo "<p>✅ Database 'portfolio_db' created successfully!</p>";
    
    // Select the database
    $pdo->exec("USE portfolio_db");
    
    // Create contacts table
    $sql = "CREATE TABLE IF NOT EXISTS contacts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        ip_address VARCHAR(45),
        status ENUM('new', 'read', 'replied') DEFAULT 'new'
    )";
    
    $pdo->exec($sql);
    echo "<p>✅ Table 'contacts' created successfully!</p>";
    
    // Create indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_email ON contacts(email)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_submitted_at ON contacts(submitted_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_status ON contacts(status)");
    
    echo "<p>✅ Database indexes created successfully!</p>";
    
    echo "<h3>🎉 Database setup completed!</h3>";
    echo "<p>Your portfolio contact form is now ready to use.</p>";
    echo "<p><a href='index.html'>Go to Portfolio</a> | <a href='admin/contacts.php'>View Admin Panel</a></p>";
    
} catch(PDOException $e) {
    echo "<p>❌ Error: " . $e->getMessage() . "</p>";
    echo "<p>Please make sure XAMPP is running and MySQL is started.</p>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background-color: #f7f3ed;
    color: #4a3429;
}

h2 {
    color: #c4915c;
    text-align: center;
    margin-bottom: 30px;
}

h3 {
    color: #c4915c;
    margin-top: 30px;
}

p {
    margin: 10px 0;
    padding: 10px;
    background-color: #f0e9dc;
    border-radius: 5px;
}

a {
    color: #c4915c;
    text-decoration: none;
    font-weight: bold;
}

a:hover {
    text-decoration: underline;
}
</style> 