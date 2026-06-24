<?php
/**
 * ============================================================================
 * FICHIER : db.php
 * RÔLE    : Connexion à la base de données SQLite via PDO
 * ============================================================================
 *
 * Ce fichier est inclus par tous les scripts du projet qui ont besoin
 * d'accéder à la base de données. Il crée un objet PDO ($pdo) connecté
 * à un fichier SQLite local.
 *
 * POURQUOI SQLite ?
 * - Pas besoin d'installer un serveur MySQL ou PostgreSQL séparé.
 * - La base de données est un simple fichier stocké dans le même dossier,
 *   ce qui facilite le déploiement et les sauvegardes.
 * - Idéal pour un petit site e-commerce avec un trafic modéré.
 *
 * POURQUOI PDO ?
 * - PDO (PHP Data Objects) est une couche d'abstraction qui permet
 *   de changer de SGBD (MySQL, PostgreSQL…) sans réécrire les requêtes.
 * - Il supporte les requêtes préparées, essentielles pour se protéger
 *   contre les injections SQL.
 */

// Chemin absolu vers le fichier SQLite
// __DIR__ retourne le dossier du fichier actuel (includes/), donc
// la base de données sera créée dans includes/database.sqlite
$db_file = __DIR__ . '/database.sqlite';

try {
    // Création de la connexion PDO vers la base SQLite
    // Le préfixe "sqlite:" indique à PDO quel driver utiliser
    $pdo = new PDO("sqlite:" . $db_file);

    // Active le mode d'erreur par exceptions :
    // Sans cette ligne, les erreurs SQL échouent silencieusement.
    // Avec ERRMODE_EXCEPTION, chaque erreur SQL lève une exception PHP,
    // ce qui facilite le débogage et empêche d'ignorer les problèmes.
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Définit le mode de récupération par défaut en tableau associatif :
    // Chaque ligne retournée par fetch() sera un tableau avec les noms
    // de colonnes comme clés (ex: $row['name']) au lieu d'indices numériques.
    // Cela rend le code plus lisible et plus facile à maintenir.
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // En cas d'échec de connexion (fichier inaccessible, permissions…),
    // on arrête immédiatement le script avec un message d'erreur explicite.
    // die() empêche le reste du code de s'exécuter avec une connexion invalide.
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
?>
