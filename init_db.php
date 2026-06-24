<?php
/**
 * ============================================================================
 * FICHIER : init_db.php
 * RÔLE    : Script d'initialisation de la base de données
 * ============================================================================
 *
 * Ce script doit être exécuté UNE SEULE FOIS lors de l'installation du site
 * (en accédant à http://localhost/moldio_universe/init_db.php dans le navigateur).
 *
 * Il effectue les opérations suivantes :
 * 1. Crée toutes les tables nécessaires au fonctionnement du site
 * 2. Insère un compte administrateur par défaut (admin / admin123)
 * 3. Insère les paramètres par défaut (numéro WhatsApp)
 *
 * SÉCURITÉ :
 * - Ce fichier doit être SUPPRIMÉ ou protégé après l'initialisation
 *   pour empêcher un visiteur de le ré-exécuter.
 * - Le mot de passe par défaut (admin123) doit être changé immédiatement
 *   après la première connexion au panneau d'administration.
 *
 * NOTE : "CREATE TABLE IF NOT EXISTS" rend le script idempotent,
 * c'est-à-dire qu'on peut le relancer sans risque : les tables existantes
 * ne seront pas écrasées et les données ne seront pas perdues.
 */

// Charge la connexion à la base de données ($pdo)
require_once __DIR__ . '/includes/db.php';

/**
 * Tableau contenant toutes les requêtes SQL de création de tables.
 * Chaque élément du tableau est une requête CREATE TABLE complète.
 */
$queries = [
    /**
     * TABLE : users
     * Stocke les comptes administrateurs du back-office.
     *
     * - id       : Identifiant unique auto-incrémenté
     * - username : Nom d'utilisateur pour la connexion
     * - password : Mot de passe haché avec password_hash() (jamais en clair !)
     * - role     : Rôle de l'utilisateur ('admin' par défaut).
     *              Prévu pour une future gestion multi-rôles
     *              (ex: 'admin', 'editor', 'viewer')
     */
    "CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        username TEXT NOT NULL,
        password TEXT NOT NULL,
        role TEXT DEFAULT 'admin'
    )",

    /**
     * TABLE : categories
     * Organise les produits par familles (ex: Vêtements, Bijoux, Chaussures).
     *
     * - id   : Identifiant unique auto-incrémenté
     * - name : Nom affiché au public (ex: "Bijoux Femme")
     * - slug : Version URL-friendly du nom (ex: "bijoux-femme"), utilisée
     *          dans les URLs pour le filtrage par catégorie.
     *          UNIQUE empêche d'avoir deux catégories avec le même slug.
     */
    "CREATE TABLE IF NOT EXISTS categories (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        slug TEXT NOT NULL UNIQUE
    )",

    /**
     * TABLE : products
     * Contient tous les articles en vente sur la boutique.
     *
     * - id             : Identifiant unique auto-incrémenté
     * - category_id    : Clé étrangère vers categories(id), lie chaque produit
     *                    à sa catégorie. NULL autorisé pour les produits
     *                    non catégorisés.
     * - title          : Nom du produit affiché aux clients
     * - description    : Description détaillée du produit (peut être NULL)
     * - price          : Prix de vente normal en euros
     * - discount_price : Prix barré/promo (0 = pas de promotion en cours).
     *                    Quand ce champ est > 0, le frontend affiche
     *                    l'ancien prix barré et le nouveau prix en rouge.
     * - image_path     : Nom du fichier image WebP (ex: "prod_667f3a1c.webp"),
     *                    stocké dans le dossier uploads/products/
     * - created_at     : Date de création automatique, utilisée pour trier
     *                    les produits du plus récent au plus ancien
     *
     * FOREIGN KEY : garantit l'intégrité référentielle. On ne peut pas
     * assigner un category_id qui n'existe pas dans la table categories.
     */
    "CREATE TABLE IF NOT EXISTS products (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        category_id INTEGER,
        title TEXT NOT NULL,
        description TEXT,
        price REAL NOT NULL,
        discount_price REAL DEFAULT 0,
        image_path TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )",

    /**
     * TABLE : coupons
     * Gère les codes promotionnels que les clients peuvent utiliser
     * lors de la commande pour bénéficier d'une réduction.
     *
     * - id             : Identifiant unique auto-incrémenté
     * - code           : Le code promo que le client saisit (ex: "SOLDES20").
     *                    UNIQUE empêche les doublons.
     * - discount_type  : Type de réduction appliquée :
     *                    'percent' → réduction en pourcentage (ex: -20%)
     *                    'fixed'   → réduction en montant fixe (ex: -5€)
     * - discount_value : Valeur numérique de la réduction (20 pour 20%, 5 pour 5€)
     * - status         : 1 = coupon actif (utilisable), 0 = coupon désactivé.
     *                    Permet de désactiver un coupon sans le supprimer,
     *                    ce qui est utile pour garder un historique.
     */
    "CREATE TABLE IF NOT EXISTS coupons (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        code TEXT NOT NULL UNIQUE,
        discount_type TEXT NOT NULL, -- 'percent' or 'fixed'
        discount_value REAL NOT NULL,
        status INTEGER DEFAULT 1 -- 1 = active, 0 = inactive
    )",

    /**
     * TABLE : settings
     * Stocke les paramètres de configuration du site sous forme
     * de paires clé/valeur (pattern "key-value store").
     *
     * - id    : Identifiant unique auto-incrémenté
     * - key   : Nom du paramètre (ex: "whatsapp_number", "site_name").
     *           UNIQUE garantit qu'un paramètre n'existe qu'une seule fois.
     * - value : Valeur du paramètre sous forme de texte
     *
     * POURQUOI ce format ?
     * - Plus flexible que des colonnes fixes : on peut ajouter de nouveaux
     *   paramètres sans modifier la structure de la table.
     * - Inconvénient : pas de typage fort (tout est TEXT), donc la validation
     *   doit être faite côté PHP.
     */
    "CREATE TABLE IF NOT EXISTS settings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        key TEXT NOT NULL UNIQUE,
        value TEXT
    )"
];

