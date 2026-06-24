<!-- ==========================================================================
     PANIER LATÉRAL (Offcanvas Bootstrap)
     ==========================================================================
     Ce panneau glisse depuis la droite de l'écran quand l'utilisateur clique
     sur l'icône panier dans la navbar (header.php).

     POURQUOI un offcanvas plutôt qu'une page panier séparée ?
     - L'utilisateur peut consulter son panier sans quitter la page en cours.
     - Expérience utilisateur plus fluide et moderne, similaire à ce qu'on
       retrouve sur les grandes boutiques en ligne.
     - Le contenu du panier (#cartItems) est injecté dynamiquement par
       JavaScript (main.js) à partir du localStorage du navigateur.
     ========================================================================== -->
<div class="offcanvas offcanvas-end" tabindex="-1" id="cartOffcanvas" aria-labelledby="cartOffcanvasLabel">
  <!-- En-tête du panier avec titre et bouton de fermeture -->
  <div class="offcanvas-header border-bottom">
    <h5 class="offcanvas-title" id="cartOffcanvasLabel"><i class="fas fa-shopping-bag me-2"></i> Mon Panier</h5>
    <!-- btn-close : bouton "X" standard de Bootstrap pour fermer le panneau -->
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>

  <!-- Corps du panier : utilise flexbox pour pousser le récapitulatif en bas -->
  <div class="offcanvas-body d-flex flex-column">

    <!-- Zone scrollable où JavaScript injectera la liste des articles.
         flex-grow-1 permet à cette zone de prendre tout l'espace disponible
         tandis que overflow-auto ajoute une scrollbar si le contenu dépasse. -->
    <div id="cartItems" class="flex-grow-1 overflow-auto">
        <!-- Les articles du panier seront injectés ici par JavaScript (main.js) -->
    </div>
    
    <!-- =======================================================================
         ZONE DE RÉCAPITULATIF ET COMMANDE
         =======================================================================
         mt-auto pousse cette section en bas du panneau offcanvas grâce à flexbox,
         quel que soit le nombre d'articles dans le panier. -->
    <div class="mt-auto border-top pt-3">

        <!-- Champ de saisie du code promo.
             L'utilisateur entre un code et clique "Appliquer".
             La fonction applyPromo() (définie dans main.js) envoie une requête
             AJAX à check_promo.php pour vérifier la validité du code. -->
        <div class="input-group mb-3">
            <input type="text" id="promoCode" class="form-control" placeholder="Code promo">
            <button class="btn btn-outline-secondary" type="button" onclick="applyPromo()">Appliquer</button>
        </div>

        <!-- Zone où s'affichera le message de retour du code promo
             (ex: "Code valide ! -10%", ou "Code invalide").
             Le contenu est injecté par JavaScript. -->
        <div id="promoMessage" class="small mb-2"></div>
        
        <!-- Sous-total : prix avant réduction, mis à jour par JavaScript -->
        <div class="d-flex justify-content-between mb-2">
            <span>Sous-total:</span>
            <span id="cartSubtotal">0.00 €</span>
        </div>

        <!-- Ligne de réduction : masquée par défaut (display:none !important).
             Elle n'apparaît que si un code promo valide est appliqué.
             JavaScript retire le style inline pour la rendre visible. -->
        <div class="d-flex justify-content-between mb-2 text-danger" id="discountRow" style="display:none !important;">
            <span>Réduction:</span>
            <span id="cartDiscount">-0.00 €</span>
        </div>

        <!-- Total final : prix après réduction éventuelle -->
        <div class="d-flex justify-content-between mb-3 fw-bold fs-5">
            <span>Total:</span>
            <span id="cartTotal">0.00 €</span>
        </div>
        
        <!-- Bouton de commande via WhatsApp.
             Au clic, la fonction checkoutWhatsApp() (main.js) :
             1. Récupère le numéro WhatsApp du vendeur via get_whatsapp.php
             2. Compose un message récapitulatif de la commande
             3. Ouvre WhatsApp Web/App avec le message pré-rempli
             C'est une alternative simple aux systèmes de paiement en ligne,
             adaptée aux marchés où WhatsApp est le canal de vente principal. -->
        <button class="btn btn-primary-custom w-100" onclick="checkoutWhatsApp()">
            <i class="fab fa-whatsapp me-2"></i> Commander
        </button>
    </div>
  </div>
</div>

<!-- ==========================================================================
     PIED DE PAGE (Footer)
     ==========================================================================
     Contient les informations de la marque, les liens de navigation secondaires
     et les icônes de réseaux sociaux. Structuré avec la grille Bootstrap
     en 3 colonnes sur desktop (col-md-4), empilées sur mobile. -->
<footer>
    <div class="container">
        <div class="row">
            <!-- Colonne 1 : Description de la marque -->
            <div class="col-md-4 mb-4">
                <h5>MOLDIO UNIVERSE</h5>
                <p>Découvrez notre collection exclusive de vêtements, bijoux et chaussures. L'élégance à portée de clic.</p>
            </div>

            <!-- Colonne 2 : Liens de navigation rapide (doublons de la navbar
                 pour faciliter l'accès depuis le bas de page) -->
            <div class="col-md-4 mb-4">
                <h5>Liens Utiles</h5>
                <ul class="list-unstyled">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="#">À propos</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>

            <!-- Colonne 3 : Icônes des réseaux sociaux.
                 Les href="#" sont des placeholders à remplacer par les vraies
                 URLs des profils sociaux de la marque. -->
            <div class="col-md-4 mb-4">
                <h5>Suivez-nous</h5>
                <a href="#" class="me-3 fs-4"><i class="fab fa-instagram"></i></a>
                <a href="#" class="me-3 fs-4"><i class="fab fa-facebook"></i></a>
                <a href="#" class="me-3 fs-4"><i class="fab fa-pinterest"></i></a>
            </div>
        </div>

        <!-- Ligne de copyright avec l'année générée dynamiquement.
             date('Y') retourne l'année en cours (ex: 2026), ce qui évite
             de devoir mettre à jour manuellement chaque année. -->
        <div class="text-center mt-4 pt-3 border-top border-secondary">
            <small>&copy; <?= date('Y') ?> Moldio Universe. Tous droits réservés.</small>
        </div>
    </div>
</footer>

<!-- ==========================================================================
     MODAL DE VALIDATION DE COMMANDE (Checkout)
     ========================================================================== -->
<div class="modal fade" id="checkoutModal" tabindex="-1" aria-labelledby="checkoutModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="checkoutModalLabel">Informations de livraison</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="checkoutForm">
            <div class="mb-3">
                <label class="form-label">Nom Complet *</label>
                <input type="text" id="clientName" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Téléphone (WhatsApp) *</label>
                <input type="tel" id="clientPhone" class="form-control" placeholder="ex: 771234567" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Adresse de livraison *</label>
                <textarea id="clientAddress" class="form-control" rows="2" required></textarea>
            </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
        <button type="button" class="btn btn-primary-custom" onclick="submitOrder()">Valider la commande</button>
      </div>
    </div>
  </div>
</div>

<!-- ==========================================================================
     SCRIPTS JAVASCRIPT
     ==========================================================================
     Les scripts sont placés en fin de page (avant </body>) pour deux raisons :
     1. Le HTML se charge et s'affiche avant que les scripts ne soient téléchargés,
        ce qui améliore le temps de chargement perçu par l'utilisateur.
     2. Le DOM est entièrement construit quand les scripts s'exécutent,
        donc pas besoin d'attendre DOMContentLoaded pour manipuler les éléments.
     ========================================================================== -->

<!-- Bootstrap JS Bundle : inclut Popper.js (nécessaire pour les dropdowns
     et tooltips) + les composants interactifs (offcanvas, collapse, etc.) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- JavaScript personnalisé du site : gestion du panier, codes promo,
     commande WhatsApp, et toutes les interactions dynamiques -->
<script src="assets/js/main.js?v=<?= time() ?>"></script>
</body>
</html>
