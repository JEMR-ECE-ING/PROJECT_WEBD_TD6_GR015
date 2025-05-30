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

$categorie = $_GET['categorie'] ?? '';

function getIdCategorie(PDO $pdo, string $nomCategorie): ?int {
    $stmt = $pdo->prepare("SELECT id_categorie FROM categories_sport WHERE nom_categorie = ?");
    $stmt->execute([$nomCategorie]);
    $row = $stmt->fetch();
    return $row ? (int)$row['id_categorie'] : null;
}

function getActivitesEtCoachs(PDO $pdo, int $idCategorie): array {
    $stmt = $pdo->prepare("
        SELECT 
            a.id_activite,
            a.nom_activite,
            a.description AS desc_activite,
            a.prix,
            a.duree_seance,
            c.id_coach,
            c.telephone_bureau,
            c.photo_url,
            c.video_url,
            c.statut_disponibilite,
            c.bureau,
            u.nom AS nom_coach,
            u.prenom AS prenom_coach,
            u.email AS email_coach
        FROM activites_sportives a
        LEFT JOIN coachs c ON c.specialite_principale = a.nom_activite
        LEFT JOIN utilisateurs u ON u.id_utilisateur = c.id_coach
        WHERE a.id_categorie = ?
        ORDER BY a.nom_activite
    ");
    $stmt->execute([$idCategorie]);
    return $stmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Sportify - Tout Parcourir</title>
    <link rel="stylesheet" href="style.css" />
    <script>
    function showCategory(cat) {
        window.location.href = 'tout_parcourir.php?categorie=' + encodeURIComponent(cat);
    }
    </script>
</head>
<body>
<header class="header">
    <nav class="nav-container">
        <div class="logo">
            <div><img src="images/logov2.png" class="logo-icon" alt="Logo Sportify"></div>
            <span class="logo-text">SPORTIFY</span> 
        </div>
        <ul class="nav-menu">
            <li><a href="accueil.php">Accueil</a></li>
            <li><a href="tout_parcourir.php" class="active">Tout Parcourir</a></li>
            <li><a href="#rdv">Rendez-vous</a></li>
            <li><a href="votre_compte.php">Votre Compte</a></li>
        </ul>
        <div class="nav-auth">
            <div class="cta-wrapper">
                <button class="cta-button" onclick="window.location.href='formulaire_inscription.php'">
                    Créer un compte
                </button>
                <button class="cta-button" onclick="window.location.href='formulaire_connexion.php'">
                    Se connecter
                </button>
            </div>
        </div>
    </nav>
</header>

<div class="main-background">
    <?php if (!$categorie): ?>
        <section class="services-section">
            <h2 class="services-title">NOS SERVICES</h2>
            <div class="services-grid">
                <div class="service-card" onclick="showCategory('activites_sportives')">
                    <div><img src="images/muscu.png" class="service-icon" alt=""></div>
                    <h3>Activités Sportives</h3>
                    <p>Musculation, Fitness, Biking, Cardio-Training, Cours Collectifs</p>
                    <button class="cta-button">Voir plus</button>
                </div>

                <div class="service-card" onclick="showCategory('sports_competition')">
                    <div><img src="images/trophe.png" class="service-icon" alt=""></div>
                    <h3>Sports de Compétition</h3>
                    <p>Basketball, Football, Rugby, Tennis, Natation, Plongeon</p>
                    <button class="cta-button">Voir plus</button>
                </div>

                <div class="service-card" onclick="window.location.href='regles.php'">
                    <div><img src="images/omnes.png" class="service-icon" alt=""></div>
                    <h3>Salle de Sport Omnes</h3>
                    <p>Équipements, Horaires, Règles d'utilisation, Nouveaux clients</p>
                    <button class="cta-button">Voir plus</button>
                </div>
            </div>
        </section>
    <?php else:
        $idCategorie = getIdCategorie($pdo, $categorie);
        if (!$idCategorie):
            echo "<p style='color:red; text-align:center;'>Catégorie inconnue.</p>";
        else:
            $activites = getActivitesEtCoachs($pdo, $idCategorie);
            if (empty($activites)):
                echo "<p style='text-align:center;'>Aucune activité trouvée pour cette catégorie.</p>";
            else:
    ?>
        <section class="services-section">
            <h2 class="services-title">
                <?= $categorie === 'activites_sportives'
                    ? 'Activités Sportives'
                    : 'Sports de Compétition' ?>
            </h2>
            <div class="services-grid">
                <?php foreach ($activites as $act): ?>
                <div class="service-card">
                    <h3 style="color:#00ff88;"><?= htmlspecialchars($act['nom_activite']) ?></h3>
                    <p><?= nl2br(htmlspecialchars($act['desc_activite'])) ?></p>
                    <p><strong>Prix :</strong> <?= number_format($act['prix'], 2) ?> €</p>
                    <p><strong>Durée :</strong> <?= intval($act['duree_seance']) ?> min</p>
                    <hr style="border-color:#00ff88; margin:1rem 0;">
                    <h4>Coach associé :</h4>
                    <?php if ($act['id_coach']): ?>
                        <p><strong>Nom :</strong>
                          <?= htmlspecialchars($act['prenom_coach'] . ' ' . $act['nom_coach']) ?>
                        </p>
                        <p><strong>Email :</strong>
                          <?= htmlspecialchars($act['email_coach']) ?>
                        </p>
                        <p><strong>Téléphone :</strong>
                          <?= htmlspecialchars($act['telephone_bureau']) ?>
                        </p>
                        <p><strong>Bureau :</strong>
                          <?= htmlspecialchars($act['bureau']) ?>
                        </p>

                        <!-- NOUVEAU BOUTON "Prendre RDV" -->
                        <button
                          class="cta-button"
                          onclick="window.location.href=
                            'mes_rendezvous.php?'
                            + 'id_activite=<?= $act['id_activite'] ?>'
                            + '&id_coach=<?= $act['id_coach'] ?>'
                          ">
                          Prendre RDV
                        </button>
                    <?php else: ?>
                        <p>Aucun coach associé trouvé.</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php
            endif;
        endif;
    endif;
    ?>
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
            <p>Lundi–Vendredi : 8h00–20h00</p>
            <p>Samedi : 9h00–18h00</p>
            <p>Dimanche : 10h00–16h00</p>
        </div>
        <div class="footer-section">
            <h3>Liens Rapides</h3>
            <a href="#activites">Activités Sportives</a><br>
            <a href="#competition">Sports de Compétition</a><br>
            <a href="#salle">Salle de Sport</a><br>
            <a href="#rdv">Mes Rendez-vous</a>
        </div>
        <div class="footer-section">
            <h3>Localisation</h3>
            <iframe
                src="https://maps.google.com/maps?q=10%20Rue%20Sextius%20Michel%2C%20750006%20Paris&hl=fr&z=15&output=embed"
                width="100%" height="200" style="border:0; border-radius:8px;"
                allowfullscreen="" loading="lazy">
            </iframe>
        </div>
    </div>
    <div class="footer-bottom">
        <p>&copy; 2025 Sportify – Omnes Education. Tous droits réservés.</p>
    </div>
</footer>
</body>
</html>
