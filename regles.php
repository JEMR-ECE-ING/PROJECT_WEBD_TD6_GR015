<?php
session_start();

// Si vous voulez restreindre l'accès aux utilisateurs connectés, décommentez :
// if (!isset($_SESSION['id_utilisateur'])) {
//     header("Location: formulaire_connexion.php");
//     exit();
// }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Règles de la Salle – Sportify</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .dashboard-container { max-width:1200px; margin:2rem auto; padding:0 1rem; }
    h1, h2 { color: #00ff88; text-align:center; }
    .rules-list { list-style: none; padding:0; margin:2rem auto; max-width:800px; color:#fff; }
    .rules-list li {
      background: rgba(0,0,0,0.8);
      border: 2px solid #00ff88;
      border-radius: 8px;
      padding: 1rem 1.5rem;
      margin-bottom: 1rem;
      font-size: 1.1rem;
    }
    .back-button {
      display:inline-block; margin-bottom:1.5rem;
      padding:0.5rem 1rem; background:rgba(0,0,0,0.8);
      border:2px solid #00ff88; border-radius:10px;
      color:#00ff88; text-decoration:none;
      transition:all .3s ease;
    }
    .back-button:hover { background:#00ff88; color:#000; }
  </style>
</head>
<body>
  <!-- En-tête/Nav (copié de tout_parcourir.php) -->
  <header class="header">
    <nav class="nav-container">
      <div class="logo">
        <div><img src="images/logov2.png" class="logo-icon" alt="Logo"></div>
        <span class="logo-text">SPORTIFY</span>
      </div>
      <ul class="nav-menu">
        <li><a href="accueil.php">Accueil</a></li>
        <li><a href="tout_parcourir.php">Tout Parcourir</a></li>
        <li><a href="mes_rendezvous.php">Rendez-vous</a></li>
        <li><a href="votre_compte.php">Votre Compte</a></li>
      </ul>
      <div class="nav-auth">
        <div class="cta-wrapper">
          <button class="cta-button" onclick="window.location.href='formulaire_connexion.php'">
            Se connecter
          </button>
        </div>
      </div>
    </nav>
  </header>

  <main class="dashboard-container">
    <a href="tout_parcourir.php" class="back-button">&larr; Retour à Tout Parcourir</a>
    <h1>Règles d’utilisation de la salle de sport Omnes</h1>

    <h2>Règles générales</h2>
    <ul class="rules-list">
      <li>Merci de toujours vous présenter à l’accueil avec votre carte d’accès avant d’entrer.</li>
      <li>Port obligatoire de tenue et de chaussures de sport adaptées (pas de chaussures de ville).</li>
      <li>Respectez les horaires d’ouverture : Lundi–Vendredi 8h–20h, Samedi 9h–18h, Dimanche 10h–16h.</li>
      <li>Les mineurs (–16 ans) doivent être accompagnés d’un adulte responsable.</li>
    </ul>

    <h2>Utilisation des machines et équipements</h2>
    <ul class="rules-list">
      <li>Avant chaque utilisation, vérifiez que la machine est en bon état (câbles, poids, sangles).</li>
      <li>Ne sautez pas les instructions d’attache et de réglage : ajustez toujours la hauteur et la charge à votre niveau.</li>
      <li>Après usage, replacez les poids et haltères à leur place pour éviter tout accident.</li>
      <li>Désinfectez les surfaces de contact (poignées, siège, dossier) à l’aide du spray et des lingettes fournis.</li>
    </ul>

    <h2>Comportement et sécurité</h2>
    <ul class="rules-list">
      <li>Ne monopolisez pas une machine plus de 15 minutes si d’autres membres attendent.</li>
      <li>Pas de nourriture ni de boissons sauf bouteilles d’eau fermées.</li>
      <li>En cas de malaise ou de blessure, prévenez immédiatement le personnel ou appelez le numéro d’urgence interne.</li>
      <li>Les téléphones portables doivent être en mode silencieux et utilisés avec discrétion.</li>
    </ul>

    <h2>Visites et premiers rendez-vous</h2>
    <ul class="rules-list">
      <li>Pour les nouveaux clients, une visite guidée est proposée après prise de rendez-vous via le site.</li>
      <li>Les coachs disponibles pour visite sont affichés dans la catégorie « Salle de Sport Omnes ». </li>
      <li>Tout rendez-vous annulé moins de 24 heures à l’avance peut entraîner des frais d’annulation.</li>
    </ul>

    <p style="text-align:center; color:#ccc; margin-top:2rem;">
      Ces règles visent à garantir votre sécurité et la bonne organisation de la salle :contentReference[oaicite:1]{index=1}.
    </p>
  </main>

  <!-- Pied de page (idem tout_parcourir) -->
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
        <p>Lundi-Vendredi : 8h00 - 20h00</p>
        <p>Samedi : 9h00 - 18h00</p>
        <p>Dimanche : 10h00 - 16h00</p>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 Sportify - Omnes Education. Tous droits réservés.</p>
    </div>
  </footer>
</body>
</html>
