<!--
=============================================================================
FICHIER : header.php — En-tête et barre de navigation latérale du back-office
=============================================================================

RÔLE : Ce fichier est inclus en haut de CHAQUE page du back-office admin.
       Il fournit la structure HTML commune à toutes les pages :
       - La déclaration DOCTYPE et les balises <head> (CSS, titre, meta)
       - La barre latérale (sidebar) de navigation avec les liens vers
         chaque section de l'administration
       - L'ouverture de la zone de contenu principal (<div class="content">)

ARCHITECTURE : Le back-office utilise un layout à 2 colonnes :
       - Colonne gauche fixe (250px) : sidebar de navigation
       - Colonne droite fluide : contenu de la page (formulaires, tableaux...)

       Chaque page admin suit ce schéma :
         1. require header.php  → ouvre la structure HTML + sidebar
         2. Contenu spécifique   → formulaires, tableaux, etc.
         3. require footer.php  → ferme les balises HTML + charge le JS

DÉPENDANCES EXTERNES :
       - Bootstrap 5.3.0 (CDN) : framework CSS pour le design responsive
       - Font Awesome 6.0.0 (CDN) : bibliothèque d'icônes vectorielles
=============================================================================
-->

<!DOCTYPE html>
<html lang="fr">
<head>
    <!-- Encodage UTF-8 pour supporter les caractères spéciaux français (é, è, ç, etc.) -->
    <meta charset="UTF-8">

    <!-- Balise viewport : indispensable pour que le site s'adapte aux écrans mobiles -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Titre affiché dans l'onglet du navigateur -->
    <title>Administration</title>

    <!-- Bootstrap 5 CSS : framework qui fournit les classes utilitaires (btn, card, row, col, etc.)
         Chargé depuis un CDN (Content Delivery Network) pour des performances optimales -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome : bibliothèque d'icônes (fas fa-box, fas fa-tags, etc.)
         Permet d'afficher des icônes sans avoir besoin d'images -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">

    <!--
    =========================================================================
    STYLES CSS PERSONNALISÉS DU BACK-OFFICE
    =========================================================================
    Ces styles s'ajoutent à Bootstrap pour créer l'apparence spécifique
    de l'interface d'administration Moldio Universe.
    -->
    <style>
        /* --- Style global du body ---
           Police Segoe UI (Windows) avec fallback sur d'autres polices système
           Fond gris très clair (#f8f9fa) pour distinguer visuellement le back-office du site public */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8f9fa; }

        /* --- Sidebar (barre de navigation latérale) ---
           - height: 100vh → occupe toute la hauteur de l'écran (viewport height)
           - position: fixed → reste visible même quand on scrolle la page
           - width: 250px → largeur fixe de la sidebar
           - background-color: #343a40 → gris foncé (couleur Bootstrap "dark") */
        .sidebar { height: 100vh; background-color: #343a40; color: white; padding-top: 20px; position: fixed; width: 250px; }

        /* --- Liens de navigation dans la sidebar ---
           - color: #cfd8dc → gris clair pour un bon contraste sur fond sombre
           - padding: 15px 20px → espace intérieur pour agrandir la zone cliquable
           - display: block → chaque lien occupe toute la largeur (un par ligne)
           - transition: 0.3s → animation fluide au survol (hover) */
        .sidebar a { color: #cfd8dc; text-decoration: none; padding: 15px 20px; display: block; transition: 0.3s; }

        /* --- Effet au survol des liens ---
           - Fond légèrement plus clair pour indiquer l'interactivité
           - Bordure rose (#e83e8c) à gauche : accent visuel de la marque Moldio */
        .sidebar a:hover { background-color: #495057; color: white; border-left: 4px solid #e83e8c; }

        /* --- Lien actif (page actuellement affichée) ---
           Même style que le hover pour indiquer la page courante */
        .sidebar .active { background-color: #495057; border-left: 4px solid #e83e8c; color: white; }

        /* --- Zone de contenu principal ---
           margin-left: 250px → décalé à droite pour ne pas être caché derrière la sidebar fixe
           padding: 30px → espace intérieur pour aérer le contenu */
        .content { margin-left: 250px; padding: 30px; }

        /* --- Style des cartes (cards) ---
           - border: none → supprime la bordure par défaut de Bootstrap
           - border-radius: 10px → coins arrondis pour un look moderne
           - box-shadow → ombre légère pour un effet de profondeur (élévation) */
        .card { border: none; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }

        /* --- Boutons primaires personnalisés ---
           On remplace le bleu par défaut de Bootstrap par le rose (#e83e8c)
           pour rester cohérent avec l'identité visuelle de la marque Moldio */
        .btn-primary { background-color: #e83e8c; border-color: #e83e8c; }
        .btn-primary:hover { background-color: #d81b60; border-color: #d81b60; }
    </style>
</head>
<body>

<!--
=========================================================================
SIDEBAR — Barre de navigation latérale fixe
=========================================================================
Contient les liens vers toutes les sections du back-office.
Chaque lien est composé d'une icône Font Awesome + un libellé texte.
La classe "me-2" (margin-end 2) de Bootstrap ajoute un espace entre
l'icône et le texte.
-->
<div class="sidebar">
    <!-- Titre du back-office, centré en haut de la sidebar -->
    <h4 class="text-center mb-4">Back-Office</h4>

    <!-- Lien vers le tableau de bord (page d'accueil admin) -->
    <a href="index.php"><i class="fas fa-tachometer-alt me-2"></i> Tableau de bord</a>

    <!-- Lien vers la gestion des commandes -->
    <a href="orders.php"><i class="fas fa-shopping-cart me-2"></i> Commandes</a>

    <!-- Lien vers la gestion des catégories de produits -->
    <a href="categories.php"><i class="fas fa-tags me-2"></i> Catégories</a>

    <!-- Lien vers la gestion des produits (ajout, modification, suppression) -->
    <a href="products.php"><i class="fas fa-box me-2"></i> Produits</a>

    <!-- Lien vers la gestion des codes promotionnels -->
    <a href="coupons.php"><i class="fas fa-ticket-alt me-2"></i> Codes Promo</a>

    <!-- Lien vers les paramètres du site (numéro WhatsApp, etc.) -->
    <a href="settings.php"><i class="fas fa-cog me-2"></i> Paramètres</a>

    <!-- Lien de déconnexion — en rouge (text-danger) pour signaler l'action irréversible
         mt-5 ajoute un espacement vers le bas pour le séparer visuellement des autres liens -->
    <a href="logout.php" class="text-danger mt-5"><i class="fas fa-sign-out-alt me-2"></i> Déconnexion</a>
</div>

<!--
=========================================================================
ZONE DE CONTENU PRINCIPAL
=========================================================================
Cette div est ouverte ici et sera fermée dans footer.php.
Tout le contenu spécifique à chaque page (tableaux, formulaires, cartes)
sera inséré entre ce header.php et le footer.php.
-->
<div class="content">
