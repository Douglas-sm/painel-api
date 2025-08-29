<?php

// Hardcoded database credentials (matching .env)
$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = 'root';

// Databases to reset
$databases = [
    'painel_alfa',
    'painel_beta',
    'painel_celta'
];

// Create PDO connection without specifying a database
try {
    $pdo = new PDO("mysql:host=$host;port=$port", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected to MySQL server successfully!\n";

    // Drop and recreate each database
    foreach ($databases as $database) {
        try {
            $pdo->exec("DROP DATABASE IF EXISTS `$database`");
            echo "Database '$database' dropped successfully.\n";

            $pdo->exec("CREATE DATABASE `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            echo "Database '$database' created successfully.\n";
        } catch (PDOException $e) {
            echo "Error processing database '$database': " . $e->getMessage() . "\n";
        }
    }

    echo "All tenant databases have been reset.\n";

} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
