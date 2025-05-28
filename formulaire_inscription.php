<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Créer un compte - Sportify</title>
    <link rel="stylesheet" href="style.css" />
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
            <li><a href="#rdv">Rendez-vous</a></li>
            <li><a href="votre_compte.php">Votre Compte</a></li>
        </ul>
        <div class="nav-auth">
            <div class="cta-wrapper">
                <button class="cta-button" onclick="window.location.href='formulaire_inscription.php'">Créer un compte</button>
                <button class="cta-button" onclick="window.location.href='formulaire_connexion.php'">Se connecter</button>
            </div>
        </div>
    </nav>
</header>

<div class="main-background">
    <main class="main-content">
        <h2 class="event-title">Créer votre compte Sportify</h2>        
        <form class="signup-form" method="POST" action="partie_php/traitement_formulaire_inscription.php">
            <div class="form-group">
                <label for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" placeholder="Votre nom" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="prenom">Prénom *</label>
                <input type="text" id="prenom" name="prenom" placeholder="Votre prénom" maxlength="100" required>
            </div>

            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" placeholder="votre.email@omneseducation.fr" maxlength="191" required>
                <small style="color: #666; font-size: 0.9em; margin-top: 0.2rem;">Utilisez votre adresse email Omnes Education</small>
            </div>

            <div class="form-group">
                <label for="mot_de_passe">Mot de passe *</label>
                <input type="password" id="mot_de_passe" name="mot_de_passe" placeholder="Minimum 8 caractères" minlength="8" required>
            </div>

            <div class="form-group">
                <label for="confirmer_mot_de_passe">Confirmer le mot de passe *</label>
                <input type="password" id="confirmer_mot_de_passe" name="confirmer_mot_de_passe" placeholder="Retapez votre mot de passe" minlength="8" required>
            </div>

            <div class="form-group">
                <label for="date_naissance">Date de naissance</label>
                <input type="date" id="date_naissance" name="date_naissance" max="<?= date('Y-m-d', strtotime('-16 years')) ?>">
            </div>

            <div class="form-group">
                <label for="telephone">Téléphone</label>
                <input type="tel" id="telephone" name="telephone" placeholder="+33 6 12 34 56 78" maxlength="20" pattern="[\+]?[0-9\s\-\(\)]{8,20}">
            </div>

            <div class="form-group">
                <label for="carte_etudiant">Numéro de carte étudiant</label>
                <input type="text" id="carte_etudiant" name="carte_etudiant" placeholder="Numéro de votre carte étudiant Omnes" maxlength="50">
            </div>            
            <div class="form-group">
                <label for="adresse_ligne1">Adresse ligne 1</label>
                <input type="text" id="adresse_ligne1" name="adresse_ligne1" placeholder="Numéro et nom de rue" maxlength="255">
            </div>

            <div class="form-group">
                <label for="adresse_ligne2">Adresse ligne 2</label>
                <input type="text" id="adresse_ligne2" name="adresse_ligne2" placeholder="Complément d'adresse (optionnel)" maxlength="255">
            </div>

            <div class="form-group">
                <label for="ville">Ville</label>
                <input type="text" id="ville" name="ville" placeholder="Votre ville" maxlength="100">
            </div>

            <div class="form-group">
                <label for="code_postal">Code postal</label>
                <input type="text" id="code_postal" name="code_postal" placeholder="75000" maxlength="10" pattern="[0-9]{5}">
            </div>

            <div class="form-group">
                <label for="pays">Pays</label>
                <select id="pays" name="pays">
                    <option value="France" selected>France</option>
                    <option value="Belgique">Belgique</option>
                    <option value="Suisse">Suisse</option>
                    <option value="Canada">Canada</option>
                    <option value="Autre">Autre</option>
                </select>
            </div>

            <button type="submit" class="cta-button">Créer mon compte</button>
            
            <p class="event-details">Déjà inscrit? Connectez vous ici.</p>
            <div class="cta-wrapper">
                <button class="cta-button" onclick="window.location.href='formulaire_connexion.php'">Se connecter</button>
            </div>
        </form>

       
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


<script>
// Validation du formulaire côté client
document.querySelector('.signup-form').addEventListener('submit', function(e) {
    const motDePasse = document.getElementById('mot_de_passe').value;
    const confirmerMotDePasse = document.getElementById('confirmer_mot_de_passe').value;
    
    if (motDePasse !== confirmerMotDePasse) {
        e.preventDefault();
        alert('Les mots de passe ne correspondent pas.');
        return false;
    }
    
    // Validation email Omnes Education (optionnel)
    const email = document.getElementById('email').value;
    if (!email.includes('@omneseducation.fr')) {
        const confirmation = confirm('Vous n\'utilisez pas une adresse email Omnes Education. Continuer quand même ?');
        if (!confirmation) {
            e.preventDefault();
            return false;
        }
    }
});

// Mise en forme automatique du téléphone
document.getElementById('telephone').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value.startsWith('33')) {
        value = '+33 ' + value.substring(2);
    } else if (value.startsWith('0')) {
        value = '+33 ' + value.substring(1);
    }
    
    // Format: +33 6 12 34 56 78
    if (value.startsWith('+33')) {
        value = value.replace(/(\+33)(\d{1})(\d{2})(\d{2})(\d{2})(\d{2})/, '$1 $2 $3 $4 $5 $6');
    }
    
    e.target.value = value;
});
</script>

</body>
</html>