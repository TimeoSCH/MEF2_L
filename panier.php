<?php
session_start();

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$total_panier = 0;
foreach ($_SESSION['panier'] as $item) {
    $total_panier += ($item['prix'] * $item['quantite']);
}

$points_disponibles = 0;
if (isset($_SESSION['email']) && file_exists("data/utilisateurs.txt")) {
    $lignes = file("data/utilisateurs.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $cols = explode(";", $ligne);
        if (trim($cols[0]) === $_SESSION['email']) {
            $points_disponibles = (int)$cols[6];
            break;
        }
    }
}

if (isset($_GET['remise'])) {
    if ($_GET['remise'] === 'oui' && $points_disponibles >= 100) {
        $_SESSION['remise_fidelite'] = 10; 
    } else {
        unset($_SESSION['remise_fidelite']); 
    }
    header("Location: panier.php");
    exit();
}

$valeur_remise = isset($_SESSION['remise_fidelite']) ? $_SESSION['remise_fidelite'] : 0;
$total_a_payer = max(0, $total_panier - $valeur_remise);

if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    unset($_SESSION['remise_fidelite']);
    header("Location: panier.php");
    exit();
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
    <title>Mon Panier - Les délices de fafa</title>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>">
</head>
<body class="page-panier">
    <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="produits.php">🍲 La Carte</a></li>
                <li><a href="profil.php">👤 Mon Profil</a></li>
                <li><button class="btn-theme" onclick="basculerTheme()" title="Changer le thème">🌗</button></li>
            </ul>
        </nav>
    </header>

    <main class="main-small">
        <h2 class="text-center mb-20">🛒 Mon Panier</h2>
        <section class="card auth-card panier-card">
            <?php if (empty($_SESSION['panier'])): ?>
                <p class="text-center mb-20">Votre panier est tristement vide ! 😢</p>
                <div class="text-center"><a href="produits.php" class="btn">Voir la carte</a></div>
            <?php else: ?>
                <table class="profile-table panier-table">
                    <tr class="table-header-row">
                        <th>Plat</th>
                        <th>Quantité</th>
                        <th>Prix Unitaire</th>
                        <th>Sous-total</th>
                    </tr>
                    <?php foreach ($_SESSION['panier'] as $item): ?>
                    <tr>
                        <td><?php echo $item['nom']; ?></td>
                        <td>x <?php echo $item['quantite']; ?></td>
                        <td><?php echo number_format($item['prix'], 2); ?> €</td>
                        <td><strong><?php echo number_format($item['prix'] * $item['quantite'], 2); ?> €</strong></td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <div class="fidelite-promo-box">
                    <p>✨ Votre solde fidélité actuel : <strong><?php echo $points_disponibles; ?></strong> points.</p>
                    <?php if ($points_disponibles >= 100): ?>
                        <?php if ($valeur_remise > 0): ?>
                            <p class="text-success text-bold">🎉 Remise Fidélité de 10 € Appliquée !</p>
                            <a href="panier.php?remise=non" class="btn btn-red btn-sm">Annuler la remise</a>
                        <?php else: ?>
                            <p>Vous avez assez de points pour économiser 10 € !</p>
                            <a href="panier.php?remise=oui" class="btn btn-orange">Utiliser 100 points (-10€)</a>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="text-muted-sm">(Il vous faut au moins 100 points pour débloquer une remise de 10 €).</p>
                    <?php endif; ?>
                </div>

                <hr class="mt-20 mb-20">
                
                <?php if($valeur_remise > 0): ?>
                    <p class="text-right text-barre">Sous-total : <?php echo number_format($total_panier, 2); ?> €</p>
                    <p class="text-right text-success-normal">Remise Fidélité : - 10.00 €</p>
                <?php endif; ?>
                
                <h3 class="text-right mb-20">Total à payer : <span class="total-price-large"><?php echo number_format($total_a_payer, 2); ?> €</span></h3>

                <div class="flex-buttons">
                    <a href="panier.php?vider=true" class="btn btn-red w-100 text-center">Vider le panier</a>
                    <a href="paiement.php" class="btn btn-green w-100 text-center">Valider & Payer</a>
                </div>
            <?php endif; ?>
        </section>
    </main>
    <script src="script.js"></script>
</body>
</html>