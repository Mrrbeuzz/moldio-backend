<?php
/**
 * save_order.php
 * Reçoit les données de la commande via AJAX (fetch) en JSON.
 * Enregistre la commande dans la base de données avec le statut "En attente".
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
require_once __DIR__ . '/includes/db.php';

// Lire le JSON envoyé
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data) {
    echo json_encode(['success' => false, 'error' => 'Données invalides']);
    exit;
}

$client_name = $data['client_name'] ?? '';
$client_phone = $data['client_phone'] ?? '';
$client_address = $data['client_address'] ?? '';
$total_amount = $data['total_amount'] ?? 0;
$discount_amount = $data['discount_amount'] ?? 0;
$items = $data['items'] ?? [];

if (empty($client_name) || empty($client_phone) || empty($items)) {
    echo json_encode(['success' => false, 'error' => 'Champs obligatoires manquants']);
    exit;
}

try {
    // Commencer une transaction
    $pdo->beginTransaction();

    // 1. Générer une référence unique (ex: CMD-5B3A)
    $order_ref = 'CMD-' . strtoupper(substr(uniqid(), -4));

    // 2. Insérer la commande principale
    $stmt = $pdo->prepare("INSERT INTO orders (order_ref, client_name, client_phone, client_address, total_amount, discount_amount, status) VALUES (?, ?, ?, ?, ?, ?, 'En attente')");
    $stmt->execute([$order_ref, $client_name, $client_phone, $client_address, $total_amount, $discount_amount]);
    $order_id = $pdo->lastInsertId();

    // 3. Insérer les articles de la commande
    $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_title, quantity, price) VALUES (?, ?, ?, ?, ?)");
    foreach ($items as $item) {
        $stmtItem->execute([
            $order_id,
            $item['id'],
            $item['title'],
            $item['qty'],
            $item['price']
        ]);
    }

    // Valider la transaction
    $pdo->commit();

    echo json_encode(['success' => true, 'order_ref' => $order_ref]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
