<?php
require_once __DIR__ . '/includes/auth.php';

// Fetch stats
$productsCount = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$categoriesCount = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// CA : Chiffre d'affaires (uniquement les commandes validées)
$caQuery = $pdo->query("SELECT SUM(total_amount) FROM orders WHERE status = 'Validée'")->fetchColumn();
$caTotal = $caQuery ? $caQuery : 0;

// Commandes en attente
$pendingOrders = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'En attente'")->fetchColumn();

// Alertes stock (produits dont le stock est <= 3)
$lowStockProducts = $pdo->query("SELECT id, title, stock FROM products WHERE stock <= 3 ORDER BY stock ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Tableau de bord</h2>
    <a href="../" target="_blank" class="btn btn-outline-secondary">Voir le site <i class="fas fa-external-link-alt ms-1"></i></a>
</div>

<!-- Statistiques Clés -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-center p-3 border-0 shadow-sm" style="border-left: 5px solid var(--bs-success) !important;">
            <p class="text-muted mb-1 text-uppercase small">Chiffre d'affaires</p>
            <h3 class="text-success mb-0"><?= number_format($caTotal, 0, ',', '.') ?> FCFA</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 border-0 shadow-sm" style="border-left: 5px solid var(--bs-warning) !important;">
            <p class="text-muted mb-1 text-uppercase small">Commandes en attente</p>
            <h3 class="text-warning mb-0"><?= $pendingOrders ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 border-0 shadow-sm" style="border-left: 5px solid var(--bs-primary) !important;">
            <p class="text-muted mb-1 text-uppercase small">Produits</p>
            <h3 class="text-primary mb-0"><?= $productsCount ?></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-center p-3 border-0 shadow-sm" style="border-left: 5px solid var(--bs-info) !important;">
            <p class="text-muted mb-1 text-uppercase small">Catégories</p>
            <h3 class="text-info mb-0"><?= $categoriesCount ?></h3>
        </div>
    </div>
</div>

<div class="row">
    <!-- Alertes Stock -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h5 class="card-title text-danger"><i class="fas fa-exclamation-triangle me-2"></i> Alertes de Stock</h5>
            </div>
            <div class="card-body">
                <?php if(count($lowStockProducts) > 0): ?>
                    <ul class="list-group list-group-flush">
                        <?php foreach($lowStockProducts as $p): ?>
                            <li class="list-group-item d-flex justify-content-between align-items-center">
                                <a href="products.php" class="text-decoration-none text-dark"><?= htmlspecialchars($p['title']) ?></a>
                                <?php if($p['stock'] == 0): ?>
                                    <span class="badge bg-danger rounded-pill">Épuisé</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark rounded-pill">Reste <?= $p['stock'] ?></span>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="text-muted text-center py-3">Tous vos produits sont bien en stock !</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Dernières Commandes (Raccourci) -->
    <div class="col-md-6">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
                <h5 class="card-title"><i class="fas fa-shopping-cart me-2"></i> Commandes Récentes</h5>
                <a href="orders.php" class="btn btn-sm btn-outline-primary">Voir tout</a>
            </div>
            <div class="card-body">
                <?php
                $recentOrders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();
                if(count($recentOrders) > 0):
                ?>
                    <div class="table-responsive">
                        <table class="table table-borderless table-hover align-middle">
                            <tbody>
                                <?php foreach($recentOrders as $ro): ?>
                                    <tr>
                                        <td><strong><?= $ro['order_ref'] ?></strong><br><small class="text-muted"><?= htmlspecialchars($ro['client_name']) ?></small></td>
                                        <td class="text-end">
                                            <?= number_format($ro['total_amount'], 0, ',', '.') ?> FCFA<br>
                                            <?php if($ro['status'] === 'En attente'): ?>
                                                <span class="badge bg-warning text-dark">En attente</span>
                                            <?php elseif($ro['status'] === 'Validée'): ?>
                                                <span class="badge bg-success">Validée</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Annulée</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-muted text-center py-3">Aucune commande pour le moment.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
