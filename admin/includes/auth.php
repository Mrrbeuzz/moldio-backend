<?php
/**
 * =============================================================================
 * FICHIER : auth.php — Garde d'authentification du back-office administrateur
 * =============================================================================
 * 
 * RÔLE : Ce fichier agit comme un "gardien" (middleware) qui protège toutes
 *        les pages du back-office. Il est inclus en haut de chaque page admin
 *        pour s'assurer que seul un administrateur connecté peut y accéder.
 * 
 * FONCTIONNEMENT :
 *   1. Démarre la session PHP (nécessaire pour lire les variables de session)
 *   2. Vérifie si l'administrateur est connecté via la variable de session
 *   3. Si non connecté → redirige vers la page de connexion
 *   4. Si connecté → charge la connexion à la base de données pour que
 *      les pages admin puissent faire des requêtes SQL
 * 
 * SÉCURITÉ : Sans ce fichier, n'importe qui pourrait accéder aux pages admin
 *            simplement en tapant l'URL dans le navigateur. Ce fichier empêche
 *            cela en forçant une redirection vers login.php.
 * =============================================================================
 */

// Démarrage de la session PHP — indispensable pour accéder à $_SESSION
// qui contient les informations de connexion de l'administrateur
session_start();

/*
 * Vérification de l'authentification :
 * - On vérifie que la clé 'admin_logged_in' existe dans la session
 * - ET qu'elle vaut exactement true (comparaison stricte avec !==)
 * - La comparaison stricte (!== au lieu de !=) empêche les faux positifs :
 *   par exemple, une chaîne "1" ou un entier 1 ne passerait pas le test
 * - Si l'une des deux conditions échoue → l'utilisateur n'est pas connecté
 */
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirection HTTP vers la page de connexion
    // header() envoie un en-tête HTTP 302 (redirection temporaire) au navigateur
    header('Location: login.php');

    // exit est CRUCIAL ici : sans lui, le code PHP continuerait à s'exécuter
    // après la redirection, ce qui pourrait exposer des données sensibles
    // pendant le bref instant avant que le navigateur ne suive la redirection
    exit;
}

/*
 * Chargement de la connexion à la base de données :
 * - __DIR__ représente le dossier où se trouve CE fichier (admin/includes/)
 * - On remonte de 2 niveaux (../../) pour atteindre la racine du projet
 * - Le fichier db.php contient la configuration PDO et la variable $pdo
 * - require_once garantit que le fichier n'est chargé qu'une seule fois,
 *   même si auth.php est inclus plusieurs fois (évite les erreurs de
 *   redéclaration de la connexion)
 */
require_once __DIR__ . '/../../includes/db.php';
?>
