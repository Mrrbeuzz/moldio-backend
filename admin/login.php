<?php
/**
 * =============================================================================
 * FICHIER : login.php — Page de connexion à l'administration
 * =============================================================================
 * 
 * RÔLE : Cette page permet à l'administrateur de s'identifier pour accéder
 *        au back-office. C'est la seule page admin accessible sans être connecté.
 * 
 * FLUX DE FONCTIONNEMENT :
 *   1. L'admin arrive sur la page → le formulaire de connexion s'affiche
 *   2. Il saisit son nom d'utilisateur et son mot de passe
 *   3. Le formulaire est soumis en POST (les données ne passent pas dans l'URL)
 *   4. Le serveur vérifie les identifiants dans la base de données :
 *      a. Recherche de l'utilisateur par son nom d'utilisateur
 *      b. Vérification du mot de passe avec password_verify() (hash bcrypt)
 *   5. Si OK → création d'une session et redirection vers le tableau de bord
 *   6. Si KO → affichage d'un message d'erreur
 * 
 * SÉCURITÉ :
 *   - Le mot de passe est stocké HASHÉ dans la base (jamais en clair)
 *   - password_verify() compare le mot de passe saisi au hash stocké
 *   - Pas de distinction entre "utilisateur inconnu" et "mauvais mot de passe"
 *     dans le message d'erreur (empêche l'énumération des utilisateurs)
 *   - Le formulaire utilise la méthode POST (pas de mot de passe dans l'URL)
 * =============================================================================
 */

// Démarrage de la session PHP — nécessaire pour stocker l'état de connexion
session_start();

// Chargement de la connexion à la base de données (variable $pdo)
// On remonte d'un niveau (../) car login.php est dans admin/ et db.php dans includes/
require_once __DIR__ . '/../includes/db.php';

// Variable pour stocker un éventuel message d'erreur à afficher à l'utilisateur
$error = '';

/*
 * ==========================================================================
 * TRAITEMENT DU FORMULAIRE DE CONNEXION (uniquement si soumis en POST)
 * ==========================================================================
 * $_SERVER['REQUEST_METHOD'] permet de distinguer :
 * - 'GET'  → l'utilisateur arrive sur la page (affichage du formulaire)
 * - 'POST' → l'utilisateur a cliqué sur "Se connecter" (traitement des données)
 */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupération des données du formulaire avec l'opérateur null coalescent (??)
    // Si la clé n'existe pas dans $_POST, on utilise une chaîne vide par défaut
    // Cela évite les erreurs "Undefined index" si le champ est absent
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    /*
     * Recherche de l'utilisateur dans la base de données :
     * - On utilise une requête préparée (prepare + execute) pour se protéger
     *   contre les injections SQL. Le "?" est un paramètre de substitution
     *   qui sera échappé automatiquement par PDO.
     * - On ne cherche PAS le mot de passe dans la requête SQL car il est hashé
     *   et la comparaison doit être faite côté PHP avec password_verify()
     */
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(); // Récupère la ligne de l'utilisateur (ou false si inexistant)

    /*
     * Vérification des identifiants :
     * 1. $user → vérifie que l'utilisateur existe (fetch() retourne false sinon)
     * 2. password_verify() → compare le mot de passe en clair saisi par l'admin
     *    avec le hash bcrypt stocké en base de données.
     *    Cette fonction gère automatiquement le sel (salt) et l'algorithme.
     * 
     * IMPORTANT : On ne fait JAMAIS password_hash() du mot de passe saisi pour
     * le comparer au hash stocké, car chaque appel à password_hash() génère un
     * hash différent (sel aléatoire). password_verify() est conçu pour ça.
     */
    if ($user && password_verify($password, $user['password'])) {
        // Connexion réussie : on stocke le flag de connexion dans la session
        // C'est cette variable qui sera vérifiée par auth.php sur chaque page
        $_SESSION['admin_logged_in'] = true;

        // Redirection vers le tableau de bord (page d'accueil du back-office)
        header('Location: index.php');
        exit; // Arrêt immédiat du script après la redirection
    } else {
        // Identifiants incorrects : message d'erreur générique
        // On ne précise PAS si c'est le nom d'utilisateur ou le mot de passe
        // qui est faux, pour des raisons de sécurité (anti-énumération)
        $error = "Identifiants incorrects.";
    }
}
?>

