<?php
$xmlFile ="cvs.xml";

$host="localhost";
$dbname="sportify";
$utilisateur="root";
$motdepasse="";
$charset="utf8mb4";

$dsn="mysql:host=$host;dbname=$dbname;charset=$charset";
$options=[
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try{
    $pdo=new PDO($dsn, $utilisateur, $motdepasse, $options);
} catch(PDOException $e) {
    die("Echec BDD: " . $e->getMessage());
}

if(!file_exists($xmlFile)) {
    die("Fichier xml invalide: $xmlFile");
}

$xml=simplexml_load_file($xmlFile);

$insertionUtilisateur = $pdo->prepare(
    "INSERT INTO utilisateurs (
    nom, 
    prenom, 
    email, 
    mot_de_passe, 
    type_utilisateur
    ) VALUES (
    :nom, 
    :prenom, 
    :email, 
    :mdp, 
    'coach')
    ");
$insertionCoach = $pdo->prepare(
    "INSERT INTO coachs (
    id_coach,
    bureau,
    telephone_bureau,
    specialite_principale,
    cv_xml,
    date_embauche
     ) VALUES (
    :id_coach,
    :bureau,
    :tel,
    :specialite,
    :cv,
    CURDATE()
     )"
    );

$counter = 1;

foreach($xml->cv as $c){
    $nom=(string)$c->Nom;
    $prenom=(string)$c->Prenom;
    $email=(string)$c->Email;
    $telephone=(string)$c->Telephone;
    $specialite=(string)$c->Discipline;
    $cvXml=(string)$c->asXML();
    $password='jesuiscoach123';
    $hash=password_hash($password,PASSWORD_DEFAULT);
    
    $insertionUtilisateur->execute([
        ':nom'=>$nom,
        ':prenom'=>$prenom,
        ':email'=>$email,
        ':mdp'=>$hash
    ]);

    $userId=$pdo->LastInsertId();

    $bureau=sprintf("SC-%02d", $counter);

    $insertionCoach->execute([
        ':id_coach'=>$userId,
        ':specialite'=>$specialite,
        ':tel'=>$telephone,
        ':bureau'=>$bureau,
        ':cv'=>$cvXml
    ]);
    $counter++;
    echo "Ajout du coach $prenom $nom (utilisateur #$userId, bureau $bureau)\n";
}
echo" C'est gucci!";
?>
