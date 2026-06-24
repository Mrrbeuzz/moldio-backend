<!--
=============================================================================
FICHIER : footer.php — Pied de page du back-office administrateur
=============================================================================

RÔLE : Ce fichier ferme la structure HTML ouverte par header.php.
       Il est inclus en bas de CHAQUE page du back-office.

RESPONSABILITÉS :
  1. Fermer la div "content" ouverte dans header.php
  2. Charger le JavaScript de Bootstrap (nécessaire pour les modals,
     les tooltips, les dropdowns et autres composants interactifs)
  3. Fermer les balises </body> et </html>

POURQUOI CHARGER LE JS EN BAS DE PAGE ?
  - Placer les scripts en bas du <body> (et non dans le <head>) permet
    au navigateur de charger et afficher le HTML/CSS en premier.
  - L'utilisateur voit la page plus rapidement car le JavaScript ne
    bloque pas le rendu visuel initial.
  - Les éléments du DOM sont déjà disponibles quand le script s'exécute,
    ce qui évite les erreurs de type "element not found".
=============================================================================
-->

<!-- Fermeture de la div "content" ouverte dans header.php
     Cette div englobe tout le contenu spécifique de chaque page admin -->
</div> <!-- End content -->

<!--
Bootstrap Bundle JS (inclut Popper.js) :
- Popper.js est nécessaire pour le positionnement des tooltips et des popups
- Le "bundle" combine Bootstrap JS + Popper.js en un seul fichier
- Ce script active les composants interactifs utilisés dans le back-office :
  * Les fenêtres modales (ajout/modification de produits, catégories, coupons)
  * Les menus déroulants (dropdowns)
  * Les accordéons et onglets
  * Les infobulles (tooltips) et popovers
-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- Fermeture des balises HTML principales -->
</body>
</html>
