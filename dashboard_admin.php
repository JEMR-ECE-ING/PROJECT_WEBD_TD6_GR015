<?php
session_start();

// connexion a la bdd comme dab
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

// verifier que l'utilisateur est connecte et qu'il est admin
$id_utilisateur = $_SESSION['id_utilisateur'] ?? null;
if (!$id_utilisateur) {
    header('Location: formulaire_connexion.php');
    exit();
}

$stmtUser = $pdo->prepare("SELECT nom, prenom, type_utilisateur FROM utilisateurs WHERE id_utilisateur = ?");
$stmtUser->execute([$id_utilisateur]);
$user_info = $stmtUser->fetch();

if (!$user_info || $user_info['type_utilisateur'] !== 'admin') {
    header('Location: historique.php');
    exit();
}

// TRAITEMENT DES ACTIONS ADMIN (suppression/modification)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];
    
    // supprimer un utilisateur
    if ($action === 'delete_user' && isset($_POST['user_id'])) {
        $user_id = intval($_POST['user_id']);
        try {
            $stmtDelUser = $pdo->prepare("DELETE FROM utilisateurs WHERE id_utilisateur = ?");
            $stmtDelUser->execute([$user_id]);
            $_SESSION['success'] = "Utilisateur supprimé avec succès";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
        }
    }
    
    // supprimer une carte de paiement
    elseif ($action === 'delete_card' && isset($_POST['card_id'])) {
        $card_id = intval($_POST['card_id']);
        try {
            $stmtDelCard = $pdo->prepare("DELETE FROM cartes_paiement WHERE id_carte = ?");
            $stmtDelCard->execute([$card_id]);
            $_SESSION['success'] = "Carte supprimée avec succès";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
        }
    }
    
    // supprimer une activite
    elseif ($action === 'delete_activity' && isset($_POST['activity_id'])) {
        $activity_id = intval($_POST['activity_id']);
        try {
            $stmtDelActivity = $pdo->prepare("DELETE FROM activites_sportives WHERE id_activite = ?");
            $stmtDelActivity->execute([$activity_id]);
            $_SESSION['success'] = "Activité supprimée avec succès";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
        }
    }
    
    // supprimer un rdv
    elseif ($action === 'delete_rdv' && isset($_POST['rdv_id'])) {
        $rdv_id = intval($_POST['rdv_id']);
        try {
            $stmtDelRdv = $pdo->prepare("DELETE FROM rendez_vous WHERE id_rdv = ?");
            $stmtDelRdv->execute([$rdv_id]);
            $_SESSION['success'] = "Rendez-vous supprimé avec succès";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
        }
    }
    
    // supprimer un paiement
    elseif ($action === 'delete_payment' && isset($_POST['payment_id'])) {
        $payment_id = intval($_POST['payment_id']);
        try {
            $stmtDelPayment = $pdo->prepare("DELETE FROM paiements WHERE id_paiement = ?");
            $stmtDelPayment->execute([$payment_id]);
            $_SESSION['success'] = "Paiement supprimé avec succès";
        } catch (Exception $e) {
            $_SESSION['error'] = "Erreur lors de la suppression: " . $e->getMessage();
        }
    }
    
    header("Location: dashboard_admin.php");
    exit();
}

// recuperer toutes les donnees pour l'admin
$utilisateurs = $pdo->query("SELECT * FROM utilisateurs ORDER BY type_utilisateur, nom, prenom")->fetchAll();

