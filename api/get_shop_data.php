<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once __DIR__ . '/../includes/db.php';

try {
    // Récupérer les catégories
    $categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);

    // Organiser les catégories par département
    $departments = [];
    foreach($categories as $cat) {
        $dept = $cat['department'] ?: 'Mode & Beauté';
        $departments[$dept][] = $cat;
    }

    // Récupérer les produits
    $products = $pdo->query("
        SELECT p.*, c.slug as category_slug, c.department
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        ORDER BY p.id DESC
    ")->fetchAll(PDO::FETCH_ASSOC);

    // Retourner le tout au format JSON
    echo json_encode([
        'status' => 'success',
        'data' => [
            'categories' => $categories,
            'departments' => $departments,
            'products' => $products
        ]
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
