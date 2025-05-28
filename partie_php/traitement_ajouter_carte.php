<?php
session_start();

// Connexion à la base de données
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
    die("Erreur base de données: " . $e->getMessage());
}

// Vérifier que l'utilisateur est connecté
$id_utilisateur = $_SESSION['user_id'] ?? null;
if (!$id_utilisateur) {
    $_SESSION['error'] = "Veuillez vous connecter avant d'ajouter une carte bancaire.";
    header('Location: ../formulaire_connexion.html');
    exit();
}

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer les données du formulaire
    $type_carte = $_POST['type_carte'] ?? '';
    $numero_carte_masque = $_POST['numero_carte_masque'] ?? '';
    $nom_carte = $_POST['nom_carte'] ?? '';
    $date_expiration = $_POST['date_expiration'] ?? '';

    // Validation des données (ajoutez des validations supplémentaires si nécessaire)
    if (empty($type_carte) || empty($numero_carte_masque) || empty($nom_carte)) {
        $_SESSION['error'] = "Tous les champs sont requis.";
        header('Location: ../formulaire_bancaire.html');
        exit();
    }

    // Assurez-vous que la carte n'est pas déjà associée au compte
    $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM cartes_paiement WHERE id_client = ? AND numero_carte_masque = ?");
    $stmtCheck->execute([$id_utilisateur, $numero_carte_masque]);
    $existingCard = $stmtCheck->fetchColumn();

    if ($existingCard > 0) {
        $_SESSION['error'] = "Cette carte est déjà associée à votre compte.";
        header('Location: traitement_ajouter_carte.php');
        exit();
    }

    // Insertion des données de la carte dans la base de données
    $stmtInsert = $pdo->prepare("
        INSERT INTO cartes_paiement (id_client, type_carte, numero_carte_masque, nom_carte, date_expiration)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([$id_utilisateur, $type_carte, $numero_carte_masque, $nom_carte, $date_expiration]);

    $_SESSION['success'] = "Carte bancaire ajoutée avec succès.";
    header('Location: ../votre_compte.php'); // Redirige vers la page de compte utilisateur
    exit();
}
?>

