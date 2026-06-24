<?php
require_once __DIR__ . '/includes/auth.php';

// Handle Order Actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $action = $_GET['action'];
    $order_id = $_GET['id'];

    if ($action === 'validate') {
        try {
            $pdo->beginTransaction();

            // Check order status
            $stmt = $pdo->prepare("SELECT status FROM orders WHERE id = ?");
            $stmt->execute([$order_id]);
            $order = $stmt->fetch();

            if ($order && $order['status'] === 'En attente') {
                // Update status
                $stmt = $pdo->prepare("UPDATE orders SET status = 'Validée' WHERE id = ?");
                $stmt->execute([$order_id]);

                // Deduct stock
                $stmtItems = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
                $stmtItems->execute([$order_id]);
                $items = $stmtItems->fetchAll();

                $stmtUpdateStock = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                foreach ($items as $item) {
                    $stmtUpdateStock->execute([$item['quantity'], $item['product_id']]);
                }
            }

            $pdo->commit();
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Erreur lors de la validation : " . $e->getMessage();
        }
    } elseif ($action === 'cancel') {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'Annulée' WHERE id = ? AND status = 'En attente'");
        $stmt->execute([$order_id]);
    }
    
    header("Location: orders.php");
    exit;
}

// Fetch orders
$orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Commandes</h2>
</div>

<?php if(isset($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Réf</th>
                        <th>Client</th>
                        <th>Date</th>
                        <th>Total (FCFA)</th>
                        <th>Statut</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($orders as $o): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($o['order_ref']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($o['client_name']) ?><br>
                                <small class="text-muted"><i class="fab fa-whatsapp"></i> <?= htmlspecialchars($o['client_phone']) ?></small>
                            </td>
                            <td><?= date('d/m/Y H:i', strtotime($o['created_at'])) ?></td>
                            <td><strong><?= number_format($o['total_amount'], 0, ',', '.') ?></strong></td>
                            <td>
                                <?php if($o['status'] === 'En attente'): ?>
                                    <span class="badge bg-warning text-dark"><i class="fas fa-clock"></i> En attente</span>
                                <?php elseif($o['status'] === 'Validée'): ?>
                                    <span class="badge bg-success"><i class="fas fa-check"></i> Validée</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="fas fa-times"></i> Annulée</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info text-white" data-bs-toggle="modal" data-bs-target="#orderModal<?= $o['id'] ?>">
                                    <i class="fas fa-eye"></i> Détails
                                </button>
                                <?php if($o['status'] === 'En attente'): ?>
                                    <a href="?action=validate&id=<?= $o['id'] ?>" class="btn btn-sm btn-success" onclick="return confirm('Valider cette commande ? Le stock sera déduit.');">
                                        <i class="fas fa-check"></i> Valider
                                    </a>
                                    <a href="?action=cancel&id=<?= $o['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Annuler cette commande ?');">
                                        <i class="fas fa-times"></i> Annuler
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal Détails -->
                        <div class="modal fade" id="orderModal<?= $o['id'] ?>" tabindex="-1">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <h5 class="modal-title">Détails Commande <?= htmlspecialchars($o['order_ref']) ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                              </div>
                              <div class="modal-body">
                                <h6>Informations Client</h6>
                                <p><strong>Nom :</strong> <?= htmlspecialchars($o['client_name']) ?><br>
                                <strong>Téléphone :</strong> <?= htmlspecialchars($o['client_phone']) ?><br>
                                <strong>Adresse :</strong> <?= nl2br(htmlspecialchars($o['client_address'])) ?></p>
                                
                                <h6 class="mt-4">Articles</h6>
                                <?php
                                $stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
                                $stmtItems->execute([$o['id']]);
                                $items = $stmtItems->fetchAll();
                                ?>
                                <ul class="list-group mb-3">
                                    <?php foreach($items as $item): ?>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            <?= $item['quantity'] ?>x <?= htmlspecialchars($item['product_title']) ?>
                                            <span><?= number_format($item['price'] * $item['quantity'], 0, ',', '.') ?> FCFA</span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                                <?php if($o['discount_amount'] > 0): ?>
                                    <div class="text-end text-danger mb-2">Réduction: -<?= number_format($o['discount_amount'], 0, ',', '.') ?> FCFA</div>
                                <?php endif; ?>
                                <h5 class="text-end">Total: <?= number_format($o['total_amount'], 0, ',', '.') ?> FCFA</h5>
                              </div>
                            </div>
                          </div>
                        </div>

                    <?php endforeach; ?>
                    <?php if(empty($orders)): ?>
                        <tr><td colspan="6" class="text-center py-4">Aucune commande pour le moment.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
