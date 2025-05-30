<?php
session_start();

$host = 'localhost';
$dbname = 'sportify';
$user = 'root';
$password = '';

// Connexion PDO
try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $user, $password,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die("Erreur base de données : " . $e->getMessage());
}

// Endpoint AJAX : liste des slots réservés pour une semaine
if (isset($_GET['action'], $_GET['coach_id'], $_GET['week_start']) 
    && $_GET['action'] === 'fetch_slots'
) {
    header('Content-Type: application/json; charset=utf-8');
    $cid    = (int)$_GET['coach_id'];
    $start  = $_GET['week_start'] . ' 00:00:00';
    $end    = date('Y-m-d H:i:s', strtotime($start . ' +7 days'));
    $q      = $pdo->prepare("
      SELECT date_rdv
      FROM rendez_vous
      WHERE id_coach = ? 
        AND statut = 'confirme'
        AND date_rdv BETWEEN ? AND ?
    ");
    $q->execute([$cid, $start, $end]);
    echo json_encode(array_column($q->fetchAll(), 'date_rdv'));
    exit;
}

// Vérifie l'authentification
$id_utilisateur = $_SESSION['id_utilisateur'] ?? null;
if (!$id_utilisateur) {
    header('Location: formulaire_connexion.php');
    exit();
}

// Pré-sélection depuis tout_parcourir.php
$prefIdActivite = isset($_GET['id_activite']) ? intval($_GET['id_activite']) : null;
$prefIdCoach    = isset($_GET['id_coach'])    ? intval($_GET['id_coach'])    : null;

// Suppression de RDV
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_rdv'])) {
    $idr = intval($_POST['delete_rdv']);
    $chk = $pdo->prepare("SELECT id_rdv FROM rendez_vous WHERE id_rdv = ? AND id_client = ?");
    $chk->execute([$idr, $id_utilisateur]);
    if ($chk->fetch()) {
        $pdo->prepare("DELETE FROM rendez_vous WHERE id_rdv = ?")->execute([$idr]);
        $_SESSION['success'] = "Rendez-vous supprimé avec succès.";
    } else {
        $_SESSION['error'] = "Suppression non autorisée.";
    }
    header("Location: mes_rendezvous.php");
    exit();
}

// (Ici votre code de création de RDV reste inchangé)

// Flash messages
$message     = $_SESSION['success'] ?? $_SESSION['error'] ?? '';
$messageType = isset($_SESSION['success']) ? 'success' : (isset($_SESSION['error']) ? 'error' : '');
unset($_SESSION['success'], $_SESSION['error']);

