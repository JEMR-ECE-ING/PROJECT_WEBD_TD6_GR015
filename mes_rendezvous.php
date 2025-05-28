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
// TRAITEMENT DE LA SUPPRESSION DE RENDEZ-VOUS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_rdv'])) {
    $id_rdv_a_supprimer = intval($_POST['delete_rdv']);

    // Vérifier que ce rendez-vous appartient à l'utilisateur connecté
    $stmtCheck = $pdo->prepare("SELECT id_rdv FROM rendez_vous WHERE id_rdv = ? AND id_client = ?");
    $stmtCheck->execute([$id_rdv_a_supprimer, $id_utilisateur]);
    if ($stmtCheck->fetch()) {
        // Suppression
        $stmtDel = $pdo->prepare("DELETE FROM rendez_vous WHERE id_rdv = ?");
        $stmtDel->execute([$id_rdv_a_supprimer]);
        $_SESSION['success'] = "Rendez-vous supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Vous ne pouvez pas supprimer ce rendez-vous.";
    }
    header("Location: mes_rendezvous.php");
    exit();
}

// Vérifier que l'utilisateur est connecté
$id_utilisateur = $_SESSION['user_id'] ?? null;
if (!$id_utilisateur) {
    header('Location: formulaire_connexion.html');
    exit();
}

