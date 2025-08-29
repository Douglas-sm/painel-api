<?php

// Hardcoded database credentials (matching .env)
$host = '127.0.0.1';
$port = '3306';
$username = 'root';
$password = 'root';

// Tenant databases and expected data
$tenants = [
    'painel_alfa' => [
        'users' => [
            ['id' => 1, 'name' => 'João', 'email' => 'joao@alfa.com'],
            ['id' => 2, 'name' => 'Maria', 'email' => 'maria@alfa.com'],
        ],
        'products' => [
            ['id' => 1, 'name' => 'Curso Matemática', 'price' => 150.00],
            ['id' => 2, 'name' => 'Curso Português', 'price' => 180.00],
        ]
    ],
    'painel_beta' => [
        'users' => [
            ['id' => 1, 'name' => 'Pedro', 'email' => 'pedro@beta.com'],
        ],
        'products' => [
            ['id' => 1, 'name' => 'Curso Inglês', 'price' => 200.00],
        ]
    ],
    'painel_celta' => [
        'users' => [
            ['id' => 1, 'name' => 'Ana', 'email' => 'ana@celta.com'],
        ],
        'products' => [
            ['id' => 1, 'name' => 'Curso História', 'price' => 120.00],
        ]
    ]
];

// Function to check if a record exists with the expected values
function checkRecord($pdo, $table, $id, $expectedValues) {
    $sql = "SELECT * FROM $table WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$record) {
        return "No record found with ID $id in table $table";
    }

    $errors = [];
    foreach ($expectedValues as $key => $value) {
        if (!isset($record[$key]) || $record[$key] != $value) {
            $errors[] = "Expected $key to be '$value', but got '" . ($record[$key] ?? 'NULL') . "'";
        }
    }

    return empty($errors) ? null : implode(", ", $errors);
}

// Check data in each tenant database
foreach ($tenants as $database => $data) {
    echo "Checking data in $database...\n";

    try {
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$database", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Check users
        echo "  Checking users...\n";
        foreach ($data['users'] as $user) {
            $id = $user['id'];
            unset($user['id']);
            $result = checkRecord($pdo, 'users', $id, $user);
            if ($result) {
                echo "    User $id: FAILED - $result\n";
            } else {
                echo "    User $id: OK\n";
            }
        }

        // Check products
        echo "  Checking products...\n";
        foreach ($data['products'] as $product) {
            $id = $product['id'];
            unset($product['id']);
            $result = checkRecord($pdo, 'products', $id, $product);
            if ($result) {
                echo "    Product $id: FAILED - $result\n";
            } else {
                echo "    Product $id: OK\n";
            }
        }

    } catch (PDOException $e) {
        echo "  Error connecting to database: " . $e->getMessage() . "\n";
    }

    echo "\n";
}

echo "Verification completed.\n";
