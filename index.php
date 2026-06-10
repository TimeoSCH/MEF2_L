<?php
session_start();

if (isset($_SESSION['email']) && file_exists("data/utilisateurs.txt")) {
    $lignes_verif = file("data/utilisateurs.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes_verif as $ligne) {
        $cols = explode(";", $ligne);
        if (trim($cols[0]) === $_SESSION['email']) {
            // Si la colonne 8 (index 7) existe et vaut 'bloque'
            if (isset($cols[7]) && trim($cols[7]) === 'bloque') {
                session_destroy(); // On détruit sa session
                header("Location: connexion.php?erreur=bloque"); // On l'éjecte vers la page de connexion
                exit();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Les délices de fafa - Accueil</title>
    <?php
    $fichier_css = "style.css"; 
    if (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'sombre') {
        $fichier_css = "style-sombre.css";
    }
    ?>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>">
</head>
<body class="page-index">
    <header>
        <h1 class="header-title">
            Les délices de Fafa 🇲🇦
        </h1>
        <nav class="main-nav">
            <ul>
                <?php 
                $role_nav = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';
                
                if ($role_nav === 'admin'): 
                ?>
                    <li><a href="produits.php">🍲 La Carte</a></li>
                    <li><a href="admin.php" class="text-success text-bold">🛡️ Tous les Profils</a></li>
                    <li><a href="deconnexion.php">🚪 Déconnexion</a></li>

                <?php elseif (in_array($role_nav, ['client', 'vip', 'premium'])): ?>
                    <li><a href="index.php">🏠 Accueil</a></li>
                    <li><a href="produits.php">🍲 La Carte</a></li>
                    
                    <?php if (basename($_SERVER['PHP_SELF']) === 'produits.php'): ?>
                        <li>
                            <a href="panier.php" class="lien-panier-actif">
                                🛒 Mon Panier 
                                <?php echo (isset($_SESSION['panier']) && is_array($_SESSION['panier']) && count($_SESSION['panier']) > 0) ? "(".array_sum(array_column($_SESSION['panier'], 'quantite')).")" : "(0)"; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    
                    <li><a href="profil.php">👤 Mon Profil</a></li>
                    <li><a href="deconnexion.php">🚪 Déconnexion</a></li>

                <?php elseif ($role_nav === 'restaurateur'): ?>
                    <li><a href="commandes.php">👨‍🍳 Gestion Cuisine</a></li>
                    <li><a href="deconnexion.php">🚪 Déconnexion</a></li>

                <?php elseif ($role_nav === 'livreur'): ?>
                    <li><a href="livraison.php">🛵 Espace Livreur</a></li>
                    <li><a href="deconnexion.php">🚪 Déconnexion</a></li>

                <?php else: ?>
                    <li><a href="index.php">🏠 Accueil</a></li>
                    <li><a href="produits.php">🍲 La Carte</a></li>
                    <li><a href="inscription.php">📝 Inscription</a></li>
                    <li><a href="connexion.php">🔑 Connexion</a></li>
                <?php endif; ?>

                <li><button class="btn-theme" onclick="basculerTheme()" title="Changer le thème">🌗</button></li>
            </ul>
        </nav>
    </header>

    <main>
        <section class="hero">
            <h2>Bienvenue au Maroc</h2>
            <p>Des saveurs authentiques livrées chez vous.</p>
            
            <div class="search-bar-home">
                <input type="text" placeholder="Rechercher un plat (ex: Tajine, Couscous...)">
                <button class="btn">Rechercher</button>
            </div>
        </section>

        <section>
            <h3>🔥 Nos Coups de Cœur</h3>
            <div class="card-grid justify-center">
                <article class="card">
                    <img src="couscous.jpg">
                    <h4>Couscous</h4>
                    <p>Semoule fine, agneau et légumes frais.</p>
                    <p><strong>18.00 €</strong></p>
                    <button class="btn">Commander</button>
                </article>

                <article class="card">
                    <img src="tajine.jpg"> 
                    <h4>Tajine d'agneau aux pruneaux</h4>
                    <p>Mijoté sucré-salé aux amandes grillées.</p>
                    <p><strong>16.50 €</strong></p>
                    <button class="btn">Commander</button>
                </article>

                <article class="card">
                    <img src="the.jpg">  
                    <h4>Thé à la Menthe</h4>
                    <p>Le traditionnel, servi bien chaud.</p>
                    <p><strong>3.00 €</strong></p>
                    <button class="btn">Commander</button>
                </article>
            </div>
        </section>
    </main>

    <footer>
        <p>&copy; 2025 Les délices de fafa - Projet Creative Yumland</p>
    </footer>
    <script src="script.js?t=<?php echo time(); ?>"></script>
</body>
</html>