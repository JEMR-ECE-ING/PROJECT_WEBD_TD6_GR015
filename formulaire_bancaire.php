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
    die("Erreur base de données: " . $e->getMessage());
}

// Vérifier que l'utilisateur est connecté
$id_utilisateur = $_SESSION['id_utilisateur'] ?? null;
if (!$id_utilisateur) {
    header('Location: formulaire_connexion.php');
    exit();
}

// TRAITEMENT DE LA SUPRESSION DE CARTE (ouais jsp pourquoi j'ai ecrit ça comme ça lol)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_carte'])) {
    $id_carte_a_supprimer = intval($_POST['delete_carte']); // on converti en int pour eviter les problemes
    
    // bon la faut verifier que cette carte aparient bien a notre utilisateur connecté
    // sinon nimporte qui pourrait supprimer les cartes des autres et ca c'est pas terrible
    $stmtCheck = $pdo->prepare("SELECT id_carte FROM cartes_paiement WHERE id_carte = ? AND id_client = ?");
    $stmtCheck->execute([$id_carte_a_supprimer, $id_utilisateur]);
    
    if ($stmtCheck->fetch()) {
        // ok c'est bon on peut supprimer cette carte tranquille
        $stmtDel = $pdo->prepare("DELETE FROM cartes_paiement WHERE id_carte = ?");
        $stmtDel->execute([$id_carte_a_supprimer]);
        $_SESSION['success'] = "Carte supprimée avec succès."; // message de reussite
    } else {
        // oups quelqu'un essaie de faire quelque chose de louche
        $_SESSION['error'] = "Vous ne pouvez pas supprimer cette carte."; // message d'erreur
    }
    
    // on redirige vers la meme page pour eviter les doublons si l'utilisateur actualise
    header("Location: formulaire_bancaire.php");
    exit(); // important de mettre exit() apres header sinon le code continue a s'executer
}

// TRAITEMENT DE L'AJOUT DE CARTE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['type_carte'])) {
    $type_carte = trim($_POST['type_carte']);
    $numero_carte_masque = trim($_POST['numero_carte_masque']);
    $cryptogramme = trim($_POST['cryptogramme']);
    $nom_carte = trim($_POST['nom_carte']);
    $date_expiration = $_POST['date_expiration'] ?? null;
    
    // Si la date d'expiration est vide, on met NULL
    // parceque sinon mysql met 0000-00-00 et c'est moche
    if (empty($date_expiration)) {
        $date_expiration = null;
    }

    // Validation des champs requis (obligatoires quoi)
    if (empty($type_carte) || empty($numero_carte_masque) || empty($cryptogramme) || empty($nom_carte)) {
        $_SESSION['error'] = "Veuillez remplir tous les champs obligatoires.";
    } else {
        // Validation du format du numéro masqué
        // bon faut que ca ressemble a un vrai numero de carte
        if (!preg_match('/^[\d\s\*]{12,20}$/', $numero_carte_masque)) {
            $_SESSION['error'] = "Format du numéro de carte invalide.";
        } else {
            // Insertion de la nouvelle carte dans la bdd
            try {
                $stmtInsert = $pdo->prepare("
                    INSERT INTO cartes_paiement (id_client, type_carte, numero_carte_masque, cryptogramme, nom_carte, date_expiration, date_ajout) 
                    VALUES (?, ?, ?, ?, ?, ?, NOW())
                ");
                $stmtInsert->execute([$id_utilisateur, $type_carte, $numero_carte_masque, $cryptogramme, $nom_carte, $date_expiration]);
                $_SESSION['success'] = "Carte ajoutée avec succès."; // tout va bien
            } catch (PDOException $e) {
                $_SESSION['error'] = "Erreur lors de l'ajout de la carte: " . $e->getMessage(); // ah merde ca marche pas
            }
        }
    }
    header("Location: formulaire_bancaire.php");
    exit();
}

