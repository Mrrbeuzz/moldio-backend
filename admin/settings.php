<?php
require_once __DIR__ . '/includes/auth.php';

// Traitement de la mise à jour des paramètres
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $wa_number = $_POST['whatsapp_number'] ?? '';
    
    // Nettoyer le numéro (ne garder que les chiffres)
    $wa_number = preg_replace('/[^0-9]/', '', $wa_number);
    
    if ($wa_number) {
        $stmt = $pdo->prepare("UPDATE settings SET value = ? WHERE key = 'whatsapp_number'");
        $stmt->execute([$wa_number]);
        $success = "Paramètres mis à jour avec succès.";
    }
}

// Récupérer les paramètres actuels depuis la base de données
$stmt = $pdo->prepare("SELECT value FROM settings WHERE key = 'whatsapp_number'");
$stmt->execute();
$wa_setting = $stmt->fetch();
$current_wa = $wa_setting ? $wa_setting['value'] : '775866418';

require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Paramètres du Site</h2>
</div>

<?php if(isset($success)): ?>
<div class="alert alert-success"><?= $success ?></div>
<?php endif; ?>

<div class="card p-4">
    <form method="POST">
        <h4 class="mb-3">Informations de Contact</h4>
        <div class="mb-3">
            <label class="form-label">Numéro WhatsApp (pour recevoir les commandes)</label>
            <div class="input-group">
                <span class="input-group-text"><i class="fab fa-whatsapp"></i></span>
                <input type="text" name="whatsapp_number" class="form-control" value="<?= htmlspecialchars($current_wa) ?>" placeholder="Ex: 775866418" required>
            </div>
            <div class="form-text">Ce numéro sera utilisé pour générer le lien de commande direct.</div>
        </div>
        
        <button type="submit" class="btn btn-primary mt-3">Enregistrer les modifications</button>
    </form>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
