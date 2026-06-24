<?php
/**
 * ============================================================================
 * FICHIER : get_whatsapp.php
 * RÔLE    : Point d'entrée AJAX pour récupérer le numéro WhatsApp du vendeur
 * ============================================================================
 *
 * Ce fichier est appelé par JavaScript (main.js) via une requête AJAX
 * lorsque l'utilisateur clique sur le bouton "Commander" dans le panier.
 *
 * FLUX DE FONCTIONNEMENT :
 * 1. Le JavaScript envoie une requête GET à ce fichier
 * 2. Ce script lit le numéro WhatsApp stocké dans la table "settings"
 * 3. Le numéro est renvoyé en texte brut (pas en JSON car c'est une valeur simple)
 * 4. Le JavaScript utilise ce numéro pour construire l'URL WhatsApp
 *    (ex: https://wa.me/221775866418?text=...) et y rediriger le client
 *
 * POURQUOI stocker le numéro en BDD plutôt qu'en dur dans le code ?
 * - L'administrateur peut le modifier depuis le back-office sans toucher
 *   au code source ni redéployer le site.
 * - Si le vendeur change de numéro, la modification est instantanée
 *   pour tous les visiteurs du site.
 */

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: *');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

// Charge la connexion à la base de données ($pdo)
require_once __DIR__ . '/includes/db.php';

// Requête préparée pour récupérer le numéro WhatsApp.
// On utilise prepare() même pour une requête sans paramètre utilisateur
// par bonne pratique et cohérence avec le reste du code.
// La clé 'whatsapp_number' correspond à l'entrée créée par init_db.php.
$stmt = $pdo->prepare("SELECT value FROM settings WHERE key = 'whatsapp_number'");
$stmt->execute();

// fetch() retourne la ligne trouvée ou false si aucun résultat
$setting = $stmt->fetch();

// Si le paramètre existe en BDD, on affiche sa valeur.
// Sinon, on retourne le numéro par défaut '775866418' comme filet de sécurité.
// Cette valeur de fallback évite une erreur si la table settings est vide
// (par exemple si init_db.php n'a pas été exécuté ou si quelqu'un a
// accidentellement supprimé l'entrée).
echo $setting ? $setting['value'] : '775866418';
?>