$clients = $pdo->query("
    SELECT u.*, cl.telephone, cl.ville, cl.carte_etudiant, cl.date_naissance
    FROM utilisateurs u
    LEFT JOIN clients cl ON u.id_utilisateur = cl.id_client
    WHERE u.type_utilisateur = 'client'
    ORDER BY u.nom, u.prenom
")->fetchAll();

$coachs = $pdo->query("
    SELECT u.*, c.bureau, c.telephone_bureau, c.specialite_principale, c.statut_disponibilite, c.date_embauche
    FROM utilisateurs u
    JOIN coachs c ON u.id_utilisateur = c.id_coach
    WHERE u.type_utilisateur = 'coach'
    ORDER BY u.nom, u.prenom
")->fetchAll();

$cartes_paiement = $pdo->query("
    SELECT cp.*, u.nom, u.prenom 
    FROM cartes_paiement cp
    JOIN utilisateurs u ON cp.id_client = u.id_utilisateur
    ORDER BY cp.date_ajout DESC
")->fetchAll();

$activites = $pdo->query("
    SELECT a.*, c.nom_categorie 
    FROM activites_sportives a
    LEFT JOIN categories_sport c ON a.id_categorie = c.id_categorie
    ORDER BY c.nom_categorie, a.nom_activite
")->fetchAll();

$rendez_vous = $pdo->query("
    SELECT rdv.*, a.nom_activite, a.prix,
           uc.nom AS nom_coach, uc.prenom AS prenom_coach,
           ucl.nom AS nom_client, ucl.prenom AS prenom_client,
           c.bureau
    FROM rendez_vous rdv
    LEFT JOIN activites_sportives a ON rdv.id_activite = a.id_activite
    LEFT JOIN coachs c ON rdv.id_coach = c.id_coach
    LEFT JOIN utilisateurs uc ON c.id_coach = uc.id_utilisateur
    LEFT JOIN utilisateurs ucl ON rdv.id_client = ucl.id_utilisateur
    ORDER BY rdv.date_rdv DESC
")->fetchAll();

$paiements = $pdo->query("
    SELECT p.*, a.nom_activite,
           uc.nom AS nom_coach, uc.prenom AS prenom_coach,
           ucl.nom AS nom_client, ucl.prenom AS prenom_client
    FROM paiements p
    LEFT JOIN rendez_vous rdv ON p.id_rdv = rdv.id_rdv
    LEFT JOIN activites_sportives a ON rdv.id_activite = a.id_activite
    LEFT JOIN coachs c ON rdv.id_coach = c.id_coach
    LEFT JOIN utilisateurs uc ON c.id_coach = uc.id_utilisateur
    LEFT JOIN utilisateurs ucl ON rdv.id_client = ucl.id_utilisateur
    ORDER BY p.date_paiement DESC
")->fetchAll();

// gestion des messages
$message = '';
$messageType = '';
if (isset($_SESSION['success'])) {
    $message = $_SESSION['success'];
    $messageType = 'success';
    unset($_SESSION['success']);
}
if (isset($_SESSION['error'])) {
    $message = $_SESSION['error'];
    $messageType = 'error';
    unset($_SESSION['error']);
}

function getStatutClass($statut) {
    switch ($statut) {
        case 'confirme':
        case 'reussi':
        case 'disponible':
            return 'available';
        case 'annule':
        case 'echec':
        case 'absent':
            return 'busy';
        default:
            return 'busy';
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Sportify</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <div class="logo">
                <div><img src="images/logov2.png" class="logo-icon"></div>
                <span class="logo-text">SPORTIFY ADMIN</span> 
            </div>
            
            <ul class="nav-menu">
                <li><a href="accueil.php">Accueil</a></li>
                <li><a href="dashboard_admin.php">Dashboard Admin</a></li>
                <li><a href="votre_compte.php">Votre Compte</a></li>
            </ul>
            
            <div class="nav-auth">
                <div class="cta-wrapper">
                    <button class="cta-button" onclick="window.location.href='ajouter_donnees.php'">Ajouter</button>
                    <button class="cta-button" onclick="window.location.href='modifier_donnees.php'">Modifier</button>
                </div>
            </div>
        </nav>
    </header>

    <div class="main-background">
        <main class="main-content">
            <section class="event-section">
                <h2 class="event-title">Dashboard Administrateur</h2>
                
                <?php if ($message): ?>
                    <div class="event-content" style="text-align: center; margin-bottom: 2rem;">
                        <p style="color: <?= $messageType === 'success' ? '#00ff88' : '#ff4444' ?>; font-weight: bold;">
                            <?= htmlspecialchars($message) ?>
                        </p>
                    </div>
                <?php endif; ?>

                <!-- Section Utilisateurs -->
                <div class="event-content">
                    <h3 style="color: #00ff88; margin-bottom: 2rem; font-size: 1.5rem;">Tous les Utilisateurs</h3>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nom</th>
                                    <th>Prénom</th>
                                    <th>Email</th>
                                    <th>Type</th>
                                    <th>Date création</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($utilisateurs as $user): ?>
                                    <tr>
                                        <td><?= $user['id_utilisateur'] ?></td>
                                        <td><?= htmlspecialchars($user['nom']) ?></td>
                                        <td><?= htmlspecialchars($user['prenom']) ?></td>
                                        <td><?= htmlspecialchars($user['email']) ?></td>
                                        <td><span class="availability-status <?= $user['type_utilisateur'] === 'admin' ? 'available' : 'busy' ?>"><?= htmlspecialchars($user['type_utilisateur']) ?></span></td>
                                        <td><?= date('d/m/Y', strtotime($user['date_creation'])) ?></td>
                                        <td><span class="availability-status <?= $user['actif'] ? 'available' : 'busy' ?>"><?= $user['actif'] ? 'Actif' : 'Inactif' ?></span></td>
                                        <td>
                                            <button class="service-button" onclick="confirmerSuppression('user', <?= $user['id_utilisateur'] ?>)" style="background-color: #ff4444; border-color: #ff4444;">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section Clients -->
                <div class="event-content" style="margin-top: 3rem;">
                    <h3 style="color: #00ff88; margin-bottom: 2rem; font-size: 1.5rem;">Clients</h3>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Téléphone</th>
                                    <th>Ville</th>
                                    <th>Carte étudiant</th>
                                    <th>Date naissance</th>
                                    <th>Inscription</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($clients as $client): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($client['prenom'] . ' ' . $client['nom']) ?></td>
                                        <td><?= htmlspecialchars($client['email']) ?></td>
                                        <td><?= htmlspecialchars($client['telephone'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($client['ville'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($client['carte_etudiant'] ?? 'N/A') ?></td>
                                        <td><?= $client['date_naissance'] ? date('d/m/Y', strtotime($client['date_naissance'])) : 'N/A' ?></td>
                                        <td><?= date('d/m/Y', strtotime($client['date_creation'])) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section Coachs -->
                <div class="event-content" style="margin-top: 3rem;">
                    <h3 style="color: #00ff88; margin-bottom: 2rem; font-size: 1.5rem;">Coachs</h3>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Email</th>
                                    <th>Bureau</th>
                                    <th>Téléphone bureau</th>
                                    <th>Spécialité</th>
                                    <th>Disponibilité</th>
                                    <th>Date embauche</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($coachs as $coach): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($coach['prenom'] . ' ' . $coach['nom']) ?></td>
                                        <td><?= htmlspecialchars($coach['email']) ?></td>
                                        <td><?= htmlspecialchars($coach['bureau']) ?></td>
                                        <td><?= htmlspecialchars($coach['telephone_bureau']) ?></td>
                                        <td><?= htmlspecialchars($coach['specialite_principale']) ?></td>
                                        <td><span class="availability-status <?= getStatutClass($coach['statut_disponibilite']) ?>"><?= htmlspecialchars($coach['statut_disponibilite']) ?></span></td>
                                        <td><?= $coach['date_embauche'] ? date('d/m/Y', strtotime($coach['date_embauche'])) : 'N/A' ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section Activités -->
                <div class="event-content" style="margin-top: 3rem;">
                    <h3 style="color: #00ff88; margin-bottom: 2rem; font-size: 1.5rem;">Activités Sportives</h3>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nom</th>
                                    <th>Catégorie</th>
                                    <th>Prix</th>
                                    <th>Durée</th>
                                    <th>Description</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($activites as $activite): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($activite['nom_activite']) ?></td>
                                        <td><?= htmlspecialchars($activite['nom_categorie'] ?? 'N/A') ?></td>
                                        <td><?= number_format($activite['prix'], 2) ?> €</td>
                                        <td><?= $activite['duree_seance'] ?> min</td>
                                        <td><?= htmlspecialchars(substr($activite['description'] ?? '', 0, 50)) ?>...</td>
                                        <td><span class="availability-status <?= $activite['actif'] ? 'available' : 'busy' ?>"><?= $activite['actif'] ? 'Actif' : 'Inactif' ?></span></td>
                                        <td>
                                            <button class="service-button" onclick="confirmerSuppression('activity', <?= $activite['id_activite'] ?>)" style="background-color: #ff4444; border-color: #ff4444;">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section Rendez-vous -->
                <div class="event-content" style="margin-top: 3rem;">
                    <h3 style="color: #00ff88; margin-bottom: 2rem; font-size: 1.5rem;">Rendez-vous</h3>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date RDV</th>
                                    <th>Client</th>
                                    <th>Coach</th>
                                    <th>Activité</th>
                                    <th>Bureau</th>
                                    <th>Durée</th>
                                    <th>Prix</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rendez_vous as $rdv): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($rdv['date_rdv'])) ?></td>
                                        <td><?= htmlspecialchars(($rdv['prenom_client'] ?? '') . ' ' . ($rdv['nom_client'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars(($rdv['prenom_coach'] ?? '') . ' ' . ($rdv['nom_coach'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars($rdv['nom_activite'] ?? 'N/A') ?></td>
                                        <td><?= htmlspecialchars($rdv['bureau'] ?? 'N/A') ?></td>
                                        <td><?= intval($rdv['duree']) ?> min</td>
                                        <td><?= number_format($rdv['prix'] ?? 0, 2) ?> €</td>
                                        <td><span class="availability-status <?= getStatutClass($rdv['statut']) ?>"><?= htmlspecialchars($rdv['statut']) ?></span></td>
                                        <td>
                                            <button class="service-button" onclick="confirmerSuppression('rdv', <?= $rdv['id_rdv'] ?>)" style="background-color: #ff4444; border-color: #ff4444;">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section Paiements -->
                <div class="event-content" style="margin-top: 3rem;">
                    <h3 style="color: #00ff88; margin-bottom: 2rem; font-size: 1.5rem;">Paiements</h3>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Client</th>
                                    <th>Coach</th>
                                    <th>Activité</th>
                                    <th>Montant</th>
                                    <th>Type carte</th>
                                    <th>Référence</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($paiements as $paiement): ?>
                                    <tr>
                                        <td><?= date('d/m/Y H:i', strtotime($paiement['date_paiement'])) ?></td>
                                        <td><?= htmlspecialchars(($paiement['prenom_client'] ?? '') . ' ' . ($paiement['nom_client'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars(($paiement['prenom_coach'] ?? '') . ' ' . ($paiement['nom_coach'] ?? '')) ?></td>
                                        <td><?= htmlspecialchars($paiement['nom_activite'] ?? 'N/A') ?></td>
                                        <td><?= number_format($paiement['montant'], 2) ?> €</td>
                                        <td><?= strtoupper($paiement['type_carte']) ?></td>
                                        <td><?= htmlspecialchars($paiement['reference_transaction'] ?? 'N/A') ?></td>
                                        <td><span class="availability-status <?= getStatutClass($paiement['statut_paiement']) ?>"><?= htmlspecialchars($paiement['statut_paiement']) ?></span></td>
                                        <td>
                                            <button class="service-button" onclick="confirmerSuppression('payment', <?= $paiement['id_paiement'] ?>)" style="background-color: #ff4444; border-color: #ff4444;">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Section Cartes de paiement -->
                <div class="event-content" style="margin-top: 3rem;">
                    <h3 style="color: #00ff88; margin-bottom: 2rem; font-size: 1.5rem;">Cartes de Paiement</h3>
                    
                    <div class="table-container">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Client</th>
                                    <th>Type carte</th>
                                    <th>Numéro masqué</th>
                                    <th>Nom carte</th>
                                    <th>Date expiration</th>
                                    <th>Date ajout</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cartes_paiement as $carte): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($carte['prenom'] . ' ' . $carte['nom']) ?></td>
                                        <td><?= strtoupper($carte['type_carte']) ?></td>
                                        <td><?= htmlspecialchars($carte['numero_carte_masque']) ?></td>
                                        <td><?= htmlspecialchars($carte['nom_carte']) ?></td>
                                        <td><?= $carte['date_expiration'] && $carte['date_expiration'] !== '0000-00-00' ? date('m/Y', strtotime($carte['date_expiration'])) : 'Non renseignée' ?></td>
                                        <td><?= date('d/m/Y', strtotime($carte['date_ajout'])) ?></td>
                                        <td>
                                            <button class="service-button" onclick="confirmerSuppression('card', <?= $carte['id_carte'] ?>)" style="background-color: #ff4444; border-color: #ff4444;">Supprimer</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Statistiques -->
                <div class="event-content" style="margin-top: 3rem;">
                    <h3 style="color: #00ff88; margin-bottom: 2rem; font-size: 1.5rem;">Statistiques</h3>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                        <div class="service-card" style="text-align: center;">
                            <h4 style="color: #00ff88; margin: 0;">Total Utilisateurs</h4>
                            <p style="font-size: 2rem; margin: 0.5rem 0; color: #fff;"><?= count($utilisateurs) ?></p>
                        </div>
                        <div class="service-card" style="text-align: center;">
                            <h4 style="color: #00ff88; margin: 0;">Clients</h4>
                            <p style="font-size: 2rem; margin: 0.5rem 0; color: #fff;"><?= count($clients) ?></p>
                        </div>
                        <div class="service-card" style="text-align: center;">
                            <h4 style="color: #00ff88; margin: 0;">Coachs</h4>
                            <p style="font-size: 2rem; margin: 0.5rem 0; color: #fff;"><?= count($coachs) ?></p>
                        </div>
                        <div class="service-card" style="text-align: center;">
                            <h4 style="color: #00ff88; margin: 0;">RDV Total</h4>
                            <p style="font-size: 2rem; margin: 0.5rem 0; color: #fff;"><?= count($rendez_vous) ?></p>
                        </div>
                        <div class="service-card" style="text-align: center;">
                            <h4 style="color: #00ff88; margin: 0;">CA Total</h4>
                            <p style="font-size: 2rem; margin: 0.5rem 0; color: #fff;"><?= number_format(array_sum(array_column($paiements, 'montant')), 2) ?> €</p>
                        </div>
                        <div class="service-card" style="text-align: center;">
                            <h4 style="color: #00ff88; margin: 0;">Cartes Enregistrées</h4>
                            <p style="font-size: 2rem; margin: 0.5rem 0; color: #fff;"><?= count($cartes_paiement) ?></p>
                        </div>
                    </div>
                </div>
            </section>
        </main>
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
</body>
</html>