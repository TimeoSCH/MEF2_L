<?php

session_start();

$message_erreur = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_saisi = $_POST['email'];
    $mdp_saisi = $_POST['password'];
    $authentifie = false;

    if (file_exists("data/utilisateurs.txt")) {
        $fichier = fopen("data/utilisateurs.txt", "r");
        
        while (!feof($fichier)) {
            $ligne = fgets($fichier);
            $ligne = trim($ligne); // Enlever les espaces et sauts de ligne
            
            if (!empty($ligne)) {
                $infos = explode(";", $ligne);
                
                if ($infos[0] == $email_saisi && $infos[1] == $mdp_saisi) {
                    $authentifie = true;
                    
                    $_SESSION['email'] = $infos[0];
                    $_SESSION['role'] = $infos[2];
                    $_SESSION['nom'] = $infos[3];
                    $_SESSION['prenom'] = $infos[4];
                    $_SESSION['adresse'] = $infos[5];
                    $_SESSION['points'] = $infos[6];
                    
                    if ($_SESSION['role'] == 'restaurateur') {
                        header("Location: commandes.php");
                    } elseif ($_SESSION['role'] == 'livreur') {
                        header("Location: livraison.php");
                    } else {
                        header("Location: profil.php");
                    }
                    exit(); 
                }
            }
        }
        fclose($fichier); 
    } else {
        $message_erreur = "Erreur système : base de données introuvable.";
    }

    if (!$authentifie) {
        $message_erreur = "Adresse e-mail ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Les délices de fafa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1 class="header-title">
            Les délices de fafa 🇲🇦
        </h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="produits.php">🍲 La Carte</a></li>
                <li><a href="inscription.php">📝 Inscription</a></li>
                <li><a href="profil.php">👤 Mon Profil</a></li>
            </ul>
        </nav>
    </header>

    <main class="flex-center auth-main">
        
        <section class="card auth-card">
            <h2 class="text-center">Connexion</h2>
            <p class="text-center mb-20">Accédez à votre compte pour commander vos plats préférés.</p>
            
            <?php if (!empty($message_erreur)): ?>
                <p class="text-center mb-15 link-secondary-bold"><?php echo $message_erreur; ?></p>
            <?php endif; ?>
            
            <form action="connexion.php" method="post">
                <div class="mb-15">
                    <label for="email" class="form-label">Adresse E-mail :</label>
                    <input type="email" id="email" name="email" placeholder="votre@email.com" required>
                </div>

                <div class="mb-20">
                    <label for="password" class="form-label">Mot de passe :</label>
                    <input type="password" id="password" name="password" placeholder="Votre mot de passe" required>
                </div>

                <button type="submit" class="btn w-100">Se connecter</button>
            </form>

            <div class="auth-footer">
                <p>Pas encore de compte ? <a href="inscription.php" class="link-secondary-bold">Inscrivez-vous ici</a></p>
                <p class="mt-10"><a href="#" class="link-muted">Mot de passe oublié ?</a></p>
            </div>
        </section>

    </main>

    <footer>
        <p>&copy; 2025-2026 Les délices de fafa - Projet Creative Yumland</p>
    </footer>
</body>
</html>
