<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'restaurateur') {
    header("Location: index.php");
    exit();
}

$commandes = [];
if (file_exists("data/commandes.txt")) {
    $fichier = fopen("data/commandes.txt", "r");
    while (!feof($fichier)) {
        $ligne = trim(fgets($fichier));
        if (!empty($ligne)) {
            $commandes[] = explode(";", $ligne);
        }
    }
    fclose($fichier);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Cuisine - Les délices de fafa</title>
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
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="deconnexion.php">🚪 Déconnexion</a></li>
                <li><button onclick="basculerTheme()" style="background:none; border:none; font-size:1.5em; cursor:pointer;" title="Changer le thème">🌗</button></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2>👨‍🍳 Gestion des commandes (Vue Restaurateur)</h2>
        <div class="card-grid mb-40">
            <?php foreach ($commandes as $cmd): ?>
                <article class="card <?php echo ($cmd[4] == 'A preparer') ? 'border-red' : 'border-orange'; ?>">
                    <h4>Commande #<?php echo $cmd[0]; ?></h4>
                    <p><strong>Statut :</strong> <?php echo $cmd[4]; ?></p>
                    <p><strong>Contenu :</strong> <?php echo $cmd[2]; ?></p>
                    <p><strong>Prix :</strong> <?php echo $cmd[3]; ?> €</p>
                    <p><strong>Livreur :</strong> <?php echo $cmd[5]; ?></p>
                    
                    <button class="btn btn-green w-100 mt-10">Prête pour livraison ➡️</button>
                    <button class="btn btn-blue w-100 mt-10">Assigner un livreur</button>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
    <script src="script.js"></script>
</body>
</html>
