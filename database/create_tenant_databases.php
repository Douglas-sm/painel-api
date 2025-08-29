<?php

// Hardcoded database credentials (matching .env)
$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = 'root';

// Databases to create
$databases = [
    'painel_central',
    'painel_alfa',
    'painel_beta',
    'painel_celta'
];

// Create PDO connection without specifying a database
try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL server successfully!\n";

    // Create each database
    foreach ($databases as $database) {
        try {
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "Database '$database' created successfully (or already exists).\n";
        } catch (PDOException $e) {
            echo "Error creating database '$database': " . $e->getMessage() . "\n";
        }
    }

    echo "All databases have been processed.\n";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
