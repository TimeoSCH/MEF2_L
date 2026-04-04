<?php
session_start();
session_unset(); // Supprime toutes les variables de session [cite: 1122]
session_destroy(); // Détruit la session [cite: 1124]
header("Location: index.php"); // Redirige vers l'accueil
exit();
?>
