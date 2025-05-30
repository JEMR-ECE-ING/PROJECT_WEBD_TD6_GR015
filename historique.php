<?php
session_start();

if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: formulaire_connexion.php");
    exit();
}

$id_utilisateur = $_SESSION['id_utilisateur'];

// Connexion PDO
$host     = 'localhost';
$dbname   = 'sportify';
$user     = 'root';
$password = '';
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user, $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    // 1) Historique des rendez-vous
    $stmtRDV = $pdo->prepare("
        SELECT 
            rdv.date_rdv,
            rdv.statut,
            a.nom_activite,
            u.prenom   AS prenom_coach,
            u.nom      AS nom_coach,
            c.bureau
        FROM rendez_vous rdv
        LEFT JOIN activites_sportives a ON rdv.id_activite = a.id_activite
        LEFT JOIN coachs c              ON rdv.id_coach    = c.id_coach
        LEFT JOIN utilisateurs u        ON c.id_coach      = u.id_utilisateur
        WHERE rdv.id_client = ?
        ORDER BY rdv.date_rdv DESC
    ");
    $stmtRDV->execute([$id_utilisateur]);
    $historiqueRDV = $stmtRDV->fetchAll();

    // 2) Historique des paiements
    $stmtPay = $pdo->prepare("
        SELECT 
            p.date_paiement,
            p.montant,
            p.type_carte,
            p.reference_transaction,
            p.statut_paiement,
            a.nom_activite,
            u.prenom   AS prenom_coach,
            u.nom      AS nom_coach
        FROM paiements p
        LEFT JOIN rendez_vous rdv         ON p.id_rdv = rdv.id_rdv
        LEFT JOIN activites_sportives a   ON rdv.id_activite = a.id_activite
        LEFT JOIN coachs c                ON rdv.id_coach    = c.id_coach
        LEFT JOIN utilisateurs u          ON c.id_coach      = u.id_utilisateur
        WHERE p.id_client = ?
        ORDER BY p.date_paiement DESC
    ");
    $stmtPay->execute([$id_utilisateur]);
    $historiquePay = $stmtPay->fetchAll();

} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Historique - Sportify</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .dashboard-container { max-width:1200px; margin:0 auto; padding:2rem; }
        .section-title {
            color:#00ff88; font-size:2rem; text-align:center;
            margin:2rem 0 1rem; padding-bottom:0.5rem;
            border-bottom:2px solid #00ff88;
        }
        table {
            width:100%; border-collapse:collapse; color:#fff; margin-bottom:3rem;
        }
        th, td {
            padding:0.75rem; border-bottom:1px solid #333; text-align:left;
        }
        thead th { border-bottom:2px solid #00ff88; }
        .back-button {
            display:inline-block; margin-bottom:1.5rem; padding:0.5rem 1rem;
            background:rgba(0,0,0,0.8); border:2px solid #00ff88;
            border-radius:10px; color:#00ff88; text-decoration:none;
            transition:all .3s ease;
        }
        .back-button:hover { background:#00ff88; color:#000; }
    </style>
</head>
<body>
    <!-- En-tête/Nav (inchangé) -->
    <header class="header"> … </header>

    <div class="main-background">
        <main class="dashboard-container">
            <a href="votre_compte.php" class="back-button">&larr; Retour à Mon Compte</a>

            <!-- 1) Rendez-vous -->
            <h2 class="section-title">Historique de mes rendez-vous</h2>
            <?php if (empty($historiqueRDV)): ?>
                <p style="text-align:center; color:#ccc;">Aucun rendez-vous.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date &amp; heure</th>
                            <th>Coach</th>
                            <th>Activité</th>
                            <th>Bureau</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historiqueRDV as $r): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($r['date_rdv'])) ?></td>
                                <td><?= htmlspecialchars($r['prenom_coach'].' '.$r['nom_coach']) ?></td>
                                <td><?= htmlspecialchars($r['nom_activite']) ?></td>
                                <td><?= htmlspecialchars($r['bureau']) ?></td>
                                <td><?= htmlspecialchars($r['statut']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

            <!-- 2) Paiements -->
            <h2 class="section-title">Historique des paiements</h2>
            <?php if (empty($historiquePay)): ?>
                <p style="text-align:center; color:#ccc;">Aucun paiement enregistré.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Montant</th>
                            <th>Carte</th>
                            <th>Référence</th>
                            <th>Statut</th>
                            <th>Activité</th>
                            <th>Coach</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($historiquePay as $p): ?>
                            <tr>
                                <td><?= date('d/m/Y H:i', strtotime($p['date_paiement'])) ?></td>
                                <td><?= number_format($p['montant'],2) ?> €</td>
                                <td><?= strtoupper(htmlspecialchars($p['type_carte'])) ?></td>
                                <td><?= htmlspecialchars($p['reference_transaction']) ?></td>
                                <td><?= htmlspecialchars($p['statut_paiement']) ?></td>
                                <td><?= htmlspecialchars($p['nom_activite']) ?></td>
                                <td><?= htmlspecialchars($p['prenom_coach'].' '.$p['nom_coach']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>

        </main>
    </div>

    <!-- Pied de page (inchangé) -->
    <footer class="footer"> … </footer>
</body>
</html>
