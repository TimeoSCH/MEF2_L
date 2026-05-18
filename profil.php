<?php

session_start();

if (!isset($_SESSION['email'])) {
    header("Location: connexion.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Profil - Les délices de fafa</title>
    <?php
    $fichier_css = "style.css"; // Thème par défaut
    if (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'sombre') {
        $fichier_css = "style-sombre.css";
    }
    ?>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>">
</head>
<body class="page-profil">
    <header>
        <h1 class="header-title">
            Les délices de Fafa 🇲🇦
        </h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="produits.php">🍲 La Carte</a></li>
                <li><a href="inscription.php">📝 Inscription</a></li>
                <li><a href="connexion.php">🔑 Connexion</a></li>
                <li><button onclick="basculerTheme()" style="background:none; border:none; font-size:1.5em; cursor:pointer;" title="Changer le thème">🌗</button></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2 class="text-center">Bienvenue, <?php echo $_SESSION['prenom']; ?> ! 👋</h2>
        
        <div class="profil-container">
            <section class="profil-block">
                <h3>Mes Informations <span>✏️</span></h3>
                <hr class="mb-15">
                <p><strong>Nom :</strong> <?php echo $_SESSION['nom']; ?></p>
                <p><strong>Prénom :</strong> <?php echo $_SESSION['prenom']; ?></p>
                <p><strong>Email :</strong> <?php echo $_SESSION['email']; ?></p>
                <p><strong>Adresse :</strong> <?php echo $_SESSION['adresse']; ?></p>
                <p><strong>Solde Fidélité :</strong> <?php echo $_SESSION['points']; ?> points</p>
                
                <button class="btn btn-blue mt-15 w-100">Modifier mes infos</button>
            </section>

            <section class="profil-block">
                <h3>Mon historique de commandes</h3>
                <hr class="mb-15">
                
                <table class="profile-table">
                    <tr class="table-header-row">
                        <th>Date</th>
                        <th>Plats</th>
                        <th>Prix</th>
                        <th>Statut</th>
                    </tr>
                    <tr>
                        <td>10/02/2026</td>
                        <td>1x Tajine Poulet, 1x Hawaii</td>
                        <td>18.50 €</td>
                        <td>Livrée</td>
                    </tr>
                    <tr>
                        <td>01/02/2026</td>
                        <td>2x Couscous Royal</td>
                        <td>36.00 €</td>
                        <td>
                            Livrée <br>
                            <a href="notation.php" class="link-secondary-bold">[Noter le plat]</a>
                        </td>
                    </tr>
                </table>
            </section>
        </div>
        
        <div class="text-center mt-30">
            <a href="deconnexion.php" class="btn btn-red">Se déconnecter</a>
        </div>
    </main>

    <footer>
        <p>&copy; 2025-2026 Les délices de fafa - Projet Creative Yumland</p>
    </footer>
    <script src="script.js"></script>
</body>
</html>
