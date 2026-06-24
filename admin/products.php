<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Traitement du formulaire d'ajout ou de modification d'un produit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? null;
    $title = $_POST['title'] ?? '';
    $category_id = $_POST['category_id'] ?? null;
    $price = $_POST['price'] ?? 0;
    $discount_price = $_POST['discount_price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $description = $_POST['description'] ?? '';

    $image_path = $_POST['current_image'] ?? null;

    // Gestion de l'upload d'image
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/../uploads/products';
        $new_image = handleImageUpload($_FILES['image'], $uploadDir);
        if ($new_image) {
            // Suppression de l'ancienne image si une nouvelle est téléchargée
            if ($image_path && file_exists($uploadDir . '/' . $image_path)) {
                unlink($uploadDir . '/' . $image_path);
            }
            $image_path = $new_image;
        }
    }

    if ($title && $price > 0) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE products SET category_id=?, title=?, description=?, price=?, discount_price=?, stock=?, image_path=? WHERE id=?");
            $stmt->execute([$category_id, $title, $description, $price, $discount_price, $stock, $image_path, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO products (category_id, title, description, price, discount_price, stock, image_path) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$category_id, $title, $description, $price, $discount_price, $stock, $image_path]);
        }
        header("Location: products.php");
        exit;
    }
}

// Traitement de la suppression d'un produit
if (isset($_GET['delete'])) {
    // Récupération de l'image associée au produit pour la supprimer du serveur
    $stmt = $pdo->prepare("SELECT image_path FROM products WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    $prod = $stmt->fetch();
    if ($prod && $prod['image_path']) {
        $file = __DIR__ . '/../uploads/products/' . $prod['image_path'];
        if (file_exists($file)) unlink($file);
    }
    
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: products.php");
    exit;
}

// Récupération des catégories pour alimenter le menu déroulant du formulaire
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
$products = $pdo->query("
    SELECT p.*, c.name as category_name 
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.id 
    ORDER BY p.id DESC
")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Produits</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#productModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> Nouveau Produit
    </button>
</div>

<div class="card p-3">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>Image</th>
                <th>Titre</th>
                <th>Catégorie</th>
                <th>Prix</th>
                <th>Stock</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
            <tr>
                <td>
                    <?php if($p['image_path']): ?>
                        <img src="../uploads/products/<?= $p['image_path'] ?>" alt="Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;">
                    <?php else: ?>
                        <div style="width: 50px; height: 50px; background:#eee; border-radius: 5px;"></div>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($p['title']) ?></td>
                <td><?= htmlspecialchars($p['category_name']) ?></td>
                <td>
                    <?= number_format($p['price'], 0, ',', '.') ?> FCFA
                    <?php if($p['discount_price'] > 0): ?>
                        <br><small class="text-danger">Promo: <?= number_format($p['discount_price'], 0, ',', '.') ?> FCFA</small>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($p['stock'] <= 3 && $p['stock'] > 0): ?>
                        <span class="badge bg-warning text-dark"><?= $p['stock'] ?> (Faible)</span>
                    <?php elseif($p['stock'] == 0): ?>
                        <span class="badge bg-danger">Épuisé</span>
                    <?php else: ?>
                        <span class="badge bg-success"><?= $p['stock'] ?></span>
                    <?php endif; ?>
                </td>
                <td>
                    <button class="btn btn-sm btn-warning text-white" onclick='editProduct(<?= json_encode($p) ?>)'><i class="fas fa-edit"></i></button>
                    <a href="?delete=<?= $p['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ce produit ?');"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($products)): ?>
            <tr><td colspan="5" class="text-center">Aucun produit trouvé.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="productModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <form method="POST" enctype="multipart/form-data">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Nouveau Produit</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="prodId">
            <input type="hidden" name="current_image" id="currentImage">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label>Titre</label>
                    <input type="text" name="title" id="prodTitle" class="form-control" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label>Catégorie</label>
                    <select name="category_id" id="prodCategory" class="form-select" required>
                        <option value="">Sélectionner</option>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Prix (FCFA)</label>
                    <input type="number" step="1" name="price" id="prodPrice" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label>Prix Promo (FCFA) (Optionnel)</label>
                    <input type="number" step="1" name="discount_price" id="prodDiscount" class="form-control" value="0">
                </div>
                <div class="col-md-4 mb-3">
                    <label>Stock</label>
                    <input type="number" step="1" name="stock" id="prodStock" class="form-control" value="0" required>
                </div>
                <div class="col-12 mb-3">
                    <label>Description</label>
                    <textarea name="description" id="prodDesc" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-12 mb-3">
                    <label>Image (sera convertie en WebP)</label>
                    <input type="file" name="image" class="form-control" accept="image/jpeg, image/png, image/webp">
                </div>
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
    document.getElementById('prodId').value = '';
    document.getElementById('currentImage').value = '';
    document.getElementById('prodTitle').value = '';
    document.getElementById('prodCategory').value = '';
    document.getElementById('prodPrice').value = '';
    document.getElementById('prodDiscount').value = '0';
    document.getElementById('prodDesc').value = '';
    document.getElementById('modalTitle').innerText = 'Nouveau Produit';
}

function editProduct(product) {
    document.getElementById('prodId').value = product.id;
    document.getElementById('currentImage').value = product.image_path;
    document.getElementById('prodTitle').value = product.title;
    document.getElementById('prodCategory').value = product.category_id || '';
    document.getElementById('prodPrice').value = product.price;
    document.getElementById('prodDiscount').value = product.discount_price;
    document.getElementById('prodStock').value = product.stock || 0;
    document.getElementById('prodDesc').value = product.description;
    document.getElementById('modalTitle').innerText = 'Modifier le Produit';
    new bootstrap.Modal(document.getElementById('productModal')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
