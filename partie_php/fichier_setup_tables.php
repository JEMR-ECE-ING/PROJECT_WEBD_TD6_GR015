<?php
// C'est un processus en plusieurs etapes:
/*
-1) Lance d'abord ce fichier pour creer les tables
-2) Ensuite, lance le fichier fs1.php pour remplir la table categories_sport
-3) Chez moi, le fichier fs1.php ne marchait pas donc j'ai du passer par seed.sql.
    Il faut donc lancer ca dans le terminal: mysql -u root -p sportify < c:\Users\User\Documents\ECE\ING2\PROJET_WEBDYNAMIQUE\partie_php\seed_categories.sql




*/

$host='localhost';       // ou 'localhost'
$dbname='sportify';
$user='root';
$password='';
$charset='utf8mb4';

$dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $password, $options);
} catch (PDOException $e) {
    die("Connexion échouée : " . $e->getMessage());
}

$sql = <<<SQL
CREATE TABLE IF NOT EXISTS utilisateurs (
    id_utilisateur INT PRIMARY KEY AUTO_INCREMENT,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    type_utilisateur ENUM('admin','coach','client') NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actif BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS clients (
    id_client INT PRIMARY KEY,
    adresse_ligne1 VARCHAR(255),
    adresse_ligne2 VARCHAR(255),
    ville VARCHAR(100),
    code_postal VARCHAR(10),
    pays VARCHAR(100),
    telephone VARCHAR(20),
    carte_etudiant VARCHAR(50),
    date_naissance DATE,
    FOREIGN KEY (id_client) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS coachs (
    id_coach INT PRIMARY KEY,
    bureau VARCHAR(100),
    telephone_bureau VARCHAR(20),
    specialite_principale VARCHAR(100),
    photo_url VARCHAR(500),
    video_url VARCHAR(500),
    cv_xml TEXT,
    statut_disponibilite ENUM('disponible','occupe','absent') DEFAULT 'disponible',
    date_embauche DATE,
    FOREIGN KEY (id_coach) REFERENCES utilisateurs(id_utilisateur) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS categories_sport (
    id_categorie INT PRIMARY KEY AUTO_INCREMENT,
    nom_categorie ENUM('activites_sportives','sports_competition','salle_sport') NOT NULL,
    description TEXT
);

CREATE TABLE IF NOT EXISTS activites_sportives (
    id_activite INT PRIMARY KEY AUTO_INCREMENT,
    nom_activite VARCHAR(100) NOT NULL,
    id_categorie INT,
    description TEXT,
    prix DECIMAL(10,2) DEFAULT 0,
    duree_seance INT DEFAULT 60,
    actif BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (id_categorie) REFERENCES categories_sport(id_categorie)
);

CREATE TABLE IF NOT EXISTS rendez_vous (
    id_rdv INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT NOT NULL,
    id_coach INT NOT NULL,
    id_activite INT,
    date_rdv DATETIME NOT NULL,
    duree INT DEFAULT 60,
    statut ENUM('confirme','annule','termine','en_attente') DEFAULT 'en_attente',
    lieu VARCHAR(255),
    notes TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES clients(id_client),
    FOREIGN KEY (id_coach) REFERENCES coachs(id_coach),
    FOREIGN KEY (id_activite) REFERENCES activites_sportives(id_activite)
);

CREATE TABLE IF NOT EXISTS paiements (
    id_paiement INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT NOT NULL,
    id_rdv INT NOT NULL,
    montant DECIMAL(10,2) NOT NULL,
    type_carte ENUM('visa', 'mastercard', 'amex') NOT NULL,
    numero_carte_masque VARCHAR(20) NOT NULL,
    statut_paiement ENUM('en_attente', 'reussi', 'echec') DEFAULT "en_attente",
    date_paiement TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reference_transaction VARCHAR(100) UNIQUE,

    FOREIGN KEY (id_client) REFERENCES clients(id_client),
    FOREIGN KEY (id_rdv) REFERENCES rendez_vous(id_rdv)
);

CREATE TABLE IF NOT EXISTS cartes_paiement(
    id_carte INT PRIMARY KEY AUTO_INCREMENT,
    id_client INT NOT NULL,
    type_carte ENUM('visa', 'mastercard', 'amex') NOT NULL,
    numero_carte_masque VARCHAR(20) NOT NULL,
    nom_carte VARCHAR(255) NOT NULL,
    date_expiration DATE,
    date_ajout TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_client) REFERENCES clients(id_client) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS salle_sport (
    id_salle INT PRIMARY KEY AUTO_INCREMENT,
    numero_salle VARCHAR(10) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    email VARCHAR(255) NOT NULL,
    description TEXT,
    actif BOOLEAN DEFAULT TRUE
);

CREATE TABLE IF NOT EXISTS services_salle_sport (
    id_service INT PRIMARY KEY AUTO_INCREMENT,
    id_salle INT NOT NULL,
    nom_service VARCHAR(255) NOT NULL,
    description TEXT,
    actif BOOLEAN DEFAULT TRUE,

    FOREIGN KEY(id_salle) REFERENCES salle_sport(id_salle) ON DELETE CASCADE
);


SQL;

$statements = array_filter(array_map('trim', explode(';', $sql)));

foreach ($statements as $stmt) {
    if ($stmt !== '') {
        try {
            $pdo->exec($stmt);
            echo "OK:" . strtok($stmt, "\n") . " …<br>";
        } catch (PDOException $e) {
            echo "Erreur sur « " . strtok($stmt, "\n") . " »: " 
               . $e->getMessage() . "<br>";
        }
    }
}

echo "<p>Tout est gucci!</p>";
