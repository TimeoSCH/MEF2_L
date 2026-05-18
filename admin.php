<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Les délices de fafa - Accueil</title>
    <?php
    $fichier_css = "style.css"; // Thème par défaut
    if (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'sombre') {
        $fichier_css = "style-sombre.css";
    }
    ?>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>">
</head>
<body>
    <header>
        <h1 class="header-title">
            Les délices de Fafa 🇲🇦
        </h1>
        <nav class="main-nav">
            <ul>
                <li><a href="produits.php">🍲 La Carte</a></li>
                <li><a href="inscription.php">📝 Inscription</a></li>
                <li><a href="connexion.php">🔑 Connexion</a></li>
                <li><a href="profil.php">👤 Mon Profil</a></li>
                <li><button onclick="basculerTheme()" style="background:none; border:none; font-size:1.5em; cursor:pointer;" title="Changer le thème">🌗</button></li>
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
    <script src="script.js"></script>
</body>
</html>
