<?php
session_start();

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$total_panier = 0;
foreach ($_SESSION['panier'] as $item) {
    $total_panier += ($item['prix'] * $item['quantite']);
}

if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    header("Location: panier.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Panier - Les délices de fafa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1 class="header-title">Les délices de fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="produits.php">🍲 La Carte</a></li>
                <li><a href="profil.php">👤 Mon Profil</a></li>
            </ul>
        </nav>
    </header>

    <main class="main-small">
        <h2 class="text-center mb-20">🛒 Mon Panier</h2>

        <section class="card auth-card panier-card">
            
            <?php if (empty($_SESSION['panier'])): ?>
                <p class="text-center mb-20">Votre panier est tristement vide ! 😢</p>
                <div class="text-center">
                    <a href="produits.php" class="btn">Voir la carte</a>
                </div>
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

                <hr class="mt-20 mb-20">
                
                <h3 class="text-right mb-20">Total à payer : <span class="total-price"><?php echo number_format($total_panier, 2); ?> €</span></h3>

                <div class="flex-buttons">
                    <a href="panier.php?vider=true" class="btn btn-red w-100 text-center">Vider le panier</a>
                    <a href="paiement.php" class="btn btn-green w-100 text-center">Valider & Payer</a>
                </div>

            <?php endif; ?>
            
        </section>
    </main>
</body>
</html>
