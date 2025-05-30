<?php
session_start();
if (!isset($_SESSION['id_utilisateur'])) {
    header("Location: formulaire_connexion.php");
    exit();
}
$id_utilisateur = $_SESSION['id_utilisateur'];

// Connexion PDO
$pdo = new PDO(
    "mysql:host=localhost;dbname=sportify;charset=utf8mb4",
    'root', '',
    [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

// Gestion AJAX du chat
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];

    // Récupérer les messages
    if ($action === 'fetch_messages' && isset($_GET['coach_id'])) {
        $coach_id = (int)$_GET['coach_id'];
        $stmt = $pdo->prepare("
            SELECT from_user, to_user, message, sent_at
            FROM messages
            WHERE (from_user = :me AND to_user = :coach)
               OR (from_user = :coach AND to_user = :me)
            ORDER BY sent_at ASC
        ");
        $stmt->execute(['me'=>$id_utilisateur, 'coach'=>$coach_id]);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    // Envoyer un nouveau message
    if ($action === 'send_message' && $_SERVER['REQUEST_METHOD']==='POST') {
        $data     = json_decode(file_get_contents('php://input'), true);
        $coach_id = (int)$data['coach_id'];
        $msg      = trim($data['message']);
        if ($msg !== '') {
            $stmt = $pdo->prepare("
                INSERT INTO messages (from_user, to_user, message, sent_at)
                VALUES (:from, :to, :msg, NOW())
            ");
            $stmt->execute([
                'from' => $id_utilisateur,
                'to'   => $coach_id,
                'msg'  => $msg
            ]);
            echo json_encode(['status'=>'ok']);
        } else {
            echo json_encode(['status'=>'empty']);
        }
        exit;
    }

    echo json_encode(['error'=>'action inconnue']);
    exit;
}

// Requête de liste des coachs
$search = trim($_GET['search'] ?? '');
$params = [];
$sql = "
    SELECT 
      c.id_coach,
      u.prenom,
      u.nom,
      c.specialite_principale AS nom_activite
    FROM coachs c
    JOIN utilisateurs u ON c.id_coach = u.id_utilisateur
";
if ($search !== '') {
    $sql .= "
      WHERE u.nom LIKE :q 
         OR u.prenom LIKE :q 
         OR c.specialite_principale LIKE :q
    ";
    $params['q'] = "%$search%";
}
$sql .= " ORDER BY u.nom ";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$coachs = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Rechercher un Coach – Sportify</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .dashboard-container { max-width:1200px; margin:0 auto; padding:2rem; }
    .page-title { color:#00ff88; text-align:center; margin-bottom:1.5rem; }
    .search-form { text-align:center; margin-bottom:1.5rem; }
    .search-form input { padding:0.5rem; width:250px; }
    .search-form button { padding:0.5rem 1rem; background:#00ff88; border:none; cursor:pointer; }
    table { width:100%; border-collapse:collapse; color:#fff; margin-bottom:2rem; }
    th, td { padding:0.75rem; border-bottom:1px solid #333; text-align:left; }
    thead th { border-bottom:2px solid #00ff88; }
    .chat-box { display:none; margin-top:2rem; }
    #chat-header { color:#00ff88; margin-bottom:1rem; }
    #chat-messages {
      background:rgba(0,0,0,0.8); height:300px; overflow-y:auto;
      padding:1rem; margin-bottom:1rem; color:#ccc;
    }
    .message { margin-bottom:0.5rem; }
    .message.mine { text-align:right; color:#fff; }
    a.back-button {
      display:inline-block; margin-bottom:1.5rem;
      padding:0.5rem 1rem; background:rgba(0,0,0,0.8);
      border:2px solid #00ff88; border-radius:10px;
      color:#00ff88; text-decoration:none;
      transition:all .3s ease;
    }
    a.back-button:hover { background:#00ff88; color:#000; }
  </style>
</head>
<body>
  <!-- ======= En-tête/Nav ======= -->
  <header class="header">
    <nav class="nav-container">
      <div class="logo">
        <img src="images/logov2.png" class="logo-icon" alt="Logo">
        <span class="logo-text">SPORTIFY</span>
      </div>
      <ul class="nav-menu">
        <li><a href="accueil.php">Accueil</a></li>
        <li><a href="tout_parcourir.php">Tout Parcourir</a></li>
        <li><a href="mes_rendezvous.php">Rendez-vous</a></li>
        <li><a href="votre_compte.php">Mon Compte</a></li>
        <li><a href="recherche_coach.php" class="active">Rechercher un Coach</a></li>
      </ul>
      <div class="nav-auth">
        <button class="cta-button" onclick="window.location.href='partie_php/traitement_logout.php'">
          Déconnexion
        </button>
      </div>
    </nav>
  </header>

  <main class="dashboard-container">
    <a href="votre_compte.php" class="back-button">&larr; Retour à Mon Compte</a>
    <h1 class="page-title">Rechercher un Coach</h1>

    <!-- Barre de recherche -->
    <form class="search-form" method="get">
      <input
        type="text" name="search"
        placeholder="Nom de coach ou spécialité…"
        value="<?= htmlspecialchars($search) ?>"
      >
      <button type="submit">Rechercher</button>
    </form>

    <!-- Liste des coaches -->
    <table>
      <thead>
        <tr><th>Nom</th><th>Spécialité</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php if (empty($coachs)): ?>
          <tr><td colspan="3" style="text-align:center;color:#ccc;">
            Aucun coach trouvé.
          </td></tr>
        <?php else: ?>
          <?php foreach ($coachs as $c): ?>
            <tr>
              <td><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></td>
              <td><?= htmlspecialchars($c['nom_activite']) ?></td>
              <td>
                <button
                  class="chat-btn"
                  data-id="<?= $c['id_coach'] ?>"
                  data-name="<?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?>"
                >CHAT</button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Chat box -->
    <div class="chat-box" id="chat-box">
      <h2 id="chat-header">Conversation avec <span id="chat-coach-name"></span></h2>
      <div id="chat-messages"></div>
      <form id="chat-form">
        <input type="hidden" id="chat-coach-id">
        <textarea
          id="chat-input" rows="3"
          style="width:100%; padding:0.5rem;"
          placeholder="Votre message…"
        ></textarea>
        <button type="submit" style="margin-top:0.5rem;padding:0.5rem 1rem;">
          Envoyer
        </button>
      </form>
    </div>
  </main>

  <!-- ======= Pied de page ======= -->
  <footer class="footer">
    <div class="footer-content">
      <!-- ... votre contenu de footer ... -->
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 Sportify - Omnes Education. Tous droits réservés.</p>
    </div>
  </footer>

  <script>
    let pollInterval, currentCoachId;
    let currentCoachName;
    const myId = <?= $id_utilisateur ?>;

    // Ouvre le chat pour le coach cliqué
    document.querySelectorAll('.chat-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        clearInterval(pollInterval);
        currentCoachId   = btn.dataset.id;
        currentCoachName = btn.dataset.name;
        document.getElementById('chat-coach-id').value   = currentCoachId;
        document.getElementById('chat-coach-name').textContent = currentCoachName;
        document.getElementById('chat-box').style.display = 'block';
        loadMessages(); // affiche la conversation existante
        pollInterval = setInterval(loadMessages, 3000);
      });
    });

    // Envoie un message et alerte en cas de succès
    document.getElementById('chat-form').addEventListener('submit', e => {
      e.preventDefault();
      const msg = document.getElementById('chat-input').value.trim();
      if (!msg) return;
      fetch(`?action=send_message`, {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ coach_id: currentCoachId, message: msg })
      })
      .then(res => res.json())
      .then(json => {
        if (json.status === 'ok') {
          alert('Votre message a bien été envoyé ✅');
          document.getElementById('chat-input').value = '';
          loadMessages();
        }
      });
    });

    // Charge la conversation depuis la BDD
    function loadMessages() {
      fetch(`?action=fetch_messages&coach_id=${currentCoachId}`)
        .then(res => res.json())
        .then(data => {
          const container = document.getElementById('chat-messages');
          container.innerHTML = '';
          data.forEach(msg => {
            const div = document.createElement('div');
            div.className = 'message ' + (msg.from_user==myId ? 'mine' : '');
            div.textContent = msg.message;
            container.appendChild(div);
          });
          container.scrollTop = container.scrollHeight;
        });
    }
  </script>
</body>
</html>
