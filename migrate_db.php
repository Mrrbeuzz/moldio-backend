<?php
require_once __DIR__ . '/includes/db.php';

try {
    // Add stock column to products
    $pdo->exec("ALTER TABLE products ADD COLUMN stock INTEGER DEFAULT 0");
    echo "Added stock column to products.\n";
} catch (Exception $e) {
    // Column might already exist
    echo "Column stock might already exist: " . $e->getMessage() . "\n";
}

try {
    // Create orders table
    $pdo->exec("CREATE TABLE IF NOT EXISTS orders (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_ref TEXT NOT NULL,
        client_name TEXT NOT NULL,
        client_phone TEXT NOT NULL,
        client_address TEXT NOT NULL,
        total_amount REAL NOT NULL,
        discount_amount REAL DEFAULT 0,
        status TEXT DEFAULT 'En attente',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Created orders table.\n";
} catch (Exception $e) {
    echo "Error creating orders table: " . $e->getMessage() . "\n";
}

try {
    // Create order_items table
    $pdo->exec("CREATE TABLE IF NOT EXISTS order_items (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        order_id INTEGER NOT NULL,
        product_id INTEGER NOT NULL,
        product_title TEXT NOT NULL,
        quantity INTEGER NOT NULL,
        price REAL NOT NULL,
        FOREIGN KEY(order_id) REFERENCES orders(id),
        FOREIGN KEY(product_id) REFERENCES products(id)
    )");
    echo "Created order_items table.\n";
} catch (Exception $e) {
    echo "Error creating order_items table: " . $e->getMessage() . "\n";
}
?>
