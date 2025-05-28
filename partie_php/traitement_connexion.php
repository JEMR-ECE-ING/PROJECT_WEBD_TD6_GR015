<?php
session_start();

$host = 'localhost';
$dbname = 'sportify';
$user = 'root';
$password = '';

try {
    // Connexion à la base de données avec PDO et gestion d'erreur
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
} catch (PDOException $e) {
    // En cas d'erreur, stocker le message et rediriger vers formulaire connexion
    $_SESSION['error'] = "Erreur de connexion à la base de données.";
    header("Location: ../formulaire_connexion.html");
    exit();
}

// Vérification que la requête est bien POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../formulaire_connexion.html");
    exit();
}

// Recuperer et traiter
$email = trim($_POST['email'] ?? '');
$mot_de_passe = $_POST['mot_de_passe'] ?? '';

// Validation simple des champs
$errors = [];
if (empty($email) || empty($mot_de_passe)) {
    $errors[] = "Veuillez remplir tous les champs.";
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = "Email invalide.";
}
if (!empty($errors)) {
    $_SESSION['errors'] = $errors;
    header("Location: ../formulaire_connexion.html");
    exit();
}

// Recherche de l'utilisateur dans la base par email
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = "Email ou mot de passe incorrect.";
    header("Location: ../formulaire_connexion.html");
    exit();
}

//J'ai fait une petite faute et j'ai mis le mdp admin sans le hash dans la bdd
if ($user['email'] === 'admin@omneseducation.fr') {
    $motdepasseadmin = "admin1234";
    if ($mot_de_passe !== $motdepasseadmin) {
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header("Location: ../formulaire_connexion.html");
        exit();
    }
} else {
    //Verification mot de passe classique
    if (empty($user['mot_de_passe']) || !password_verify($mot_de_passe, $user['mot_de_passe'])) {
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header("Location: ../formulaire_connexion.html");
        exit();
    }
}

// Authentification réussie : création des variables de session
$_SESSION['id_utilisateur'] = $user['id_utilisateur'];
$_SESSION['nom'] = $user['nom'];
$_SESSION['prenom'] = $user['prenom'];
$_SESSION['email'] = $user['email'];
$_SESSION['type_utilisateur'] = $user['type_utilisateur'];

// Gestion du cookie "se souvenir de moi" (30 jours)
if ($remember) {
    $cookieData = base64_encode($user['id_utilisateur'] . ':' . $user['email']);
    setcookie('sportify_remember', $cookieData, time() + (86400 * 30), "/", "", false, true); // HttpOnly pour plus de sécurité
}

// Redirection vers la dashboard
header("Location: ../votre_compte.php");
exit();