// TRAITEMENT DU FORMULAIRE POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_activite = $_POST['id_activite'] ?? null;
    $id_coach = $_POST['id_coach'] ?? null;
    $date_heure_rdv = $_POST['date_heure_rdv'] ?? null;

    // Nettoyage sommaire (à améliorer si besoin)
    $id_activite = intval($id_activite);
    $id_coach = intval($id_coach);
    $date_heure_rdv = trim($date_heure_rdv);

    if (!$id_activite || !$id_coach || !$date_heure_rdv) {
        $_SESSION['error'] = "Veuillez remplir tous les champs du formulaire.";
        header("Location: mes_rendezvous.php");
        exit();
    }

    // Calculer une date complète à partir de "Jeudi 08h00-10h00"
    // Ici on va supposer que "Jeudi" = jeudi de la semaine en cours ou la suivante si déjà passé
    // Pour simplifier, on prendra la date du prochain jour indiqué (Jeudi ou Vendredi)
    $jours_fr = ['Jeudi' => 4, 'Vendredi' => 5]; // jours de la semaine (1 = lundi, 7 = dimanche)

    // Extraire jour et plage horaire
    if (preg_match('/^(Jeudi|Vendredi) (\d{2}h\d{2})-\d{2}h\d{2}$/', $date_heure_rdv, $matches)) {
        $jour = $matches[1];
        $heure_debut = str_replace('h', ':', $matches[2]);

        // Trouver date du prochain jour (Jeudi ou Vendredi)
        $aujourdhui = new DateTime();
        $numero_jour_aujourdhui = (int)$aujourdhui->format('N'); // 1 = lundi ... 7 = dimanche
        $numero_jour_cible = $jours_fr[$jour];

        $diff_jours = $numero_jour_cible - $numero_jour_aujourdhui;
        if ($diff_jours < 0) {
            $diff_jours += 7; // jour suivant dans la semaine prochaine
        }
        $date_rdv_obj = clone $aujourdhui;
        $date_rdv_obj->modify("+$diff_jours days");
        $date_rdv_obj->setTime(intval(explode(':', $heure_debut)[0]), intval(explode(':', $heure_debut)[1]), 0);

        $date_rdv_formatted = $date_rdv_obj->format('Y-m-d H:i:s');
    } else {
        $_SESSION['error'] = "Format de date/heure invalide.";
        header("Location: mes_rendezvous.php");
        exit();
    }

    // Durée par défaut (à adapter selon activité ?)
    $duree = 120; // 2h

    // Vérifier qu'aucun rendez-vous ne chevauche le créneau choisi (simple check)
    $stmtCheck = $pdo->prepare("
        SELECT COUNT(*) FROM rendez_vous 
        WHERE id_coach = ? AND date_rdv = ? AND statut = 'confirme'
    ");
    $stmtCheck->execute([$id_coach, $date_rdv_formatted]);
    $count = $stmtCheck->fetchColumn();

    if ($count > 0) {
        $_SESSION['error'] = "Ce créneau est déjà réservé. Veuillez choisir un autre horaire.";
        header("Location: mes_rendezvous.php");
        exit();
    }

    // Insertion du rendez-vous
    $stmtInsert = $pdo->prepare("
        INSERT INTO rendez_vous (id_client, id_coach, id_activite, date_rdv, duree, statut) 
        VALUES (?, ?, ?, ?, ?, 'confirme')
    ");
    $stmtInsert->execute([$id_utilisateur, $id_coach, $id_activite, $date_rdv_formatted, $duree]);

    $_SESSION['success'] = "Rendez-vous enregistré avec succès pour le " . $date_rdv_obj->format('d/m/Y H:i');
    header("Location: mes_rendezvous.php");
    exit();
}

// ---- FIN DU TRAITEMENT POST, AFFICHAGE HTML COMMENCE ----


// Gestion message session affichage
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

$stmtAllRDV = $pdo->prepare("
    SELECT rdv.*, a.nom_activite, c.id_coach, c.bureau, u.nom AS nom_coach, u.prenom AS prenom_coach 
    FROM rendez_vous rdv
    LEFT JOIN activites_sportives a ON rdv.id_activite = a.id_activite
    LEFT JOIN coachs c ON rdv.id_coach = c.id_coach
    LEFT JOIN utilisateurs u ON c.id_coach = u.id_utilisateur
    WHERE rdv.id_client = ? AND rdv.statut = 'confirme'
    ORDER BY rdv.date_rdv ASC
");
$stmtAllRDV->execute([$id_utilisateur]);
$tous_les_rdv = $stmtAllRDV->fetchAll();

// Récupérer prochain rendez-vous
$stmtNextRDV = $pdo->prepare("
    SELECT rdv.*, a.nom_activite, c.id_coach, c.bureau, u.nom AS nom_coach, u.prenom AS prenom_coach 
    FROM rendez_vous rdv
    LEFT JOIN activites_sportives a ON rdv.id_activite = a.id_activite
    LEFT JOIN coachs c ON rdv.id_coach = c.id_coach
    LEFT JOIN utilisateurs u ON c.id_coach = u.id_utilisateur
    WHERE rdv.id_client = ? AND rdv.date_rdv >= NOW() AND rdv.statut = 'confirme'
    ORDER BY rdv.date_rdv ASC
    LIMIT 1
");
$stmtNextRDV->execute([$id_utilisateur]);
$prochain_rdv = $stmtNextRDV->fetch();

// Récupérer activités + catégories
$stmtActivites = $pdo->query("
    SELECT a.*, c.nom_categorie 
    FROM activites_sportives a
    LEFT JOIN categories_sport c ON a.id_categorie = c.id_categorie
    WHERE a.actif = 1
    ORDER BY c.nom_categorie, a.nom_activite
");
$activites = $stmtActivites->fetchAll();

// Récupérer coachs
$stmtCoachs = $pdo->query("
    SELECT c.id_coach, u.nom, u.prenom, c.specialite_principale, c.telephone_bureau, c.bureau 
    FROM coachs c
    JOIN utilisateurs u ON c.id_coach = u.id_utilisateur
");
$coachs = $stmtCoachs->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Mes Rendez-vous - Sportify</title>
    <link rel="stylesheet" href="style.css" />
    <style>
        /* Styles simplifiés pour intégration */
        .main-background {
            background-image: url('images/pcbback.png');
            background-repeat: repeat;
            background-size: 200px 200px;
            background-position: center;
            padding: 2rem 3rem;
            color: #eee;
            min-height: 100vh;
        }
        .event-card {
            border: 2px solid #00ff88;
            border-radius: 15px;
            background: rgba(0,0,0,0.7);
            padding: 1.5rem;
            max-width: 600px;
            margin: 0 auto 2rem;
        }

        .all-rdv-list {
        max-width: 900px;
        margin: 1rem auto;
        color: #eee;
        }

        .rdv-item {
            background: rgba(0,0,0,0.7);
            border: 2px solid #00ff88;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            position: relative;
        }

        .event-card h3 {
            color: #00ff88;
            margin-bottom: 1rem;
        }
        .container-rdv form {
            display: flex;
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            flex-wrap: wrap;
            justify-content: center;
        }
        fieldset {
            border: 2px solid #00ff88;
            border-radius: 15px;
            padding: 1rem;
            flex: 1 1 300px;
            min-width: 280px;
            background: rgba(0,0,0,0.8);
            color: #00ff88;
        }
        legend {
            font-weight: bold;
            font-size: 1.2rem;
            margin-bottom: 1rem;
        }
        label {
            display: flex;
            align-items: center;
            margin-bottom: 0.6rem;
            cursor: pointer;
            color: #ccc;
            user-select: none;
        }
        label input[type="radio"] {
            margin-right: 0.75rem;
            accent-color: #00ff88;
        }
        /* Couleurs catégories */
        .activites_sportives label {
            color: #00a0a0;
        }
        .sports_competition label {
            color: #008000;
        }
        button {
            background-color: #00ff88;
            border: none;
            border-radius: 25px;
            color: #000;
            padding: 0.8rem 2rem;
            font-weight: bold;
            cursor: pointer;
            font-size: 1.1rem;
            transition: background-color 0.3s;
            margin: 2rem auto 0;
            display: block;
            min-width: 200px;
        }
        button:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
        button:hover:not(:disabled) {
            background-color: #00cc6a;
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
            <li><a href="accueil.html">Accueil</a></li>
            <li><a href="tout_parcourir.php">Tout Parcourir</a></li>
            <li><a href="#recherche">Recherche</a></li>
            <li><a href="mes_rendezvous.php">Rendez-vous</a></li>
            <li><a href="votre_compte.php">Votre Compte</a></li>
        </ul>
        <div class="nav-auth">
            <div class="cta-wrapper">
                <button class="cta-button" onclick="window.location.href='formulaire_inscription.html'">Créer un compte</button>
                <button class="cta-button" onclick="window.location.href='formulaire_connexion.html'">Se connecter</button>
            </div>
        </div>
    </nav>
</header>

<div class="main-background">

<?php if ($message): ?>
    <div class="message <?= $messageType ?>" style="text-align:center; margin-bottom:1rem; color:<?= $messageType === 'success' ? '#0f0' : '#f44' ?>;">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<?php if ($prochain_rdv): ?>
    <section class="event-card">
        <h2>Votre prochain rendez-vous</h2>
        <h3><?= htmlspecialchars($prochain_rdv['nom_activite']) ?></h3>
        <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($prochain_rdv['date_rdv'])) ?></p>
        <p><strong>Coach :</strong> <?= htmlspecialchars($prochain_rdv['nom_coach'] . ' ' . $prochain_rdv['prenom_coach']) ?></p>
        <p><strong>Bureau :</strong> <?= htmlspecialchars($prochain_rdv['bureau']) ?></p>
        <p><strong>Durée :</strong> <?= intval($prochain_rdv['duree']) ?> minutes</p>
        <p><strong>Statut :</strong> <?= htmlspecialchars($prochain_rdv['statut']) ?></p>
    </section>
<?php else: ?>
    <p style="text-align:center; color:#ccc;">Vous n'avez pas de rendez-vous à venir.</p>
<?php endif; ?>

<?php if (!empty($tous_les_rdv)): ?>
<section class="all-rdv-list" style="max-width: 900px; margin: 1rem auto; color: #eee;">
    <h2 style="color:#00ff88; text-align:center; margin-bottom:1rem;">Tous mes rendez-vous</h2>
    <?php foreach ($tous_les_rdv as $rdv): ?>
    <div class="rdv-item" style="background:rgba(0,0,0,0.7); border:2px solid #00ff88; border-radius:10px; padding:1rem; margin-bottom:1rem; position:relative;">
        <h3><?= htmlspecialchars($rdv['nom_activite']) ?> - <?= date('d/m/Y H:i', strtotime($rdv['date_rdv'])) ?></h3>
        <p><strong>Coach :</strong> <?= htmlspecialchars($rdv['nom_coach'] . ' ' . $rdv['prenom_coach']) ?></p>
        <p><strong>Bureau :</strong> <?= htmlspecialchars($rdv['bureau'] ?? '') ?></p>
        <p><strong>Durée :</strong> <?= intval($rdv['duree']) ?> minutes</p>
        <p><strong>Statut :</strong> <?= htmlspecialchars($rdv['statut']) ?></p>

        <!-- Formulaire suppression -->
        <form method="POST" style="position:absolute; top:10px; right:10px;" onsubmit="return confirm('Confirmer la suppression de ce rendez-vous ?');">
            <input type="hidden" name="delete_rdv" value="<?= $rdv['id_rdv'] ?>">
            <button type="submit" class="cta-button">Supprimer</button>
        </form>
    </div>
    <?php endforeach; ?>
</section>
<?php endif; ?>

<section class="container-rdv">
    <form method="POST" id="form-rdv">

        <fieldset class="activites_sportives">
            <legend>Choisissez une activité sportive</legend>
            <?php foreach ($activites as $activite): ?>
                <?php $classCat = $activite['nom_categorie'] === 'activites_sportives' ? 'activites_sportives' : 'sports_competition'; ?>
                <label class="<?= $classCat ?>">
                    <input type="radio" name="id_activite" value="<?= $activite['id_activite'] ?>" required onchange="majBoutonValider()">
                    <?= htmlspecialchars($activite['nom_activite']) ?> — <?= number_format($activite['prix'], 2) ?> €
                </label>
            <?php endforeach; ?>
        </fieldset>

        <fieldset id="fieldset-coachs" style="display:none;">
            <legend>Choisissez un coach</legend>
            <div id="coachs-container" style="max-height: 250px; overflow-y: auto; padding-left: 10px;">
                <!-- Les coachs apparaissent ici via JS -->
            </div>
        </fieldset>

        <fieldset id="fieldset-planning" style="display:none;">
            <legend>Choisissez un créneau horaire</legend>
            <div class="planning-group" id="planning-group" style="display:flex; gap:10px; flex-wrap: wrap; justify-content: center;">
                <!-- Planning généré en JS -->
            </div>
        </fieldset>

        <button type="submit" id="btn-valider-rdv" disabled>Confirmer le rendez-vous</button>
    </form>
</section>

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
    </div>

    <div class="footer-bottom">
        <p>&copy; 2025 Sportify - Omnes Education. Tous droits réservés.</p>
    </div>
</footer>

<script>
    // Variables PHP injectées
    const activites = <?= json_encode($activites) ?>;
    const coachs = <?= json_encode($coachs) ?>;

    let activiteSelectionnee = null;
    let coachSelectionne = null;
    let creneauSelectionne = null;

    // Mise à jour bouton valider
    function majBoutonValider() {
        const btn = document.getElementById('btn-valider-rdv');
        btn.disabled = !(activiteSelectionnee && coachSelectionne && creneauSelectionne);
    }

    // Quand on sélectionne une activité
    document.querySelectorAll('input[name="id_activite"]').forEach(radio => {
        radio.addEventListener('change', function() {
            activiteSelectionnee = activites.find(a => a.id_activite == this.value);

            // Affiche le fieldset coachs
            const fsCoachs = document.getElementById('fieldset-coachs');
            fsCoachs.style.display = 'block';

            // Génère la liste des coachs pour cette activité
            const container = document.getElementById('coachs-container');
            container.innerHTML = '';

            const coachsFiltres = coachs.filter(c => c.specialite_principale === activiteSelectionnee.nom_activite);
            if (coachsFiltres.length === 0) {
                container.innerHTML = '<p style="color:#ccc;">Aucun coach disponible pour cette activité.</p>';
                document.getElementById('fieldset-planning').style.display = 'none';
                coachSelectionne = null;
                majBoutonValider();
                return;
            }

            coachsFiltres.forEach(coach => {
                const label = document.createElement('label');
                label.style.color = '#ccc';
                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'id_coach';
                input.value = coach.id_coach;
                input.required = true;
                input.style.marginRight = '0.5rem';

                input.addEventListener('change', () => {
                    coachSelectionne = coach;
                    afficherPlanning();
                    majBoutonValider();
                });

                label.appendChild(input);
                label.appendChild(document.createTextNode(coach.nom + ' ' + coach.prenom + ' (' + coach.bureau + ')'));
                container.appendChild(label);
            });

            // Reset planning & selections
            document.getElementById('fieldset-planning').style.display = 'none';
            creneauSelectionne = null;
            majBoutonValider();
        });
    });

    // Afficher planning et gérer la sélection
    function afficherPlanning() {
        const fsPlanning = document.getElementById('fieldset-planning');
        fsPlanning.style.display = 'block';
        const planningGroup = document.getElementById('planning-group');
        planningGroup.innerHTML = '';

        const jours = ['Jeudi', 'Vendredi'];
        const horaires = ['08h00-10h00', '10h00-12h00', '12h00-14h00', '14h00-16h00', '16h00-18h00'];

        jours.forEach(jour => {
            const dayDiv = document.createElement('div');
            dayDiv.style.flex = '1';
            dayDiv.style.marginRight = '10px';

            const title = document.createElement('strong');
            title.textContent = jour;
            title.style.display = 'block';
            title.style.marginBottom = '10px';
            title.style.color = '#00ff88';
            dayDiv.appendChild(title);

            horaires.forEach(horaire => {
                const label = document.createElement('label');
                label.style.display = 'block';
                label.style.marginBottom = '6px';
                label.style.color = '#ccc';

                const input = document.createElement('input');
                input.type = 'radio';
                input.name = 'date_heure_rdv';
                input.value = jour + ' ' + horaire;
                input.required = true;
                input.style.marginRight = '0.5rem';

                input.addEventListener('change', () => {
                    creneauSelectionne = input.value;
                    majBoutonValider();
                });

                label.appendChild(input);
                label.appendChild(document.createTextNode(horaire));
                dayDiv.appendChild(label);
            });

            planningGroup.appendChild(dayDiv);
        });
    }
</script>

</body>
</html>
