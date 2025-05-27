<?php
// partie_php/check_login.php
// Fichier à inclure dans les pages qui nécessitent une connexion

session_start();

// Fonction pour vérifier si l'utilisateur est connecté
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

// Fonction pour vérifier le type d'utilisateur
function getUserType() {
    return $_SESSION['user_type'] ?? null;
}

// Fonction pour obtenir les infos utilisateur
function getUserInfo() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'nom' => $_SESSION['user_nom'],
        'prenom' => $_SESSION['user_prenom'],
        'type' => $_SESSION['user_type']
    ];
}

// Fonction pour rediriger si pas connecté
function requireLogin($redirect_url = 'connexion.html') {
    if (!isLoggedIn()) {
        header('Location: ' . $redirect_url . '?error=' . urlencode("Vous devez être connecté pour accéder à cette page"));
        exit();
    }
}

// Fonction pour vérifier un type d'utilisateur spécifique
function requireUserType($required_type, $redirect_url = 'accueil.html') {
    requireLogin();
    
    if (getUserType() !== $required_type) {
        header('Location: ' . $redirect_url . '?error=' . urlencode("Accès non autorisé"));
        exit();
    }
}

// Vérifier le cookie "Se souvenir de moi" si pas de session active
if (!isLoggedIn() && isset($_COOKIE['sportify_remember'])) {
    $cookie_data = base64_decode($_COOKIE['sportify_remember']);
    list($user_id, $email) = explode(':', $cookie_data);
    
    // Ici vous pourriez reconnecter automatiquement l'utilisateur
    // en vérifiant les données dans la base
}
?>