<!--
=============================================================================
PARTIE HTML — Interface visuelle de la page de connexion
=============================================================================
Cette page est autonome : elle ne charge PAS header.php ni footer.php
car elle a son propre design (centrée, sans sidebar) distinct du back-office.
=============================================================================
-->
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion Administration</title>

    <!--
    Styles CSS intégrés directement dans la page (pas de fichier externe)
    car cette page est unique et son style n'est pas réutilisé ailleurs.
    -->
    <style>
        /* --- Page de connexion ---
           Utilisation de Flexbox pour centrer parfaitement le formulaire
           au milieu de l'écran (horizontalement ET verticalement).
           height: 100vh → la page occupe toute la hauteur de l'écran.
           background-color: #fce4ec → rose très pâle (couleur de la marque) */
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #fce4ec; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }

        /* --- Boîte de connexion ---
           - border-radius: 15px → coins très arrondis pour un look moderne
           - box-shadow → ombre rose subtile pour donner de la profondeur
           - max-width: 400px → limite la largeur sur grands écrans */
        .login-box { background: white; padding: 40px; border-radius: 15px; box-shadow: 0 10px 25px rgba(233, 30, 99, 0.1); width: 100%; max-width: 400px; text-align: center; }

        /* --- Titre de la page de connexion ---
           Couleur rose foncé (#d81b60) cohérente avec la charte graphique
           font-weight: 300 → texte fin/léger pour un style épuré */
        h1 { color: #d81b60; margin-bottom: 30px; font-weight: 300; }

        /* --- Champs de saisie ---
           - box-sizing: border-box → le padding est inclus dans la largeur (100%)
             ce qui évite que le champ dépasse de son conteneur
           - outline: none → supprime le contour bleu par défaut du navigateur
           - transition → animation fluide du changement de couleur de bordure */
        input { width: 100%; padding: 12px; margin: 10px 0; border: 1px solid #f8bbd0; border-radius: 8px; box-sizing: border-box; outline: none; transition: border-color 0.3s; }

        /* Quand le champ est en focus (cliqué/actif), la bordure devient rose foncé
           pour indiquer visuellement quel champ est sélectionné */
        input:focus { border-color: #d81b60; }

        /* --- Bouton de connexion ---
           Style cohérent avec les couleurs de la marque (rose)
           cursor: pointer → le curseur se transforme en main au survol */
        button { width: 100%; padding: 12px; background: #d81b60; color: white; border: none; border-radius: 8px; cursor: pointer; font-size: 16px; margin-top: 20px; transition: background 0.3s; }

        /* Effet de survol : couleur plus foncée pour le feedback visuel */
        button:hover { background: #c2185b; }

        /* --- Message d'erreur --- Texte rouge pour attirer l'attention */
        .error { color: red; margin-bottom: 15px; }
    </style>
</head>
<body>
    <!-- Boîte de connexion centrée sur l'écran -->
    <div class="login-box">
        <h1>Admin Login</h1>

        <!-- Affichage conditionnel du message d'erreur :
             Le message n'apparaît que si $error contient une chaîne non vide.
             htmlspecialchars() est utilisé pour échapper
             les caractères spéciaux et prévenir les attaques XSS. -->
        <?php if($error): ?><div class="error"><?= $error ?></div><?php endif; ?>

        <!-- Formulaire de connexion :
             - method="POST" → les données sont envoyées dans le corps de la requête
               (pas visibles dans l'URL, contrairement à GET)
             - action non spécifié → le formulaire est soumis à la même page (login.php)
             - Les attributs "required" empêchent la soumission si un champ est vide
               (validation côté client, mais la validation serveur reste nécessaire) -->
        <form method="POST">
            <!-- Champ nom d'utilisateur — attribut "required" pour la validation HTML5 -->
            <input type="text" name="username" placeholder="Nom d'utilisateur" required>

            <!-- Champ mot de passe — type="password" masque les caractères saisis -->
            <input type="password" name="password" placeholder="Mot de passe" required>

            <!-- Bouton de soumission du formulaire -->
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>
</html>
