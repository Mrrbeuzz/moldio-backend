<?php
require_once __DIR__ . '/includes/auth.php';

// Traitement de l'ajout ou la modification d'un code promo
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = strtoupper(trim($_POST['code'] ?? ''));
    $discount_type = $_POST['discount_type'] ?? 'percent';
    $discount_value = $_POST['discount_value'] ?? 0;
    $status = $_POST['status'] ?? 1;
    $id = $_POST['id'] ?? null;

    if ($code && $discount_value > 0) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE coupons SET code=?, discount_type=?, discount_value=?, status=? WHERE id=?");
            $stmt->execute([$code, $discount_type, $discount_value, $status, $id]);
        } else {
            // Vérifier si le code existe déjà pour éviter les doublons
            $stmt = $pdo->prepare("SELECT id FROM coupons WHERE code = ?");
            $stmt->execute([$code]);
            if (!$stmt->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO coupons (code, discount_type, discount_value, status) VALUES (?, ?, ?, ?)");
                $stmt->execute([$code, $discount_type, $discount_value, $status]);
            }
        }
        header("Location: coupons.php");
        exit;
    }
}

// Traitement de la suppression d'un code promo
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM coupons WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: coupons.php");
    exit;
}

$coupons = $pdo->query("SELECT * FROM coupons ORDER BY id DESC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Codes Promo</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#couponModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> Nouveau Code
    </button>
</div>

<div class="card p-3">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Code</th>
                <th>Réduction</th>
                <th>Statut</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($coupons as $c): ?>
            <tr>
                <td><strong><?= htmlspecialchars($c['code']) ?></strong></td>
                <td>
                    <?= $c['discount_value'] ?> 
                    <?= $c['discount_type'] === 'percent' ? '%' : 'FCFA' ?>
                </td>
                <td>
                    <?php if($c['status'] == 1): ?>
                        <span class="badge bg-success">Actif</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Inactif</span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-warning text-white" onclick='editCoupon(<?= json_encode($c) ?>)'><i class="fas fa-edit"></i></button>
                    <a href="?delete=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce code promo ?');"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($coupons)): ?>
            <tr><td colspan="4" class="text-center">Aucun code promo trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="couponModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Nouveau Code Promo</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="coupId">
            
            <div class="mb-3">
                <label>Code</label>
                <input type="text" name="code" id="coupCode" class="form-control" required style="text-transform: uppercase;">
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Type de réduction</label>
                    <select name="discount_type" id="coupType" class="form-select" required>
                        <option value="percent">Pourcentage (%)</option>
                        <option value="fixed">Montant fixe (FCFA)</option>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Valeur</label>
                    <input type="number" step="0.01" name="discount_value" id="coupVal" class="form-control" required>
                </div>
            </div>
            <div class="mb-3">
                <label>Statut</label>
                <select name="status" id="coupStatus" class="form-select">
                    <option value="1">Actif</option>
                    <option value="0">Inactif</option>
                </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
            <button type="submit" class="btn btn-primary">Enregistrer</button>
          </div>
      </form>
    </div>
  </div>
</div>

<script>
function resetForm() {
    document.getElementById('coupId').value = '';
    document.getElementById('coupCode').value = '';
    document.getElementById('coupType').value = 'percent';
    document.getElementById('coupVal').value = '';
    document.getElementById('coupStatus').value = '1';
    document.getElementById('modalTitle').innerText = 'Nouveau Code Promo';
}

function editCoupon(coupon) {
    document.getElementById('coupId').value = coupon.id;
    document.getElementById('coupCode').value = coupon.code;
    document.getElementById('coupType').value = coupon.discount_type;
    document.getElementById('coupVal').value = coupon.discount_value;
    document.getElementById('coupStatus').value = coupon.status;
    document.getElementById('modalTitle').innerText = 'Modifier le Code Promo';
    new bootstrap.Modal(document.getElementById('couponModal')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