// Prochain rendez-vous
$stmt = $pdo->prepare("
  SELECT rdv.*, a.nom_activite, u.prenom AS prenom_coach, u.nom AS nom_coach, c.bureau
  FROM rendez_vous rdv
  JOIN activites_sportives a ON rdv.id_activite = a.id_activite
  JOIN coachs c               ON rdv.id_coach    = c.id_coach
  JOIN utilisateurs u         ON c.id_coach       = u.id_utilisateur
  WHERE rdv.id_client = ? AND rdv.date_rdv >= NOW() AND rdv.statut = 'confirme'
  ORDER BY rdv.date_rdv ASC
  LIMIT 1
");
$stmt->execute([$id_utilisateur]);
$prochain_rdv = $stmt->fetch();

// Tous les rendez-vous confirmés
$stmt = $pdo->prepare("
  SELECT rdv.*, a.nom_activite, u.prenom AS prenom_coach, u.nom AS nom_coach, c.bureau
  FROM rendez_vous rdv
  JOIN activites_sportives a ON rdv.id_activite = a.id_activite
  JOIN coachs c               ON rdv.id_coach    = c.id_coach
  JOIN utilisateurs u         ON c.id_coach       = u.id_utilisateur
  WHERE rdv.id_client = ? AND rdv.statut = 'confirme'
  ORDER BY rdv.date_rdv ASC
");
$stmt->execute([$id_utilisateur]);
$tous_les_rdv = $stmt->fetchAll();

// Liste des activités (actives)
$activites = $pdo->query("
  SELECT a.*, c.nom_categorie
  FROM activites_sportives a
  JOIN categories_sport c ON a.id_categorie = c.id_categorie
  WHERE a.actif = 1
  ORDER BY c.nom_categorie, a.nom_activite
")->fetchAll();

// Liste des coachs
$coachs = $pdo->query("
  SELECT c.id_coach, u.prenom, u.nom, c.specialite_principale, c.bureau
  FROM coachs c
  JOIN utilisateurs u ON c.id_coach = u.id_utilisateur
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Mes Rendez-vous – Sportify</title>
  <link rel="stylesheet" href="style.css" />
  <style>
    .main-background {
      background: url('images/pcbback.png') repeat;
      background-size: 200px 200px;
      padding: 2rem 0;
    }
    .container { max-width:900px; margin:auto; padding:0 1rem; color:#eee; }
    h1,h2 { color:#00ff88; text-align:center; }
    .alert { padding:1rem; border-radius:5px; margin-bottom:1rem; text-align:center; }
    .alert.success { background:#033; color:#0f0; }
    .alert.error   { background:#300; color:#f44; }
    .card { background:rgba(0,0,0,0.7); border:2px solid #00ff88; border-radius:10px; padding:1rem 1.5rem; margin-bottom:1.5rem; }
    .card h3 { margin:0 0 .5rem; color:#00ff88; }
    .card form { margin-top:1rem; }
    .card form button { background:#00ff88; border:none; border-radius:25px; color:#000; padding:.5rem 1.5rem; cursor:pointer; }
    .rdv-list .card { position:relative; }
    .rdv-list .card form { position:absolute; top:1rem; right:1rem; }
    form#form-rdv { background:rgba(0,0,0,0.7); border:2px solid #00ff88; border-radius:10px; padding:1.5rem; }
    form#form-rdv fieldset { border:1px solid #00ff88; border-radius:5px; margin-bottom:1rem; padding:1rem; }
    form#form-rdv legend { padding:0 .5rem; color:#00ff88; }
    .options-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:.5rem; }
    .options-grid label { background:rgba(255,255,255,0.1); padding:.5rem; border-radius:4px; cursor:pointer; display:flex; align-items:center; }
    .options-grid input { margin-right:.5rem; accent-color:#00ff88; }
    #btn-valider { display:block; margin:1rem auto 0; background:#00ff88; color:#000; border:none; padding:.75rem 2rem; border-radius:25px; cursor:pointer; }
    #btn-valider:disabled { opacity:.5; cursor:not-allowed; }

    /* Nouvel emploi du temps */
    #week-nav { display:flex; justify-content:space-between; align-items:center; margin-bottom:.5rem; }
    #timetable { width:100%; border-collapse:collapse; text-align:center; }
    #timetable th, #timetable td { border:1px solid #00ff88; padding:.5rem; }
    #timetable th { background:rgba(0,0,0,0.8); }
  </style>
</head>
<body>
  <!-- HEADER -->
  <header class="header">
    <nav class="nav-container">
      <div class="logo">
        <img src="images/logov2.png" class="logo-icon" alt="Logo">
        <span class="logo-text">SPORTIFY</span>
      </div>
      <ul class="nav-menu">
        <li><a href="accueil.php">Accueil</a></li>
        <li><a href="tout_parcourir.php">Tout Parcourir</a></li>
        <li><a href="mes_rendezvous.php" class="active">Rendez-vous</a></li>
        <li><a href="votre_compte.php">Votre Compte</a></li>
      </ul>
      <div class="nav-auth">
        <button class="cta-button" onclick="location.href='partie_php/traitement_logout.php'">Déconnexion</button>
      </div>
    </nav>
  </header>

  <!-- MAIN -->
  <div class="main-background">
    <div class="container">
      <h1>Mes Rendez-vous</h1>

      <?php if ($message): ?>
        <div class="alert <?= $messageType ?>">
          <?= htmlspecialchars($message) ?>
        </div>
      <?php endif; ?>

      <?php if ($prochain_rdv): ?>
        <div class="card">
          <h2>Prochain rendez-vous</h2>
          <h3><?= htmlspecialchars($prochain_rdv['nom_activite']) ?></h3>
          <p><strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($prochain_rdv['date_rdv'])) ?></p>
          <p><strong>Coach :</strong> <?= htmlspecialchars($prochain_rdv['prenom_coach'].' '.$prochain_rdv['nom_coach']) ?></p>
          <p><strong>Bureau :</strong> <?= htmlspecialchars($prochain_rdv['bureau']) ?></p>
        </div>
      <?php else: ?>
        <p style="text-align:center;color:#ccc;">Vous n'avez pas de rendez-vous à venir.</p>
      <?php endif; ?>

      <?php if ($tous_les_rdv): ?>
        <h2>Tous mes rendez-vous</h2>
        <div class="rdv-list">
          <?php foreach($tous_les_rdv as $rdv): ?>
            <div class="card">
              <h3>
                <?= htmlspecialchars($rdv['nom_activite']) ?> –
                <?= date('d/m/Y H:i', strtotime($rdv['date_rdv'])) ?>
              </h3>
              <p><strong>Coach :</strong> <?= htmlspecialchars($rdv['prenom_coach'].' '.$rdv['nom_coach']) ?></p>
              <p><strong>Bureau :</strong> <?= htmlspecialchars($rdv['bureau']) ?></p>
              <form method="POST" onsubmit="return confirm('Supprimer ce RDV ?');">
                <input type="hidden" name="delete_rdv" value="<?= $rdv['id_rdv'] ?>">
                <button type="submit">Supprimer</button>
              </form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <h2>Prendre un nouveau RDV</h2>
      <form method="POST" id="form-rdv">
        <fieldset>
          <legend>Activité</legend>
          <div class="options-grid">
            <?php foreach($activites as $a):
              $chk = $a['id_activite'] === $prefIdActivite ? 'checked' : '';
            ?>
              <label>
                <input type="radio" name="id_activite" value="<?= $a['id_activite'] ?>"
                       required onchange="majBtn()" <?= $chk ?>>
                <?= htmlspecialchars($a['nom_activite']) ?>
              </label>
            <?php endforeach; ?>
          </div>
        </fieldset>

        <fieldset id="fs-coachs" style="display:none;">
          <legend>Coach</legend>
          <div class="options-grid" id="coachs-container"></div>
        </fieldset>

        <fieldset id="fs-creneaux" style="display:none;">
          <legend>Créneau</legend>

          <!-- navigation de semaine -->
          <div id="week-nav">
            <button type="button" id="prev-week">&larr;</button>
            <span id="current-week-label"></span>
            <button type="button" id="next-week">&rarr;</button>
          </div>

          <!-- tableau hebdomadaire -->
          <table id="timetable">
            <thead>
              <tr>
                <th>Heure</th>
                <th>Lundi</th><th>Mardi</th><th>Mercredi</th>
                <th>Jeudi</th><th>Vendredi</th>
                <th>Samedi</th><th>Dimanche</th>
              </tr>
            </thead>
            <tbody>
              <!-- généré par JS -->
            </tbody>
          </table>
        </fieldset>

        <button type="submit" id="btn-valider" disabled>Confirmer</button>
      </form>
    </div>
  </div>

  <!-- FOOTER -->
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
        <a href="tout_parcourir.php">Activités Sportives</a><br>
        <a href="tout_parcourir.php?categorie=sports_competition">Sports de Compétition</a><br>
        <a href="regles.php">Salle de Sport</a><br>
        <a href="mes_rendezvous.php">Mes Rendez-vous</a>
      </div>
      <div class="footer-section">
        <h3>Localisation</h3>
        <iframe
          src="https://maps.google.com/maps?q=10%20Rue%20Sextius%20Michel%2C%20750006%20Paris&amp;hl=fr&amp;z=15&amp;output=embed"
          width="100%" height="200" style="border:0;border-radius:8px;" loading="lazy">
        </iframe>
      </div>
    </div>
    <div class="footer-bottom">
      <p>&copy; 2025 Sportify – Omnes Education. Tous droits réservés.</p>
    </div>
  </footer>

  <script>
    const activites = <?= json_encode($activites) ?>;
    const coachs     = <?= json_encode($coachs) ?>;
    let actSel, coachSel, slotSel;

    function majBtn() {
      document.getElementById('btn-valider').disabled = !(actSel && coachSel && slotSel);
    }

    // Sélection d'une activité
    document.querySelectorAll('input[name="id_activite"]').forEach(radio => {
      radio.addEventListener('change', () => {
        actSel = activites.find(a => a.id_activite == radio.value);
        coachSel = slotSel = null;
        document.getElementById('fs-coachs').style.display = 'block';
        document.getElementById('fs-creneaux').style.display = 'none';
        majBtn();

        // Génération des coachs
        const cont = document.getElementById('coachs-container');
        cont.innerHTML = '';
        coachs
          .filter(c => c.specialite_principale === actSel.nom_activite)
          .forEach(c => {
            const lbl = document.createElement('label');
            lbl.innerHTML = `
              <input type="radio" name="id_coach" value="${c.id_coach}">
              ${c.prenom} ${c.nom} (${c.bureau})
            `;
            const inp = lbl.querySelector('input');
            if (c.id_coach === <?= $prefIdCoach ?? 'null' ?>) {
              inp.checked = true;
              coachSel = c;
            }
            inp.addEventListener('change', () => {
              coachSel = c;
              afficherPlanning();
              majBtn();
            });
            cont.appendChild(lbl);
          });
      });
    });

    // Créneaux forfaitaires
    const timeSlots = ['08h00-10h00','10h00-12h00','12h00-14h00','14h00-16h00','16h00-18h00'];

    function getMonday(d) {
      d = new Date(d);
      let day = d.getDay(), diff = d.getDate() - day + (day===0? -6:1);
      return new Date(d.setDate(diff));
    }
    function fmtDate(d) {
      const YYYY = d.getFullYear(),
            MM   = ('0'+(d.getMonth()+1)).slice(-2),
            DD   = ('0'+d.getDate()).slice(-2);
      return `${YYYY}-${MM}-${DD}`;
    }

    async function fetchBookedSlots(coachId, weekStart) {
      const res = await fetch(
        `mes_rendezvous.php?action=fetch_slots&coach_id=${coachId}&week_start=${weekStart}`
      );
      return res.json();
    }

    let currentMonday = getMonday(new Date());

    async function renderTimetable(weekStartDate) {
      const monday = getMonday(weekStartDate);
      const days = [...Array(7)].map((_,i)=>{
        let d = new Date(monday);
        d.setDate(monday.getDate()+i);
        return d;
      });
      document.getElementById('current-week-label').textContent =
        days[0].toLocaleDateString() + ' → ' + days[6].toLocaleDateString();

      const booked = await fetchBookedSlots(coachSel.id_coach, fmtDate(monday));
      const tbody  = document.querySelector('#timetable tbody');
      tbody.innerHTML = '';

      for (let slot of timeSlots) {
        const tr = document.createElement('tr');
        const th = document.createElement('th');
        th.textContent = slot;
        tr.appendChild(th);

        days.forEach(day => {
          const td = document.createElement('td');
          const isoDay = fmtDate(day);
          const start  = slot.split('-')[0].replace('h',':') + ':00';
          const dateTime = `${isoDay} ${start}`;
          const isBooked = booked.some(d => d.startsWith(isoDay));
          if (!isBooked) {
            const r = document.createElement('input');
            r.type = 'radio';
            r.name = 'date_rdv';
            r.value = dateTime;
            r.addEventListener('change', ()=>{
              slotSel = r.value;
              majBtn();
            });
            td.appendChild(r);
          } else {
            td.textContent = '—';
            td.style.color = '#f44';
          }
          tr.appendChild(td);
        });

        tbody.appendChild(tr);
      }
    }

    function afficherPlanning() {
      document.getElementById('fs-creneaux').style.display = 'block';
      renderTimetable(new Date());
    }

    document.getElementById('prev-week').addEventListener('click', ()=>{
      currentMonday.setDate(currentMonday.getDate() - 7);
      renderTimetable(currentMonday);
    });
    document.getElementById('next-week').addEventListener('click', ()=>{
      currentMonday.setDate(currentMonday.getDate() + 7);
      renderTimetable(currentMonday);
    });
  </script>
</body>
</html>
