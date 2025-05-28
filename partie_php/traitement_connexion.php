<?php
session_start();

$host = 'localhost';
$dbname = 'sportify';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    $_SESSION['error'] = "Erreur de connexion à la base de données.";
    header("Location: ../formulaire_connexion.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $mot_de_passe = $_POST['mot_de_passe'] ?? '';

    // Validation des champs
    if (empty($email) || empty($mot_de_passe)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs.";
        header("Location: ../formulaire_connexion.php");
        exit();
    }

    //On fait des requetes pour chercher l'email de l'utilisateur dans le tableau utilisaiteurs
    $stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // On cherche a valider le mot de pase
    if ($user) {
        if ($user['email'] === 'admin@omneseducation.fr') {
            $motdepasseadmin = "admin1234";
            if ($mot_de_passe !== $motdepasseadmin) {
                $_SESSION['error'] = "Email ou mot de passe incorrect.";
                header("Location: ../formulaire_connexion.php");
                exit();
            }
        } elseif (empty($user['mot_de_passe']) || !password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $_SESSION['error'] = "Email ou mot de passe incorrect.";
            header("Location: ../formulaire_connexion.php");
            exit();
        }

        $_SESSION['id_utilisateur'] = $user['id_utilisateur'];
        $_SESSION['nom'] = $user['nom'];
        $_SESSION['prenom'] = $user['prenom'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['type_utilisateur'] = $user['type_utilisateur'];

        header("Location: ../votre_compte.php");
        exit();
    } else {
        $_SESSION['error'] = "Email ou mot de passe incorrect.";
        header("Location: ../formulaire_connexion.php");
        exit();
    }
}
?>
