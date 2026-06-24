<?php
/**
 * =============================================================================
 * FICHIER : logout.php — Déconnexion de l'administrateur
 * =============================================================================
 * 
 * RÔLE : Ce fichier gère la déconnexion de l'administrateur du back-office.
 *        Il est appelé quand l'admin clique sur le lien "Déconnexion" dans
 *        la sidebar.
 * 
 * FLUX DE FONCTIONNEMENT :
 *   1. Démarre la session existante (obligatoire pour pouvoir la détruire)
 *   2. Détruit complètement la session (supprime toutes les données)
 *   3. Redirige vers la page de connexion
 * 
 * SÉCURITÉ :
 *   - session_destroy() supprime TOUTES les données de session côté serveur
 *   - Après cette opération, la variable $_SESSION['admin_logged_in']
 *     n'existe plus, donc auth.php bloquera l'accès à toutes les pages
 *   - L'admin devra se reconnecter pour accéder au back-office
 * 
 * NOTE : session_start() DOIT être appelé avant session_destroy(),
 *        sinon PHP ne sait pas quelle session détruire (il n'a pas
 *        encore lu le cookie de session du navigateur).
 * =============================================================================
 */

// Étape 1 : Reprendre la session existante
// Cela permet à PHP de récupérer l'identifiant de session depuis le cookie
// du navigateur et de charger les données associées côté serveur
session_start();

// Étape 2 : Détruire la session
// - Supprime le fichier de session sur le serveur
// - Vide le tableau $_SESSION en mémoire
// - Le cookie de session dans le navigateur devient invalide
// (il pointe vers une session qui n'existe plus)
session_destroy();

// Étape 3 : Rediriger vers la page de connexion
// L'administrateur verra le formulaire de login et devra se réidentifier
header('Location: login.php');

// Étape 4 : Arrêter l'exécution du script
// Comme pour toute redirection, exit empêche l'exécution de code
// supplémentaire après l'envoi de l'en-tête de redirection
exit;
?>
