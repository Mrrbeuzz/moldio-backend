<?php
require_once __DIR__ . '/includes/auth.php';

// Traitement de l'ajout ou la modification d'une catégorie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $name), '-'));
    $department = $_POST['department'] ?? 'Mode & Beauté';
    $id = $_POST['id'] ?? null;

    if ($name) {
        if ($id) {
            $stmt = $pdo->prepare("UPDATE categories SET name = ?, slug = ?, department = ? WHERE id = ?");
            $stmt->execute([$name, $slug, $department, $id]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO categories (name, slug, department) VALUES (?, ?, ?)");
            $stmt->execute([$name, $slug, $department]);
        }
        header("Location: categories.php");
        exit;
    }
}

// Traitement de la suppression d'une catégorie
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: categories.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestion des Catégories</h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#categoryModal" onclick="resetForm()">
        <i class="fas fa-plus"></i> Nouvelle Catégorie
    </button>
</div>

<div class="card p-3">
    <table class="table table-hover">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nom</th>
                <th>Slug</th>
                <th>Rayon</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
            <tr>
                <td><?= $cat['id'] ?></td>
                <td><?= htmlspecialchars($cat['name']) ?></td>
                <td><?= htmlspecialchars($cat['slug']) ?></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($cat['department']) ?></span></td>
                <td>
                    <button class="btn btn-sm btn-warning text-white" onclick="editCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>', '<?= htmlspecialchars(addslashes($cat['department'])) ?>')"><i class="fas fa-edit"></i></button>
                    <a href="?delete=<?= $cat['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Êtes-vous sûr de vouloir supprimer cette catégorie ?');"><i class="fas fa-trash"></i></a>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($categories)): ?>
            <tr><td colspan="4" class="text-center">Aucune catégorie trouvée.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div class="modal fade" id="categoryModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
          <div class="modal-header">
            <h5 class="modal-title" id="modalTitle">Nouvelle Catégorie</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" name="id" id="catId">
            <div class="mb-3">
                <label>Nom de la catégorie</label>
                <input type="text" name="name" id="catName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label>Rayon (Département)</label>
                <select name="department" id="catDepartment" class="form-select" required>
                    <option value="Mode & Beauté">Mode & Beauté</option>
                    <option value="Épicerie & Agro">Épicerie & Agro</option>
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
    document.getElementById('catId').value = '';
    document.getElementById('catName').value = '';
    document.getElementById('catDepartment').value = 'Mode & Beauté';
    document.getElementById('modalTitle').innerText = 'Nouvelle Catégorie';
}

function editCategory(id, name, department) {
    document.getElementById('catId').value = id;
    document.getElementById('catName').value = name;
    document.getElementById('catDepartment').value = department;
    document.getElementById('modalTitle').innerText = 'Modifier la Catégorie';
    new bootstrap.Modal(document.getElementById('categoryModal')).show();
}
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
