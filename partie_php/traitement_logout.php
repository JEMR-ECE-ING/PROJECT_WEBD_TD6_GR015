<?php
// partie_php/traitement_logout.php
session_start();

//TOUT DEEEEETRUIRE
$_SESSION = array();

//TOUT DETRUIREEEEEEEE
session_destroy();

//Renvoyer vers l'accueil
header('Location: ../accueil.php?logout=success');
exit();
?>