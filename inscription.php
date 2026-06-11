<?php
session_start();
$message_succes = "";
$message_erreur = "";

function estEmailValide($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function emailExisteDeja($email_a_tester) {
    $fichier = "data/utilisateurs.txt";
    if (file_exists($fichier)) {
        $lignes = file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (trim($cols[0]) === $email_a_tester) {
                return true;
            }
        }
    }
    return false;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim(strtolower($_POST['email']));
    $adresse = trim($_POST['adresse']);
    $mdp = trim($_POST['password']);
    
    if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($mdp)) {
        if (!estEmailValide($email)) {
            $message_erreur = "⚠️ Le format de votre adresse e-mail est invalide (exemple attendu : nom@domaine.com).";
        } elseif (emailExisteDeja($email)) {
            $message_erreur = "⚠️ Cette adresse e-mail est déjà associée à un compte existant.";
        } else {
            $nouvelle_ligne = "\n" . $email . ";" . $mdp . ";client;" . $nom . ";" . $prenom . ";" . $adresse . ";0";
            $fichier = fopen("data/utilisateurs.txt", "a");
            if ($fichier) {
                fwrite($fichier, $nouvelle_ligne); 
                fclose($fichier); 
                $message_succes = "✅ Votre compte a bien été créé ! Vous pouvez vous connecter.";
            } else {
                $message_erreur = "❌ Erreur système : impossible de sauvegarder les données.";
            }
        }
    } else {
        $message_erreur = "⚠️ Veuillez remplir tous les champs obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Les délices de fafa</title>
    <?php
    $fichier_css = "style.css"; 
    if (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'sombre') {
        $fichier_css = "style-sombre.css";
    }
    ?>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>?t=<?php echo time(); ?>">
</head>
<body>
   <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="produits.php">🍲 La Carte</a></li>
                <li><a href="inscription.php">📝 Inscription</a></li>
                <li><a href="connexion.php">🔑 Connexion</a></li>
                <li><button class="btn-theme" onclick="basculerTheme()" title="Changer le thème">🌗</button></li>
            </ul>
        </nav>
    </header>
    
    <main class="main-small">
        <h2 class="text-center mb-20">Créer un compte</h2>

        <?php if (!empty($message_succes)): ?>
            <div class="msg-success" style="background-color: #e8f8f5; border: 1px solid #27ae60; padding: 15px; border-radius: 5px;">
                <p><?php echo $message_succes; ?></p>
                <a href="connexion.php" class="btn btn-green w-100 text-center mt-10">Aller à la connexion</a>
            </div>
        <?php endif; ?>

        <?php if (!empty($message_erreur)): ?>
            <div class="box-warning" style="background-color: #fadbd8; color: #c0392b; border: 1px solid #e74c3c; padding: 10px; border-radius: 5px; text-align: center; margin-bottom: 15px;">
                <strong><?php echo $message_erreur; ?></strong>
            </div>
        <?php endif; ?>

        <?php if (empty($message_succes)): ?>
        <form action="inscription.php" method="post" class="auth-card" style="margin: 0 auto;">
            <label class="form-label">Nom :</label>
            <input type="text" name="nom" required class="input-sm">
            
            <label class="form-label">Prénom :</label>
            <input type="text" name="prenom" required class="input-sm">
            
            <label class="form-label">Adresse E-mail :</label>
            <input type="email" name="email" required class="input-sm" placeholder="exemple@domaine.com">
            
            <label class="form-label">Adresse complète de livraison :</label>
            <textarea name="adresse" rows="3" required class="input-sm" style="resize: vertical;"></textarea>
            
            <label class="form-label">Mot de passe :</label>
            <input type="password" name="password" required class="input-sm">
            
            <button type="submit" class="btn w-100 mt-15" style="font-size: 1.1em; padding: 12px;">Créer mon compte</button>
        </form>
        <?php endif; ?>
    </main>
    
    <script src="script.js?t=<?php echo time(); ?>"></script>
</body>
</html>