// Récupérer toutes les cartes de l'utilisateur depuis la base
$stmtCartes = $pdo->prepare("
    SELECT id_carte, type_carte, numero_carte_masque, nom_carte, date_expiration, date_ajout 
    FROM cartes_paiement 
    WHERE id_client = ? 
    ORDER BY date_ajout DESC
");
$stmtCartes->execute([$id_utilisateur]);
$cartes = $stmtCartes->fetchAll(); // on recupere tout d'un coup

// Gestion des messages de succes/erreur
$message = '';
$messageType = '';
if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $messageType = 'success';
    unset($_SESSION['success']); // on supprime le message apres l'avoir affiché
}
if (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $messageType = 'error';
    unset($_SESSION['error']); // pareil ici
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mes cartes bancaires - Sportify</title>
    <link rel="stylesheet" href="style.css"/>
    <style>
        /* styles pour afficher les cartes en grille */
        .cartes-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }

        /* style pour chaque carte individuelle */
        .carte-card {
            background: rgba(0, 0, 0, 0.8);
            border: 2px solid #00ff88;
            border-radius: 15px;
            padding: 1.5rem;
            position: relative;
            transition: transform 0.3s ease;
        }

        /* petit effet sympa au survol */
        .carte-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 255, 136, 0.3);
        }

        /* style pour le type de carte (visa, mastercard, etc) */
        .carte-type {
            font-size: 1.2rem;
            font-weight: bold;
            color: #00ff88;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
        }

        /* style pour le numero de carte masqué */
        .carte-numero {
            font-size: 1.1rem;
            color: #fff;
            font-family: 'Courier New', monospace; /* police monospace pour que ca ressemble aux vraies cartes */
            margin-bottom: 0.5rem;
        }

        /* nom sur la carte */
        .carte-nom {
            color: #ccc;
            margin-bottom: 0.5rem;
        }

        /* date d'expiration */
        .carte-expiration {
            color: #aaa;
            font-size: 0.9rem;
            margin-bottom: 1rem;
        }

        /* date d'ajout de la carte */
        .carte-date-ajout {
            color: #888;
            font-size: 0.8rem;
        }

        /* bouton X rouge pour supprimer */
        .btn-supprimer {
            position: absolute;
            top: 10px;
            right: 10px;
            background-color: #ff4444;
            color: white;
            border: none;
            border-radius: 50%;
            width: 30px;
            height: 30px;
            cursor: pointer;
            font-size: 1.2rem;
            transition: background-color 0.3s;
        }

        /* effet au survol sur le bouton supprimer */
        .btn-supprimer:hover {
            background-color: #cc0000;
        }

        /* message quand y'a pas de cartes */
        .no-cartes {
            text-align: center;
            color: #ccc;
            font-size: 1.2rem;
            padding: 3rem;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 15px;
            border: 2px dashed #666;
        }

        /* styles pour les messages de succes/erreur */
        .message {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 2rem;
            text-align: center;
            font-weight: bold;
        }

        .message.success {
            background-color: rgba(0, 255, 0, 0.2);
            border: 2px solid #00ff00;
            color: #00ff00;
        }

        .message.error {
            background-color: rgba(255, 0, 0, 0.2);
            border: 2px solid #ff0000;
            color: #ff4444;
        }
    </style>
</head>
<body>
<header class="header">
    <nav class="nav-container">
        <div class="logo">
            <div><img src="images/logov2.png" class="logo-icon"></div>
            <span class="logo-text">SPORTIFY</span>
        </div>
        <ul class="nav-menu">
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="tout_parcourir.php">Tout Parcourir</a></li>
            <li><a href="#recherche">Recherche</a></li>
            <li><a href="mes_rendezvous.php">Rendez-vous</a></li>
            <li><a href="votre_compte.php">Votre Compte</a></li>
        </ul>
        <div class="nav-auth">
                <div class="cta-wrapper">
                    <button class="cta-button" onclick="window.location.href='partie_php/traitement_logout.php'">
                    Déconnexion
                    </button>
                </div>
            <div>
    </nav>
</header>

