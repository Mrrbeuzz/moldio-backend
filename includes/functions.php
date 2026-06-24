<?php
/**
 * ============================================================================
 * FICHIER : functions.php
 * RÔLE    : Fonctions utilitaires réutilisables dans tout le projet
 * ============================================================================
 *
 * Ce fichier contient les fonctions "helper" partagées entre les différentes
 * pages du site. Pour l'instant, il ne contient qu'une seule fonction dédiée
 * au traitement des images uploadées.
 *
 * POURQUOI un fichier séparé pour les fonctions ?
 * - Évite la duplication de code : la même fonction peut être appelée
 *   depuis l'admin (ajout de produit) ou d'autres scripts.
 * - Facilite la maintenance : un seul endroit à modifier si on veut
 *   changer la logique de traitement des images.
 */

/**
 * handleImageUpload()
 * -------------------
 * Gère l'upload d'une image envoyée via un formulaire HTML, la convertit
 * au format WebP et la sauvegarde dans le dossier spécifié.
 *
 * POURQUOI convertir en WebP ?
 * - Le format WebP offre une compression supérieure au JPEG et au PNG
 *   (environ 25-35% plus léger), ce qui accélère le chargement des pages.
 * - Tous les navigateurs modernes supportent le WebP.
 * - Un format unique simplifie la gestion des images côté serveur.
 *
 * @param array  $file      Le tableau $_FILES['nom_du_champ'] contenant
 *                          les infos sur le fichier uploadé (tmp_name, error, etc.)
 * @param string $uploadDir Le chemin du dossier de destination (ex: 'uploads/products')
 *
 * @return string|false     Le nom du fichier WebP généré en cas de succès,
 *                          ou false en cas d'erreur (fichier invalide, mauvais format…)
 */
function handleImageUpload($file, $uploadDir) {
    // Vérification que l'upload s'est bien passé côté serveur
    // UPLOAD_ERR_OK vaut 0 et signifie qu'il n'y a eu aucune erreur
    // Les autres codes (1-7) indiquent des problèmes : fichier trop gros,
    // upload partiel, pas de dossier temporaire, etc.
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return false;
    }

    // getimagesize() vérifie que le fichier est bien une image valide
    // et retourne ses dimensions + son type MIME.
    // C'est une vérification de sécurité importante car un utilisateur
    // malveillant pourrait renommer un fichier .exe en .jpg.
    $info = getimagesize($file['tmp_name']);
    if (!$info) return false;

    // Récupère le type MIME réel de l'image (ex: 'image/jpeg', 'image/png')
    // Ce type est déterminé par le contenu du fichier, pas par son extension,
    // ce qui est plus fiable pour la sécurité.
    $mime = $info['mime'];

    // Crée une ressource image GD en mémoire à partir du fichier uploadé.
    // La bibliothèque GD de PHP permet de manipuler les images
    // (redimensionner, convertir, ajouter du texte…).
    // On utilise une fonction différente selon le format source.
    switch ($mime) {
        case 'image/jpeg':
            // Les JPEG sont les plus courants pour les photos de produits
            $image = imagecreatefromjpeg($file['tmp_name']);
            break;
        case 'image/png':
            // Les PNG supportent la transparence, il faut la préserver
            $image = imagecreatefrompng($file['tmp_name']);

            // Convertit la palette de couleurs en couleurs "vraies" (true color)
            // car les PNG à palette limitée perdraient des informations de couleur
            imagepalettetotruecolor($image);

            // Active le mélange alpha pour gérer correctement les pixels
            // semi-transparents lors de la conversion
            imagealphablending($image, true);

            // Indique à GD de sauvegarder le canal alpha (transparence)
            // dans l'image de sortie, sinon les zones transparentes
            // deviendraient noires
            imagesavealpha($image, true);
            break;
        case 'image/webp':
            // Si l'image est déjà en WebP, on la charge quand même
            // pour la re-sauvegarder avec notre qualité standardisée (80%)
            $image = imagecreatefromwebp($file['tmp_name']);
            break;
        default:
            // Format non supporté (GIF, BMP, TIFF…) → on refuse l'upload
            // pour garantir la cohérence des images sur le site
            return false;
    }

    // Vérification supplémentaire : la création de l'image peut échouer
    // si le fichier est corrompu même avec un type MIME valide
    if (!$image) return false;

    // Génère un nom de fichier unique avec le préfixe 'prod_'
    // uniqid() utilise le timestamp en microsecondes pour éviter
    // les collisions de noms quand plusieurs images sont uploadées
    // simultanément. Exemple : "prod_667f3a1c2d4e1.webp"
    $filename = uniqid('prod_') . '.webp';

    // Construit le chemin complet de destination
    $filepath = $uploadDir . '/' . $filename;

    // Convertit l'image en WebP et la sauvegarde sur le disque
    // Le 3ème paramètre (80) est la qualité de compression :
    // - 100 = qualité maximale, fichier plus lourd
    // - 80  = bon compromis qualité/poids pour un site e-commerce
    // - 50  = léger mais perte de détails visible
    imagewebp($image, $filepath, 80);

    // Libère la mémoire RAM occupée par la ressource image GD
    // Important car les images peuvent consommer beaucoup de mémoire
    // (une image 4000x3000 en true color = ~36 Mo en RAM)
    imagedestroy($image);

    // Retourne uniquement le nom du fichier (pas le chemin complet)
    // car le chemin du dossier est déjà connu par le code appelant.
    // Ce nom sera stocké dans la colonne 'image_path' de la table products.
    return $filename;
}
?>
