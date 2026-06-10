<?php
session_start();
$message_erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $mdp = trim($_POST['password']);

    if (!empty($email) && !empty($mdp)) {
        $fichier = "data/utilisateurs.txt";
        $compte_trouve = false;

        if (file_exists($fichier)) {
            $lignes = file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lignes as $ligne) {
                $cols = explode(";", $ligne);
                
                if (count($cols) >= 7 && trim($cols[0]) === $email && trim($cols[1]) === $mdp) {
                    
                    $est_bloque = (isset($cols[7]) && trim($cols[7]) === '1') ? true : false;

                    if ($est_bloque) {
                        $message_erreur = "Accès refusé : Votre compte a été suspendu par l'administration.";
                        $compte_trouve = true; 
                        break; 
                    }

                    $_SESSION['email'] = trim($cols[0]);
                    $_SESSION['role'] = trim($cols[2]);
                    $_SESSION['nom'] = trim($cols[3]);
                    $_SESSION['prenom'] = trim($cols[4]);
                    $_SESSION['adresse'] = trim($cols[5]);
                    $_SESSION['points'] = trim($cols[6]);

                    $compte_trouve = true;

                    if ($_SESSION['role'] === 'admin') {
                        header("Location: admin.php");
                    } elseif ($_SESSION['role'] === 'restaurateur') {
                        header("Location: commandes.php");
                    } elseif ($_SESSION['role'] === 'livreur') {
                        header("Location: livraison.php");
                    } else {
                        header("Location: index.php");
                    }
                    exit();
                }
            }
        }

        if (!$compte_trouve || $message_erreur === "") {
            $message_erreur = "Identifiants incorrects.";
        }
    } else {
        $message_erreur = "Veuillez remplir tous les champs.";
    }
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
    <title>Connexion - Les délices de fafa</title>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>">
</head>
<body>
    <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="produits.php">🍲 La Carte</a></li>
                <li><a href="inscription.php">📝 Inscription</a></li>
                <li><button class="btn-theme" onclick="basculerTheme()" title="Changer le thème">🌗</button></li>
            </ul>
        </nav>
    </header>
    
    <main class="main-small">
        <section class="card auth-card">
            <h2 class="text-center mb-20">Se connecter</h2>

            <?php if (!empty($message_erreur)): ?>
                <p class="text-center mb-15 link-secondary-bold"><?php echo $message_erreur; ?></p>
            <?php endif; ?>

            <form action="connexion.php" method="post">
                <label class="form-label">Adresse E-mail :</label>
                <input type="email" name="email" required>

                <label class="form-label">Mot de passe :</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required>
                    <button type="button" class="btn-eye" onclick="togglePassword('password')" title="Afficher/Cacher">👁️</button>
                </div>

                <button type="submit" class="btn w-100 mt-10">Connexion</button>
            </form>
            
            <div class="auth-footer mt-20">
                <p>Pas encore de compte ? <a href="inscription.php" class="link-secondary-bold no-underline">S'inscrire</a></p>
            </div>
        </section>
    </main>
    
    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            if (input.type === "password") {
                input.type = "text";
            } else {
                input.type = "password";
            }
        }
    </script>
    <script src="script.js?t=<?php echo time(); ?>"></script>
</body>
</html>