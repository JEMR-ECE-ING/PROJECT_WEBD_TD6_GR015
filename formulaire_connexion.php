<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Page de connexion</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <nav class="nav-container">
            <div class="logo">
                <div><img src="images/logov2.png" class = "logo-icon"></div>
                <span class="logo-text">SPORTIFY</span> 
            </div>
            
            <ul class="nav-menu">
                <li><a href="accueil.php">Accueil</a></li>
                <li><a href="tout_parcourir.php">Tout Parcourir</a></li>
                <li><a href="#recherche">Recherche</a></li>
                <li><a href="#rdv">Rendez-vous</a></li>
                <li><a href="votre_compte.php">Votre Compte</a></li>
            </ul>
            <!--Petit bouton recherche avec partie.-->
            <div class="nav-auth">
                <!--
                <div class="search-bar">
                    <input type="text" placeholder="Rechercher...">
                    <button><img src="images/search.png" class="logo-recherche"></button>
                </div>
                -->
                <div class="cta-wrapper">
                    <button class="cta-button" onclick="window.location.href='formulaire_inscription.php'">Créer un compte</button>
                    <button class="cta-button" onclick="window.location.href='formulaire_connexion.php'">Se connecter</button>
                </div>
            </div>
        </nav>
    </header>

    <div class="main-background">
        <main class="main-content">
            <section class="event-section">
                <h2 class="event-title">Connexion</h2>
                <div class="event-content">
                     <div>
                        <img src="images/coolimage.png" alt="stade_login" class="event-image">
                    </div>
                    
                    <div class="event-details">
                        <div id="messages-container"></div>

                    <form class="signup-form" method="POST" action="partie_php/traitement_connexion.php">
                        <div class="form-group">
                            <label for="email">Email Omnes Education</label>
                            <input type="email" id="email" name="email" required placeholder="prenom.nom@omneseducation.fr">
                        </div>

                        <div class="form-group">
                            <label for="mot_de_passe">Mot de passe</label>
                            <input type="password" id="mot_de_passe" name="mot_de_passe" required placeholder="Votre mot de passe">
                        </div>

                        <button type="submit" class="cta-button" style="width: 100%; margin-bottom: 1rem;">
                            Se connecter
                        </button>

                        <div class="login-section">
                            <p style="color: #cccccc;">Vous n'avez pas de compte ?</p>
                            <button type="button" class="cta-button" onclick="window.location.href='formulaire_inscription.php'">
                                Créer un compte
                            </button>
                        </div>
                    </form>
                    </div>
                </div>
            </section>
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
        // Validation email
        document.getElementById('email').addEventListener('blur', function() {
            if (this.value && !this.value.endsWith('@omneseducation.fr')) {
                this.style.borderColor = '#ff4444';
                alert('Veuillez utiliser votre email Omnes Education');
            } else if (this.value) {
                this.style.borderColor = '#00ff88';
            }
        });

        // Affichage des messages (vous devrez adapter selon votre système de messages)
        window.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const messagesContainer = document.getElementById('messages-container');
            
            // Message de succès d'inscription
            if (urlParams.has('success')) {
                messagesContainer.innerHTML = '<div class="success-message">Compte créé avec succès ! Vous pouvez maintenant vous connecter.</div>';
            }
            
            // Messages d'erreur
            if (urlParams.has('error')) {
                const error = decodeURIComponent(urlParams.get('error'));
                messagesContainer.innerHTML = '<div class="error-message">' + error + '</div>';
            }
        });
    </script>
</body>
</html>