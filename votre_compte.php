<?php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: formulaire_connexion.php");
    exit();
}

// Recuperer les infos de la session
$id_utilisateur = $_SESSION['id_utilisateur'];
$nom = $_SESSION['nom'];
$prenom = $_SESSION['prenom'];
$email = $_SESSION['email'];
$type_utilisateur = $_SESSION['type_utilisateur'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Compte - <?php echo ucfirst($type_utilisateur); ?></title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .welcome-box {
            background: rgba(0,0,0,0.8);
            border: 2px solid #00ff88;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 3rem;
        }
        
        .welcome-title {
            color: #00ff88;
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }
        
        .user-info {
            color: #ffffff;
            font-size: 1.2rem;
            margin-bottom: 0.5rem;
        }
        
        .user-badge {
            display: inline-block;
            background: #00ff88;
            color: #000;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            font-weight: bold;
            margin-top: 1rem;
            text-transform: uppercase;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
            margin-bottom: 3rem;
        }
        
        .action-card {
            background: rgba(0,0,0,0.8);
            border: 2px solid #00ff88;
            border-radius: 15px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.2);
        }
        
        .action-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .action-card h3 {
            color: #ffffff;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }
        
        .action-card p {
            color: #cccccc;
            margin-bottom: 1.5rem;
        }
        
        .section-title {
            color: #00ff88;
            font-size: 2rem;
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #00ff88;
        }
        
        .logout-button {
            position: absolute;
            right: 20px;
            background: transparent;
            border: 2px solid #ff4444;
            color: #ff4444;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .logout-button:hover {
            background: #ff4444;
            color: #ffffff;
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
        <main class="dashboard-container">
            <h1 class="page-title" style="color: #00ff88; font-size: 2.5rem; text-align: center; margin-bottom: 3rem;">
                Mon Espace <?php echo ucfirst($type_utilisateur); ?>
            </h1>
            
            <!-- Section de bienvenue -->
            <div class="welcome-box">
                <h2 class="welcome-title">Bienvenue, <?php echo htmlspecialchars($prenom . ' ' . $nom); ?> !</h2>
                <p class="user-info"><?php echo htmlspecialchars($email); ?></p>
                <span class="user-badge"><?php echo $type_utilisateur; ?></span>
            </div>

            <div class="actions-grid">
                <div class="action-card" onclick="window.location.href='mes_rendezvous.php'">
                    <h3>Mes Rendez-vous</h3>
                    <p>Consultez et gérez vos rendez-vous</p>
                    <button class="cta-button">Accéder</button>
                </div>
                
                <div class="action-card" onclick="window.location.href='formulaire_bancaire.php'">
                    <h3>Moyens de Paiement</h3>
                    <p>Gérez vos cartes et moyens de paiement</p>
                    <button class="cta-button">Gérer</button>
                </div>
            </div>

            <?php if ($type_utilisateur == 'admin'): ?>
            <!-- Section Admin -->
            <h2 class="section-title">Administration</h2>
            <div class="actions-grid">
                <div class="action-card" onclick="window.location.href='admin/gestion_coachs.php'">
                    <h3>Gestion des Coachs</h3>
                    <p>Ajouter, modifier ou supprimer des coachs</p>
                    <button class="cta-button">Gérer</button>
                </div>
                
                <div class="action-card" onclick="window.location.href='dashboard_admin.php'">
                    <h3>Gestion des Activités</h3>
                    <p>Gérer les activités sportives</p>
                    <button class="cta-button">Gérer</button>
                </div>
                
                <div class="action-card" onclick="window.location.href='admin/statistiques.php'">
                    <h3>Statistiques</h3>
                    <p>Voir les statistiques détaillées</p>
                    <button class="cta-button">Voir</button>
                </div>
                
                <div class="action-card" onclick="window.location.href='admin/gestion_salle.php'">
                    <h3>Gestion Salle de Sport</h3>
                    <p>Gérer les informations de la salle</p>
                    <button class="cta-button">Gérer</button>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($type_utilisateur == 'coach'): ?>
            <!-- Section Coach -->
            <h2 class="section-title">Espace Coach</h2>
            <div class="actions-grid">
                <div class="action-card" onclick="window.location.href='dashboard_admin.php'">
                    <h3>Mon Planning</h3>
                    <p>Gérer mes disponibilités</p>
                    <button class="cta-button">Voir</button>
                </div>
                
                <div class="action-card" onclick="window.location.href='coach/messages.php'">
                    <h3>Messages Clients</h3>
                    <p>Communiquer avec mes clients</p>
                    <button class="cta-button">Ouvrir</button>
                </div>
                
                <div class="action-card" onclick="window.location.href='coach/mon_cv.php'">
                    <h3>Mon CV</h3>
                    <p>Mettre à jour mon profil</p>
                    <button class="cta-button">Modifier</button>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($type_utilisateur == 'client'): ?>
            <!-- Section Client -->
            <h2 class="section-title">Mon Espace Personnel</h2>
            <div class="actions-grid">
                <div class="action-card" onclick="window.location.href='recherche_coach.php'">
                    <h3>Rechercher un Coach</h3>
                    <p>Trouvez le coach idéal pour vos objectifs</p>
                    <button class="cta-button">Rechercher</button>
                </div>
                
                <div class="action-card" onclick="window.location.href='dashboard_admin.php'">
                    <h3>Mon Historique</h3>
                    <p>Consultez vos séances passées</p>
                    <button class="cta-button">Consulter</button>
                </div>
                
                <div class="action-card" onclick="window.location.href='votre_compte.php'">
                    <h3>Mon Profil</h3>
                    <p>Mettre à jour mes informations</p>
                    <button class="cta-button">Modifier</button>
                </div>
            </div>
            <?php endif; ?>
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
                        src="https://maps.google.com/maps?q=10%20Rue%20Sextius%20Michel%2C%20750006%20Paris&hl=fr&z=15&output=embed"                        width="100%"
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