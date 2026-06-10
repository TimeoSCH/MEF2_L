<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['role']) || !in_array(strtolower(trim($_SESSION['role'])), ['client', 'vip', 'premium']) || !isset($_SESSION['email'])) {
    header("Location: connexion.php");
    exit();
}

$message = "";
$erreur = "";
$fichier_cmd = "data/commandes.txt";
$fichier_notations = "data/notations.txt";

$id_commande = isset($_GET['id']) ? trim($_GET['id']) : (isset($_POST['id_commande']) ? trim($_POST['id_commande']) : null);

if (!$id_commande) {
    die("Erreur : Aucune commande spécifiée.");
}

$commande_valide = false;
$date_commande = "Inconnue";
if (file_exists($fichier_cmd)) {
    $lignes = file($fichier_cmd, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $cols = explode(";", $ligne);
        if (count($cols) >= 5 && trim($cols[0]) === $id_commande) {
 
            if (trim($cols[1]) === $_SESSION['email']) {
                if (strtolower(trim($cols[4])) === 'livree') {
                    $commande_valide = true;
                    $date_commande = isset($cols[7]) ? trim($cols[7]) : "Récente";
                } else {
                    $erreur = "Cette commande n'est pas encore livrée, vous ne pouvez pas la noter.";
                }
            } else {
                $erreur = "Cette commande ne vous appartient pas.";
            }
            break;
        }
    }
} else {
    $erreur = "Fichier des commandes introuvable.";
}

$deja_note = false;
if (file_exists($fichier_notations)) {
    $lignes_notations = file($fichier_notations, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes_notations as $ligne) {
        $cols = explode(";", $ligne);
        if (trim($cols[0]) === $id_commande) {
            $deja_note = true;
            $erreur = "Vous avez déjà noté cette commande. Merci pour votre retour !";
            break;
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && $commande_valide && !$deja_note && empty($erreur)) {
    $note = trim($_POST['note']);
    $commentaire = trim(str_replace(";", ",", $_POST['commentaire'])); 
    $date_avis = date("d/m/Y H:i");

    $nouvel_avis = "\n" . $id_commande . ";" . $_SESSION['email'] . ";" . $note . ";" . $commentaire . ";" . $date_avis;
    file_put_contents($fichier_notations, $nouvel_avis, FILE_APPEND);
    
    $message = "✅ Merci pour votre évaluation ! Votre avis a bien été enregistré.";
    $deja_note = true; 
}

$fichier_css = "style.css"; 
if (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'sombre') {
    $fichier_css = "style-sombre.css";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notation - Les délices de fafa</title>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>?t=<?php echo time(); ?>">
</head>
<body class="page-panier">
    <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="profil.php">👤 Mon Profil</a></li>
                <li><button class="btn-theme" onclick="basculerTheme()">🌗</button></li>
            </ul>
        </nav>
    </header>
    <main class="main-small">
        <section class="card auth-card panier-card">
            <h2 class="text-center">Évaluer votre commande</h2>
            <p class="text-center mb-20">Commande #<?php echo htmlspecialchars($id_commande); ?></p>
            
            <?php if ($erreur && !$message): ?>
                <div class="box-warning" style="background-color: #ffeaa7; color: #d35400; padding: 15px; border-radius: 5px; margin-bottom: 15px; text-align: center;">
                    <strong>⚠️ <?php echo $erreur; ?></strong>
                </div>
                <div class="text-center mt-15"><a href="profil.php" class="btn btn-blue">Retour au profil</a></div>
            
            <?php elseif ($message): ?>
                <p class="msg-success" style="background-color: #2ecc71; color: white; padding: 15px; border-radius: 5px; text-align: center; font-weight: bold;">
                    <?php echo $message; ?>
                </p>
                <div class="text-center mt-15"><a href="profil.php" class="btn btn-green">Retour au profil</a></div>
            
            <?php else: ?>
                <form action="notation.php" method="post">
                    <input type="hidden" name="id_commande" value="<?php echo htmlspecialchars($id_commande); ?>">
                    
                    <label class="form-label">Note sur 5 étoiles :</label>
                    <select name="note" required class="input-sm w-100 mb-15">
                        <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                        <option value="4">⭐⭐⭐⭐ - Très bien</option>
                        <option value="3">⭐⭐⭐ - Moyen</option>
                        <option value="2">⭐⭐ - Décevant</option>
                        <option value="1">⭐ - Mauvais</option>
                    </select>

                    <label class="form-label mt-15">Laissez un commentaire :</label>
                    <textarea name="commentaire" rows="4" placeholder="Votre avis sur le repas et la livraison..." class="input-sm w-100" style="resize: vertical;"></textarea>

                    <button type="submit" class="btn btn-green w-100 mt-15" style="padding: 15px; font-size: 1.1em;">Envoyer mon avis</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
    <script src="script.js?t=<?php echo time(); ?>"></script>
</body>
</html>
