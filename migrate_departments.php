<?php
/**
 * Script de migration pour ajouter la colonne "department" à la table "categories".
 */
require_once __DIR__ . '/includes/db.php';

try {
    $pdo->exec("ALTER TABLE categories ADD COLUMN department TEXT DEFAULT 'Mode & Beauté'");
    echo "Colonne 'department' ajoutée avec succès à la table 'categories'.\n";
} catch (Exception $e) {
    echo "Erreur ou la colonne existe déjà : " . $e->getMessage() . "\n";
}
