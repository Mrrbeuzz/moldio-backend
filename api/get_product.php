<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

require_once __DIR__ . '/../includes/db.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Product ID is required']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        SELECT p.*, c.name as category_name, c.slug as category_slug 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.id = ?
    ");
    $stmt->execute([$id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product) {
        // Also fetch related products
        $catId = $product['category_id'];
        $relatedStmt = $pdo->prepare("
            SELECT * FROM products 
            WHERE category_id = ? AND id != ? 
            ORDER BY id DESC LIMIT 4
        ");
        $relatedStmt->execute([$catId, $id]);
        $related = $relatedStmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'product' => $product,
                'related' => $related
            ]
        ]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Product not found']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
