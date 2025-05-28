<?php
session_start();

// Connexion PDO
$action; $host = 'localhost'; $dbname = 'sportify'; $user = 'root'; $password = '';
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user, $password,
        [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
         PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]
    );
} catch (PDOException $e) {
    die("Erreur base de données: " . $e->getMessage());
}

// Récupérer les coachs actifs
$stmtCoachs = $pdo->query(
    "SELECT 
      c.id_coach, u.nom, u.prenom, c.specialite_principale,
      c.bureau, c.telephone_bureau, c.cv_xml, c.statut_disponibilite
    FROM coachs c
    JOIN utilisateurs u ON c.id_coach = u.id_utilisateur
    WHERE u.actif = 1
    ORDER BY u.nom, u.prenom"
);
$coachs = $stmtCoachs->fetchAll();

function genererInitiales($nom, $prenom) {
    return strtoupper(substr($nom,0,1) . substr($prenom,0,1));
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sportify Accueil</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <div class="logo">
                <img src="images/logov2.png" class="logo-icon" alt="Logo">
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
                <button class="cta-button" onclick="window.location.href='formulaire_inscription.php'">Créer un compte</button>
                <button class="cta-button" onclick="window.location.href='formulaire_connexion.php'">Se connecter</button>
            </div>
        </nav>
    </header>

    <div class="main-background">
        <main class="main-content">
            <section class="hero">
                <video autoplay muted loop class="hero-video">
                    <source src="images/video.mp4" type="video/mp4">
                </video>
                <h1>LIBÉREZ VOTRE POTENTIEL SPORTIF</h1>
                <button class="cta-button">Prendre RDV</button>
            </section>

            <section class="event-section">
                <h2 class="event-title">ÉVÉNEMENT DE LA SEMAINE</h2>
                <div class="event-content">
                    <video autoplay muted loop class="hero-video">
                        <source src="images/video2.mp4" type="video/mp4">
                    </video>
                    <img src="images/rugby.png" alt="Match de Rugby" class="event-image">
                    <div class="event-details">
                        <h3>Match de Rugby : Omnes Education vs. Visiteurs</h3>
                        <p>Rejoignez-nous ce samedi pour un match passionnant entre notre équipe Omnes Education et nos visiteurs.</p>
                        <p><strong>Date :</strong> Samedi 31 Mai 2025</p>
                        <p><strong>Heure :</strong> 15h00</p>
                        <p><strong>Lieu :</strong> Terrain de Rugby Omnes</p>
                        <button class="cta-button" onclick="handleRegistration()">S'inscrire</button>
                    </div>
                </div>
            </section>

            <section class="coach-section">
                <h2 class="coach-title">NOS COACHS CERTIFIÉS</h2>
                <div class="coach-carousel">
                    <?php foreach ($coachs as $coach): ?>
                        <div class="coach-card">
                            <div class="coach-avatar"><?= genererInitiales($coach['nom'], $coach['prenom']) ?></div>
                            <div class="coach-name"><?= htmlspecialchars($coach['prenom'] . ' ' . $coach['nom']) ?></div>
                            <div class="coach-specialty"><?= htmlspecialchars($coach['specialite_principale']) ?></div>
                            <div class="coach-bureau">Bureau: <?= htmlspecialchars($coach['bureau']) ?></div>
                            <button class="cta-button" onclick="afficherCv(<?= $coach['id_coach'] ?>)">Voir CV</button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
        <div id="cvModal" class="event-section">
            <div class="event-content">
                <span class="close" onclick="fermerModal()">&times;</spa>
            <div id="cvContent" class="event-details"></div>
        </div>
    </div>
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

    <script>
        const coachsData = <?= json_encode($coachs) ?>;
        function afficherCv(idCoach) {
            const coach = coachsData.find(c => c.id_coach == idCoach);
            let cvData = null;
            if (coach.cv_xml) {
                const parser = new DOMParser();
                const xmlDoc = parser.parseFromString(coach.cv_xml, 'text/xml');
                cvData = {
                    nom:        xmlDoc.querySelector('Nom')?.textContent || '',
                    prenom:     xmlDoc.querySelector('Prenom')?.textContent || '',
                    discipline: xmlDoc.querySelector('Discipline')?.textContent || '',
                    email:      xmlDoc.querySelector('Email')?.textContent || '',
                    telephone:  xmlDoc.querySelector('Telephone')?.textContent || '',
                    formation:  xmlDoc.querySelector('Formation')?.textContent || '',
                    experience: xmlDoc.querySelector('Experience')?.textContent || '',
                    adresse: {
                        rue:        xmlDoc.querySelector('Adresse > Rue')?.textContent || '',
                        codePostal: xmlDoc.querySelector('Adresse > CodePostal')?.textContent || '',
                        ville:      xmlDoc.querySelector('Adresse > Ville')?.textContent || ''
                    }
                };
            }
            let html = `<h2>CV - ${coach.prenom} ${coach.nom}</h2>`;
            if (cvData) {
                html += `
                    <div class="event-detials">
                        <div><h3>Informations personnelles</h3>
                            <p><strong>Nom :</strong> ${cvData.nom}</p>
                            <p><strong>Prénom :</strong> ${cvData.prenom}</p>
                            <p><strong>Email :</strong> ${cvData.email}</p>
                            <p><strong>Téléphone :</strong> ${cvData.telephone}</p>
                        </div>
                        <div><h3>Adresse</h3>
                            <p>${cvData.adresse.rue}</p>
                            <p>${cvData.adresse.codePostal} ${cvData.adresse.ville}</p>
                        </div>
                    </div>
                    <div class="event-details"><h3>Spécialité</h3><p>${cvData.discipline}</p></div>
                    <div class="event-details"><h3>Formation</h3><p>${cvData.formation}</p></div>
                    <div class="event-details"><h3>Expérience</h3><p>${cvData.experience}</p></div>
                `;
            } else {
                html += `
                    <div class="cv-section">
                        <p>CV non disponible.</p>
                        <p><strong>Spécialité :</strong> ${coach.specialite_principale}</p>
                        <p><strong>Bureau :</strong> ${coach.bureau}</p>
                        <p><strong>Téléphone :</strong> ${coach.telephone_bureau || 'N/A'}</p>
                    </div>
                `;
            }
            document.getElementById('cvContent').innerHTML = html;
            document.getElementById('cvModal').style.display = 'block';
        }
        function fermerModal() { document.getElementById('cvModal').style.display = 'none'; }
        window.onclick = e => { if (e.target.id === 'cvModal') fermerModal(); };
        function handleRegistration() { alert('Inscription événement'); }
    </script>
</body>
</html>
