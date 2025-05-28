<?php
// partie_php/traitement_formulaire_inscription.php
session_start();

$host = 'localhost';
$nombdd = 'sportify';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$nombdd;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    $_SESSION['error'] = "Erreur de connexion à la BDD";
    header('Location: ../formulaire_inscription.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../formulaire_inscription.php');
    exit();
}

//nettoyer les caracteres speciaux du html 
function cleanInput($data) {
    return htmlspecialchars(trim($data));
}

// recup les donnnes
$nom = cleanInput($_POST['nom'] ?? '');
$prenom = cleanInput($_POST['prenom'] ?? '');
$email = cleanInput($_POST['email'] ?? '');
$mot_de_passe = $_POST['mot_de_passe'] ?? '';
$confirmer_mot_de_passe = $_POST['confirmer_mot_de_passe'] ?? '';
$date_naissance = !empty($_POST['date_naissance']) ? $_POST['date_naissance'] : null;
$adresse_ligne1 = !empty($_POST['adresse_ligne1']) ? cleanInput($_POST['adresse_ligne1']) : null;
$adresse_ligne2 = !empty($_POST['adresse_ligne2']) ? cleanInput($_POST['adresse_ligne2']) : null;
$ville = !empty($_POST['ville']) ? cleanInput($_POST['ville']) : null;
$code_postal = !empty($_POST['code_postal']) ? cleanInput($_POST['code_postal']) : null;
$pays = !empty($_POST['pays']) ? cleanInput($_POST['pays']) : null;
$telephone = !empty($_POST['telephone']) ? cleanInput($_POST['telephone']) : null;
$carte_etudiant = !empty($_POST['carte_etudiant']) ? cleanInput($_POST['carte_etudiant']) : null;

$erreurs = [];

// Validations obligatoires
if (empty($nom)) {
    $erreurs[] = "Le nom est requis";
}
if (empty($prenom)) {
    $erreurs[] = "Le prénom est requis";
}
if (empty($email)) {
    $erreurs[] = "L'email est requis";
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $erreurs[] = "Format d'email invalide";
} elseif (!str_ends_with($email, '@omneseducation.fr')) {
    $erreurs[] = "Vous devez utiliser une adresse email Omnes Education (@omneseducation.fr)";
}

if (empty($mot_de_passe)) {
    $erreurs[] = "Le mot de passe est requis";
}
if ($mot_de_passe !== $confirmer_mot_de_passe) {
    $erreurs[] = "Les mots de passe ne correspondent pas";
}

// email existant
if (empty($erreurs)) {
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM utilisateurs WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $erreurs[] = "Il semble que cet email soit déjà utilisé";
        }
    } catch(PDOException $e) {
        $erreurs[] = "Erreur lors de la vérification de l'email";
    }
}

// Si des erreurs, sauvegarder et rediriger
if (!empty($erreurs)) {
    $_SESSION['erreurs'] = $erreurs;
    $_SESSION['form_data'] = [
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'date_naissance' => $_POST['date_naissance'] ?? '',
        'adresse_ligne1' => $_POST['adresse_ligne1'] ?? '',
        'adresse_ligne2' => $_POST['adresse_ligne2'] ?? '',
        'ville' => $_POST['ville'] ?? '',
        'code_postal' => $_POST['code_postal'] ?? '',
        'pays' => $_POST['pays'] ?? 'France',
        'telephone' => $_POST['telephone'] ?? '',
        'carte_etudiant' => $_POST['carte_etudiant'] ?? '',
    ];
    header("Location: ../formulaire_inscription.php");
    exit();
}


try {
    $pdo->beginTransaction();

    //coder le mot de passe
    $mot_de_passe_hash = password_hash($mot_de_passe, PASSWORD_DEFAULT);
    
    // Insérer dans utilisateurs
    $stmt = $pdo->prepare("
        INSERT INTO utilisateurs (
            nom, prenom, email, mot_de_passe, type_utilisateur, actif, date_creation
        ) VALUES (?, ?, ?, ?, 'client', 1, NOW())
    ");
    $stmt->execute([$nom, $prenom, $email, $mot_de_passe_hash]);
    
    $id_utilisateur = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("
        INSERT INTO clients (
            id_client,
            adresse_ligne1,
            adresse_ligne2,
            ville,
            code_postal,
            pays,
            telephone,
            carte_etudiant,
            date_naissance
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    
    $stmt->execute([
        $id_utilisateur,
        $adresse_ligne1,
        $adresse_ligne2,
        $ville,
        $code_postal,
        $pays,
        $telephone,
        $carte_etudiant,
        $date_naissance
    ]);

    $pdo->commit();
    
    //Nettoyer les sessions
    unset($_SESSION['erreurs']);
    unset($_SESSION['form_data']);
    
    $_SESSION['reussite_totale'] = 'Compte créé avec succès ! Vous pouvez maintenant vous connecter';
    header("Location: ../formulaire_connexion.php");
    exit();
    
} catch(PDOException $e) {
    $pdo->rollBack();
    
    //
    $_SESSION['erreur'] = "Erreur lors de la création: " . $e->getMessage();
    header("Location: ../formulaire_inscription.php");
    exit();
}
?>