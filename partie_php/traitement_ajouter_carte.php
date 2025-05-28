<?php
session_start();

// 1) Récupérer l’ID utilisateur tel que vous l'avez défini lors de la connexion
//    (remplacez 'user_id' par 'id_utilisateur' si c’est ce que vous avez mis en session)
$id_utilisateur = $_SESSION['id_utilisateur'] ?? null;
if (!$id_utilisateur) {
    $_SESSION['error'] = "Veuillez vous connecter avant d'ajouter une carte bancaire.";
    header('Location: ../formulaire_connexion.php');
    exit();
}

// 2) Connexion PDO
try {
    $pdo = new PDO(
        'mysql:host=localhost;dbname=sportify;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Erreur base de données: " . $e->getMessage());
}

// 3) Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type_carte           = $_POST['type_carte']            ?? '';
    $numero_carte_masque  = $_POST['numero_carte_masque']   ?? '';
    $cryptogramme         = $_POST['cryptogramme']          ?? '';
    $nom_carte            = $_POST['nom_carte']             ?? '';
    $date_expiration      = $_POST['date_expiration']       ?? '';

    // 4) Validation simple
    if (empty($type_carte) || empty($numero_carte_masque) || empty($cryptogramme) || empty($nom_carte)) {
        $_SESSION['error'] = "Tous les champs marqués d’un * sont requis.";
        header('Location: ../formulaire_bancaire.php');
        exit();
    }

    // 5) Vérifier que la carte n’existe pas déjà
    $stmtCheck = $pdo->prepare("
      SELECT COUNT(*) 
      FROM cartes_paiement 
      WHERE id_client = ? 
        AND numero_carte_masque = ?
    ");
    $stmtCheck->execute([$id_utilisateur, $numero_carte_masque]);
    if ($stmtCheck->fetchColumn() > 0) {
        $_SESSION['error'] = "Cette carte est déjà associée à votre compte.";
        header('Location: ../formulaire_bancaire.php');
        exit();
    }

    // 6) Insertion
    $stmtInsert = $pdo->prepare("
      INSERT INTO cartes_paiement
        (id_client, type_carte, numero_carte_masque, cryptogramme, nom_carte, date_expiration)
      VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmtInsert->execute([
        $id_utilisateur,
        $type_carte,
        $numero_carte_masque,
        $cryptogramme,
        $nom_carte,
        $date_expiration
    ]);

    $_SESSION['success'] = "Carte bancaire ajoutée avec succès.";
    header('Location: ../votre_compte.php');
    exit();
}

// Si on arrive ici, c’est qu’il n’y a pas eu de POST → on peut rediriger ou afficher une 404
header('Location: ../formulaire_bancaire.php');
exit();
?>
