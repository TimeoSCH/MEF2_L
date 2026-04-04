<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'livreur') {
    header("Location: index.php");
    exit();
}

$ma_course = null;
if (file_exists("data/commandes.txt")) {
    $fichier = fopen("data/commandes.txt", "r");
    while (!feof($fichier)) {
        $ligne = trim(fgets($fichier));
        if (!empty($ligne)) {
            $infos = explode(";", $ligne);
            // On cherche la commande assignée à ce livreur précis
            if ($infos[5] == $_SESSION['email'] && $infos[4] == 'En livraison') {
                $ma_course = $infos;
                break;
            }
        }
    }
    fclose($fichier);
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Livraison - Les délices de fafa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1 class="header-title">Les délices de fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="deconnexion.php">🚪 Déconnexion</a></li>
            </ul>
        </nav>
    </header>
    <main class="flex-justify-center">
        <div class="card delivery-card">
            <h2 class="text-center">Course en cours 🛵</h2>
            <hr>
            <?php if ($ma_course): ?>
                <h3>Commande #<?php echo $ma_course[0]; ?></h3>
                <p><strong>Client :</strong> <?php echo $ma_course[1]; ?></p>
                <p><strong>Adresse :</strong> <?php echo $ma_course[6]; ?></p>
                <p><strong>Contenu :</strong> <?php echo $ma_course[2]; ?></p>
                
                <div class="action-buttons">
                    <button class="btn btn-green">Ouvrir GPS (Waze/Maps) 🗺️</button>
                    <button class="btn btn-blue">Appeler le client 📞</button>
                </div>
                
                <hr class="hr-20">
                <button class="btn w-100">Livraison Terminée ✅</button>
            <?php else: ?>
                <p class="text-center">Aucune commande ne vous est assignée pour le moment.</p>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