/*
 * EXÉCUTION DES REQUÊTES DE CRÉATION DES TABLES
 * On boucle sur chaque requête et on l'exécute.
 * Le try/catch intercepte toute erreur SQL (syntaxe, permissions…)
 * et arrête le script avec un message explicite.
 */
foreach ($queries as $query) {
    try {
        $pdo->exec($query);
    } catch (PDOException $e) {
        die("Erreur lors de la création de la base de données : " . $e->getMessage());
    }
}

/*
 * INSERTION DU COMPTE ADMINISTRATEUR PAR DÉFAUT
 *
 * On vérifie d'abord si un admin existe déjà pour ne pas créer de doublon
 * (important si le script est exécuté plusieurs fois par erreur).
 *
 * password_hash() avec PASSWORD_DEFAULT utilise l'algorithme bcrypt,
 * qui est actuellement le standard recommandé pour stocker les mots de passe.
 * Le hash résultant contient automatiquement un "sel" (salt) aléatoire,
 * ce qui protège contre les attaques par rainbow tables.
 *
 * ⚠️ IMPORTANT : Changer le mot de passe 'admin123' dès la mise en production !
 */
$stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE username = 'admin'");
if ($stmt->fetchColumn() == 0) {
    // Identifiants par défaut : admin / admin123
    $password = password_hash('admin123', PASSWORD_DEFAULT);
    $pdo->exec("INSERT INTO users (username, password, role) VALUES ('admin', '$password', 'admin')");
}

/*
 * INSERTION DU NUMÉRO WHATSAPP PAR DÉFAUT
 *
 * Ce numéro est utilisé par le frontend pour rediriger les commandes
 * vers WhatsApp. On vérifie d'abord s'il existe déjà pour ne pas
 * écraser un numéro personnalisé par l'admin.
 *
 * Le numéro est au format local (sans indicatif pays).
 * Le JavaScript du frontend se chargera d'ajouter l'indicatif
 * international si nécessaire.
 */
$stmt = $pdo->query("SELECT COUNT(*) FROM settings WHERE key = 'whatsapp_number'");
if ($stmt->fetchColumn() == 0) {
    $default_wa = '775866418';
    $pdo->exec("INSERT INTO settings (key, value) VALUES ('whatsapp_number', '$default_wa')");
}

// Message de confirmation affiché dans le navigateur
// Rappelle à l'utilisateur de supprimer ce script en production
// pour des raisons de sécurité
echo "Base de données initialisée avec succès. Vous pouvez maintenant supprimer ce fichier si le site est en production.";
?>
