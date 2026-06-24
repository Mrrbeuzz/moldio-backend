<?php
/**
 * ============================================================================
 * FICHIER : header.php
 * RÔLE    : En-tête commun à toutes les pages du site frontend
 * ============================================================================
 *
 * Ce fichier est inclus en haut de chaque page publique du site (index.php,
 * product.php, etc.) via require_once ou include.
 *
 * Il contient :
 * 1. L'initialisation de la session PHP
 * 2. Le chargement des catégories depuis la BDD (pour le menu dynamique)
 * 3. La structure HTML du <head> (CSS, meta tags)
 * 4. La barre de navigation responsive avec Bootstrap 5
 *
 * POURQUOI un fichier header séparé ?
 * - Principe DRY (Don't Repeat Yourself) : on écrit le menu une seule fois
 *   et il est automatiquement identique sur toutes les pages.
 * - Si on ajoute une catégorie en BDD, le menu se met à jour partout
 *   sans toucher au code HTML.
 */

// Démarre la session PHP uniquement si elle n'est pas déjà active.
// La vérification avec PHP_SESSION_NONE évite l'erreur
// "session already started" si un autre fichier a déjà appelé session_start().
// La session est nécessaire pour stocker le panier, l'authentification, etc.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inclut la connexion à la base de données (le fichier db.php crée l'objet $pdo)
require_once __DIR__ . '/db.php';

// Récupère toutes les catégories triées par nom alphabétique
// pour les afficher dans le menu déroulant "Collection".
// fetchAll() retourne un tableau de toutes les catégories d'un coup.
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- Meta viewport : indispensable pour le responsive design sur mobile.
         Sans cette balise, les smartphones afficheraient la version desktop
         en miniature au lieu de s'adapter à la largeur de l'écran. -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Boutique</title>

    <!-- Bootstrap 5 CSS : framework CSS qui fournit la grille responsive,
         les composants (navbar, dropdown, offcanvas…) et les classes utilitaires
         (d-flex, mb-3, etc.) utilisées dans tout le site. -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome 6 : bibliothèque d'icônes vectorielles.
         Utilisée pour les icônes du panier (fa-shopping-bag),
         des réseaux sociaux (fa-instagram, fa-whatsapp), etc. -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!-- CSS personnalisé du site.
         Le paramètre ?v=<?= time() ?> ajoute un timestamp à l'URL
         pour forcer le navigateur à recharger le fichier CSS à chaque visite
         (cache busting). En production, on utiliserait plutôt un numéro
         de version fixe pour profiter du cache navigateur. -->
    <link href="assets/css/style.css?v=<?= time() ?>" rel="stylesheet">
</head>
<body>

<!-- ==========================================================================
     BARRE DE NAVIGATION (Navbar)
     ==========================================================================
     - sticky-top : la navbar reste collée en haut de l'écran au scroll,
       pour que l'utilisateur puisse toujours naviguer facilement.
     - navbar-expand-lg : le menu est affiché en entier sur les écrans larges
       (≥992px) et se replie en menu hamburger sur mobile/tablette.
     ========================================================================== -->
<nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
        <!-- Logo cliquable qui ramène à la page d'accueil -->
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/logo.png" alt="MOLDIO UNIVERSE" height="50">
        </a>

        <!-- Bouton hamburger : visible uniquement sur mobile/tablette.
             data-bs-toggle="collapse" et data-bs-target="#navbarNav"
             indiquent à Bootstrap quel élément afficher/masquer au clic. -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Zone repliable du menu : masquée sur mobile, visible sur desktop -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <!-- mx-auto centre les liens de navigation horizontalement -->
            <ul class="navbar-nav mx-auto">
                <!-- Lien vers la page d'accueil -->
                <li class="nav-item">
                    <a class="nav-link" href="index.php">Accueil</a>
                </li>

                <!-- Menu déroulant "Rayons" avec les catégories dynamiques groupées par département. -->
                <?php
                // Grouper les catégories par département
                $departments = [];
                foreach($categories as $cat) {
                    $dept = $cat['department'] ?: 'Mode & Beauté';
                    $departments[$dept][] = $cat;
                }
                ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="index.php#shop" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Rayons
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item" href="index.php#shop">Tous les rayons</a></li>
                        <li><hr class="dropdown-divider"></li>

                        <?php foreach($departments as $deptName => $deptCats): ?>
                            <li><h6 class="dropdown-header fw-bold text-primary"><?= htmlspecialchars($deptName) ?></h6></li>
                            <?php foreach($deptCats as $cat): ?>
                                <li><a class="dropdown-item" href="index.php?category=<?= $cat['slug'] ?>#shop"><?= htmlspecialchars($cat['name']) ?></a></li>
                            <?php endforeach; ?>
                            <li><hr class="dropdown-divider"></li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <!-- Liens statiques (pages futures) -->
                <li class="nav-item">
                    <a class="nav-link" href="#">A propos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Contact</a>
                </li>
            </ul>

            <!-- Icône du panier positionnée à droite de la navbar.
                 Au clic, elle ouvre le panneau latéral (offcanvas) du panier
                 défini dans footer.php. Le badge (cart-count) affiche le nombre
                 d'articles et est mis à jour dynamiquement par JavaScript. -->
            <div class="d-flex align-items-center">
                <a href="#" class="cart-icon" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas">
                    <i class="fas fa-shopping-bag"></i>
                    <span class="cart-count" id="cartCount">0</span>
                </a>
            </div>
        </div>
    </div>
</nav>