<div class="main-background">
    <div class="main-content">
        
        <?php if ($message): ?>
            <div class="message <?= $messageType ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <h2 class="event-title">Mes moyens de paiement</h2>

        <?php if (!empty($cartes)): ?>
            <div class="cartes-grid">
                <?php foreach ($cartes as $carte): ?>
                    <div class="carte-card">
                        <!-- bouton pour supprimer la carte (le petit X rouge en haut a droite) -->
                        <button class="btn-supprimer" onclick="confirmerSuppression(<?= $carte['id_carte'] ?>)" title="Supprimer cette carte">×</button>
                        
                        <div class="carte-type"><?= htmlspecialchars($carte['type_carte']) ?></div>
                        <div class="carte-numero"><?= htmlspecialchars($carte['numero_carte_masque']) ?></div>
                        <div class="carte-nom"><?= htmlspecialchars($carte['nom_carte']) ?></div>
                        
                        <?php if ($carte['date_expiration'] && $carte['date_expiration'] !== '0000-00-00'): ?>
                            <div class="carte-expiration">Expire le : <?= date('m/Y', strtotime($carte['date_expiration'])) ?></div>
                        <?php else: ?>
                            <div class="carte-expiration">Date d'expiration non renseignée</div>
                        <?php endif; ?>
                        
                        <div class="carte-date-ajout">Ajoutée le : <?= date('d/m/Y', strtotime($carte['date_ajout'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-cartes">
                <p>Aucune carte enregistrée</p>
                <p>Ajoutez votre première carte ci-dessous</p>
            </div>
        <?php endif; ?>

        <h2 class="event-title">Ajouter une nouvelle carte</h2>
        
        <!-- formulaire pour ajouter une nouvelle carte -->
        <form method="POST" novalidate class="signup-form">
            <div class="form-group">
                <label for="type_carte">Type de carte *</label>
                <select id="type_carte" name="type_carte" required>
                    <option value="" disabled selected>Choisissez un type</option>
                    <option value="visa">Visa</option>
                    <option value="mastercard">MasterCard</option>
                    <option value="amex">American Express</option>
                    <option value="paypal">Paypal</option>
                </select>
            </div>

            <div class="form-group">
                <label for="numero_carte_masque">Numéro de carte masqué *</label>
                <input type="text" id="numero_carte_masque" name="numero_carte_masque" placeholder="**** **** **** 1234" maxlength="20" required pattern="[\d\s\*]{12,20}" title="Format attendu : chiffres, espaces ou *">
            </div>

            <div class="form-group">
                <label for="cryptogramme">Cryptogramme *</label>
                <input type="text" id="cryptogramme" name="cryptogramme" placeholder="***" maxlength="4" required>
            </div>

            <div class="form-group">
                <label for="nom_carte">Nom sur la carte *</label>
                <input type="text" id="nom_carte" name="nom_carte" placeholder="Nom sur la carte" maxlength="255" required>
            </div>

            <div class="form-group">
                <label for="date_expiration">Date d'expiration</label>
                <input type="month" id="date_expiration" name="date_expiration" min="<?= date('Y-m') ?>">
            </div>

            <button type="submit" class="cta-button">Ajouter la carte</button>
        </form>
    </div>
</div>

<footer class="footer">
    <div class="footer-content">
        <div class="footer-section">
            <h3>Contact Sportify</h3>
            <p>contact@sportify-omnes.fr</p>
            <p>01 23 45 67 89</p>
            <p>Campus Omnes Education</p>
            <p>123 Rue du Sport, 75000 Paris</p>
        </div>

        <div class="footer-section">
            <h3>Horaires</h3>
            <p>Lundi - Vendredi : 8h00 - 20h00</p>
            <p>Samedi : 9h00 - 18h00</p>
            <p>Dimanche : 10h00 - 16h00</p>
        </div>

        <div class="footer-section">
            <h3>Liens Rapides</h3>
            <a href="#activites">Activités Sportives</a>
            <a href="#competition">Sports de Compétition</a>
            <a href="#salle">Salle de Sport</a>
            <a href="#rdv">Mes Rendez-vous</a>
        </div>

        <div class="footer-section">
            <h3>Localisation</h3>
            <div class="map-placeholder">
                <iframe
                    src="https://maps.google.com/maps?q=10%20Rue%20Sextius%20Michel%2C%20750006%20Paris&hl=fr&z=15&output=embed"                        
                    width="100%"
                    height="100%"
                    style="border:0; border-radius:8px;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <p>&copy; 2025 Sportify - Omnes Education. Tous droits réservés.</p>
    </div>
</footer>

<!-- formulaire caché pour la supression des cartes -->
<!-- on le cache parceque c'est moche et on veut juste que le bouton marche -->
<form id="form-suppression" method="POST" style="display: none;">
    <input type="hidden" name="delete_carte" id="carte-a-supprimer">
</form>

<script>
// fonction pour confirmer la supression avant de vraiment supprimer
// parceque sinon les gens vont supprimer par accident et venir se plaindre apres
function confirmerSuppression(idCarte) {
    // on demande confirmation avec un popup
    if (confirm('Êtes-vous sûr de vouloir supprimer cette carte ?\nCette action est irréversible.')) {
        // si l'utilisateur confirme, on met l'id de la carte dans le formulaire caché
        document.getElementById('carte-a-supprimer').value = idCarte;
        // et on envoie le formulaire
        document.getElementById('form-suppression').submit();
    }
    // sinon on fait rien et la carte reste
}
</script>

</body>
</html>