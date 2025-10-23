<?php
// Database connection test for XAMPP
require_once 'vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables
Dotenv::createImmutable(__DIR__, 'config.env')->load();

try {
    $host = $_ENV['DBHOST'];
    $dbname = $_ENV['DBNAME'];
    $username = $_ENV['DBUSER'];
    $password = $_ENV['DBPWD'];
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h2>✅ Database Connection Successful!</h2>";
    echo "<p><strong>Host:</strong> $host</p>";
    echo "<p><strong>Database:</strong> $dbname</p>";
    echo "<p><strong>Username:</strong> $username</p>";
    
    // Test a simple query
    $stmt = $pdo->query("SELECT COUNT(*) as user_count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p><strong>Users in database:</strong> " . $result['user_count'] . "</p>";
    
    echo "<p><a href='index.php'>Go to Application</a></p>";
    
} catch (PDOException $e) {
    echo "<h2>❌ Database Connection Failed!</h2>";
    echo "<p><strong>Error:</strong> " . $e->getMessage() . "</p>";
    echo "<h3>Please check:</h3>";
    echo "<ul>";
    echo "<li>XAMPP MySQL service is running</li>";
    echo "<li>Database 'db_elementary_school_pedro' exists</li>";
    echo "<li>Database credentials in config.env are correct</li>";
    echo "<li>Database schema has been imported</li>";
    echo "</ul>";
}
?>
