<?php
session_start();
if (!isset($_SESSION['id_utilisateur']) || $_SESSION['type_utilisateur'] !== 'coach') {
    header("Location: formulaire_connexion.php");
    exit();
}

$id_coach = $_SESSION['id_utilisateur'];

// Connexion PDO
$pdo = new PDO(
    "mysql:host=localhost;dbname=sportify;charset=utf8mb4",
    'root', '',
    [
      PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]
);

// AJAX pour le chat
if (isset($_GET['action'])) {
    header('Content-Type: application/json; charset=utf-8');
    $action = $_GET['action'];

    // Charger les messages entre ce coach et un client
    if ($action === 'fetch_messages' && isset($_GET['client_id'])) {
        $client_id = (int)$_GET['client_id'];
        $stmt = $pdo->prepare("
            SELECT from_user, to_user, message, sent_at
            FROM messages
            WHERE (from_user = :me AND to_user = :other)
               OR (from_user = :other AND to_user = :me)
            ORDER BY sent_at ASC
        ");
        $stmt->execute([
            'me'    => $id_coach,
            'other' => $client_id
        ]);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    // Envoyer un message du coach vers le client
    if ($action === 'send_message' && $_SERVER['REQUEST_METHOD']==='POST') {
        $data      = json_decode(file_get_contents('php://input'), true);
        $client_id = (int)$data['client_id'];
        $msg       = trim($data['message']);
        if ($msg !== '') {
            $stmt = $pdo->prepare("
                INSERT INTO messages (from_user, to_user, message, sent_at)
                VALUES (:from, :to, :msg, NOW())
            ");
            $stmt->execute([
                'from' => $id_coach,
                'to'   => $client_id,
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

// Récupérer la liste des clients ayant déjà envoyé un message à ce coach
$stmtClients = $pdo->prepare("
    SELECT DISTINCT u.id_utilisateur, u.prenom, u.nom
    FROM messages m
    JOIN utilisateurs u
      ON u.id_utilisateur = CASE
        WHEN m.from_user = :me THEN m.to_user
        ELSE m.from_user
      END
    WHERE m.from_user = :me
       OR m.to_user = :me
      AND u.type_utilisateur = 'client'
    ");
$stmtClients->execute(['me' => $id_coach]);
$clients = $stmtClients->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0">
  <title>Messages Clients – Sportify</title>
  <link rel="stylesheet" href="style.css">
  <style>
    .dashboard-container { max-width:1200px; margin:0 auto; padding:2rem; }
    .page-title { color:#00ff88; text-align:center; margin-bottom:1.5rem; }
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
  <!-- En-tête/Nav -->
  <header class="header">
    <nav class="nav-container">
      <div class="logo">
        <img src="images/logov2.png" class="logo-icon" alt="Logo">
        <span class="logo-text">SPORTIFY</span>
      </div>
      <ul class="nav-menu">
        <li><a href="accueil.php">Accueil</a></li>
        <li><a href="dashboard_admin.php">Dashboard</a></li>
        <li><a href="messages_coach.php" class="active">Messages Clients</a></li>
        <li><a href="votre_compte.php">Mon Profil</a></li>
      </ul>
      <div class="nav-auth">
        <button class="cta-button" onclick="window.location.href='partie_php/traitement_logout.php'">
          Déconnexion
        </button>
      </div>
    </nav>
  </header>

  <main class="dashboard-container">
    <a href="dashboard_admin.php" class="back-button">&larr; Retour au Dashboard</a>
    <h1 class="page-title">Vos conversations clients</h1>

    <table>
      <thead>
        <tr><th>Nom du client</th><th>Action</th></tr>
      </thead>
      <tbody>
        <?php if (empty($clients)): ?>
          <tr>
            <td colspan="2" style="text-align:center;color:#ccc;">
              Aucun client n'a encore envoyé de message.
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($clients as $c): ?>
            <tr>
              <td><?= htmlspecialchars($c['prenom'].' '.$c['nom']) ?></td>
              <td>
                <button
                  class="chat-btn"
                  data-id="<?= $c['id_utilisateur'] ?>"
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
      <h2 id="chat-header">Conversation avec <span id="chat-client-name"></span></h2>
      <div id="chat-messages"></div>
      <form id="chat-form">
        <input type="hidden" id="chat-client-id">
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

  <!-- Pied de page -->
  <footer class="footer">
    <div class="footer-content">
      <!-- votre footer ici -->
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 Sportify - Omnes Education. Tous droits réservés.</p>
    </div>
  </footer>

  <script>
    let pollInterval, currentClientId;
    let currentClientName;
    const myId = <?= $id_coach ?>;

    document.querySelectorAll('.chat-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        clearInterval(pollInterval);
        currentClientId   = btn.dataset.id;
        currentClientName = btn.dataset.name;
        document.getElementById('chat-client-id').value   = currentClientId;
        document.getElementById('chat-client-name').textContent = currentClientName;
        document.getElementById('chat-box').style.display = 'block';
        loadMessages();
        pollInterval = setInterval(loadMessages, 3000);
      });
    });

    document.getElementById('chat-form').addEventListener('submit', e => {
      e.preventDefault();
      const msg = document.getElementById('chat-input').value.trim();
      if (!msg) return;
      fetch(`?action=send_message`, {
        method: 'POST',
        headers:{'Content-Type':'application/json'},
        body:JSON.stringify({ client_id: currentClientId, message: msg })
      })
      .then(res => res.json())
      .then(json => {
        if (json.status === 'ok') {
          alert('Message envoyé ✅');
          document.getElementById('chat-input').value = '';
          loadMessages();
        }
      });
    });

    function loadMessages() {
      fetch(`?action=fetch_messages&client_id=${currentClientId}`)
        .then(res => res.json())
        .then(data => {
          const container = document.getElementById('chat-messages');
          container.innerHTML = '';
          data.forEach(msg => {
            const div = document.createElement('div');
            div.className = 'message ' + (msg.from_user==myId?'mine':'');
            div.textContent = msg.message;
            container.appendChild(div);
          });
          container.scrollTop = container.scrollHeight;
        });
    }
  </script>
</body>
</html>
