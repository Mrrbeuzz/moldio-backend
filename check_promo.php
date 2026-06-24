<?php
/**
 * ============================================================================
 * FICHIER : check_promo.php
 * RÔLE    : Point d'entrée AJAX pour vérifier la validité d'un code promo
 * ============================================================================
 *
 * Ce fichier est appelé par JavaScript (main.js) via une requête AJAX POST
 * quand l'utilisateur clique sur "Appliquer" dans le panier.
 *
 * FLUX DE FONCTIONNEMENT :
 * 1. Le frontend envoie le code promo saisi par l'utilisateur (POST)
 * 2. Ce script cherche le code dans la table "coupons" de la BDD
 * 3. Si le code existe ET est actif (status = 1), on renvoie ses détails
 *    (type de réduction, valeur) en JSON
 * 4. Si le code n'existe pas ou est désactivé, on renvoie {valid: false}
 * 5. Le JavaScript applique la réduction côté client sur le montant du panier
 *
 * POURQUOI vérifier côté serveur ?
 * - Si la vérification était uniquement côté client, un utilisateur
 *   malveillant pourrait modifier le JavaScript pour s'inventer des
 *   réductions. La vérification serveur garantit que seuls les codes
 *   réellement enregistrés en BDD sont acceptés.
 *
 * NOTE : La commande finale passant par WhatsApp, il n'y a pas de
 * paiement en ligne automatique. Le vendeur vérifiera manuellement
 * le code promo lors du traitement de la commande.
 */

// Charge la connexion à la base de données ($pdo)
require_once __DIR__ . '/includes/db.php';

// Indique au navigateur que la réponse est au format JSON.
// Sans cet en-tête, le navigateur pourrait interpréter la réponse
// comme du HTML, ce qui causerait des erreurs de parsing côté JavaScript.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}
// Récupère le code promo envoyé par le frontend via POST.
// L'opérateur ?? (null coalescing) retourne '' si $_POST['code']
// n'est pas défini, évitant ainsi un warning PHP.
$code = $_POST['code'] ?? '';

// Si aucun code n'a été saisi, on retourne immédiatement "invalide"
// pour éviter une requête SQL inutile
if (!$code) {
    echo json_encode(['valid' => false]);
    exit; // Arrête le script ici, le code suivant ne s'exécute pas
}

// Recherche le code promo dans la table "coupons".
// REQUÊTE PRÉPARÉE (prepare + execute) : le point d'interrogation (?)
// est un placeholder. La valeur réelle ($code) est passée séparément
// via execute(). Cela empêche les injections SQL car PDO échappe
// automatiquement les caractères dangereux (', ", --, etc.).
// La condition "status = 1" exclut les coupons désactivés par l'admin.
$stmt = $pdo->prepare("SELECT * FROM coupons WHERE code = ? AND status = 1");
$stmt->execute([$code]);

// fetch() récupère une seule ligne de résultat (ou false si rien trouvé)
$coupon = $stmt->fetch();

if ($coupon) {
    // Le code promo est valide et actif → on renvoie ses informations
    // au frontend pour qu'il puisse calculer la réduction :
    // - 'valid' : true indique au JavaScript que le code est accepté
    // - 'code'  : le code tel qu'enregistré en BDD (pour l'affichage)
    // - 'type'  : 'percent' ou 'fixed', détermine comment calculer la réduction
    // - 'value' : la valeur numérique (ex: 20 pour -20% ou 5 pour -5€)
    //   Le cast (float) garantit un type numérique dans le JSON
    echo json_encode([
        'valid' => true,
        'code' => $coupon['code'],
        'type' => $coupon['discount_type'],
        'value' => (float)$coupon['discount_value']
    ]);
} else {
    // Le code promo n'existe pas en BDD OU il est désactivé (status = 0)
    // On ne distingue pas les deux cas volontairement, pour ne pas
    // révéler à un utilisateur malveillant quels codes existent.
    echo json_encode(['valid' => false]);
}
?>
