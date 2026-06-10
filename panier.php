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

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

if (isset($_GET['retirer'])) {
    $index = (int)$_GET['retirer'];
    if (isset($_SESSION['panier'][$index])) {
        unset($_SESSION['panier'][$index]);
        $_SESSION['panier'] = array_values($_SESSION['panier']); 
    }
    header("Location: panier.php");
    exit();
}

if (isset($_GET['annuler_modif'])) {
    $_SESSION['panier'] = [];
    unset($_SESSION['modif_id']);
    unset($_SESSION['modif_ancien_prix']);
    header("Location: profil.php");
    exit();
}

$total_panier = 0;
foreach ($_SESSION['panier'] as $item) {
    $total_panier += ($item['prix'] * $item['quantite']);
}

// --- NOUVEAU : VÉRIFICATION DU STATUT VIP/PREMIUM ---
$role_client = 'client';
$points_disponibles = 0;
if (isset($_SESSION['email']) && file_exists("data/utilisateurs.txt")) {
    $lignes = file("data/utilisateurs.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $cols = explode(";", $ligne);
        if (trim($cols[0]) === $_SESSION['email']) {
            $role_client = strtolower(trim($cols[2]));
            $points_disponibles = (int)$cols[6];
            break;
        }
    }
}

// Application du pourcentage de remise selon le rôle
$pourcentage_remise = 0;
if ($role_client === 'vip') {
    $pourcentage_remise = 0.20; // 20%
} elseif ($role_client === 'premium') {
    $pourcentage_remise = 0.10; // 10%
}

$montant_remise_statut = $total_panier * $pourcentage_remise;
$total_apres_statut = $total_panier - $montant_remise_statut;

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
$total_a_payer = max(0, $total_apres_statut - $valeur_remise);

if (isset($_GET['vider'])) {
    $_SESSION['panier'] = [];
    unset($_SESSION['remise_fidelite']);
    unset($_SESSION['modif_id']);
    unset($_SESSION['modif_ancien_prix']);
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
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>?t=<?php echo time(); ?>">
</head>
<body class="page-panier">
    <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <?php if (isset($_SESSION['role']) && in_array(strtolower($_SESSION['role']), ['client', 'vip', 'premium'])): ?>
                    <li><a href="index.php">🏠 Accueil</a></li>
                    <li><a href="produits.php">🍲 La Carte</a></li>
                    <li><a href="profil.php">👤 Mon Profil</a></li>
                    <li><a href="deconnexion.php">🚪 Déconnexion</a></li>
                <?php else: ?>
                    <li><a href="index.php">🏠 Accueil</a></li>
                    <li><a href="produits.php">🍲 La Carte</a></li>
                    <li><a href="connexion.php">🔑 Connexion</a></li>
                <?php endif; ?>
                <li><button class="btn-theme" onclick="basculerTheme()">🌗</button></li>
            </ul>
        </nav>
    </header>

    <main class="main-small">
        <h2 class="text-center mb-20">🛒 Mon Panier</h2>
        
        <?php if(isset($_SESSION['modif_id'])): ?>
            <div class="box-warning">
                <p>🛠️ <strong>Mode Modification :</strong> Vous modifiez la commande #<?php echo htmlspecialchars($_SESSION['modif_id']); ?>.</p>
                <p>Ancien montant payé : <strong><?php echo number_format($_SESSION['modif_ancien_prix'], 2); ?> €</strong></p>
                <a href="panier.php?annuler_modif=true" class="btn btn-red btn-sm mt-10">Annuler la modification</a>
            </div>
        <?php endif; ?>

        <section class="card auth-card panier-card">
            <?php if (empty($_SESSION['panier'])): ?>
                <p class="text-center mb-20">Votre panier est tristement vide ! 😢</p>
                <div class="text-center"><a href="produits.php" class="btn">Voir la carte</a></div>
            <?php else: ?>
                <table class="profile-table panier-table">
                    <tr class="table-header-row">
                        <th>Plat</th>
                        <th>Qté</th>
                        <th>Prix</th>
                        <th>Total</th>
                        <th>Retirer</th>
                    </tr>
                    <?php foreach ($_SESSION['panier'] as $index => $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['nom']); ?></td>
                        <td>x <?php echo $item['quantite']; ?></td>
                        <td><?php echo number_format($item['prix'], 2); ?> €</td>
                        <td><strong><?php echo number_format($item['prix'] * $item['quantite'], 2); ?> €</strong></td>
                        <td class="text-center"><a href="panier.php?retirer=<?php echo $index; ?>" class="btn-icon-red" title="Retirer de la commande">❌</a></td>
                    </tr>
                    <?php endforeach; ?>
                </table>

                <div class="fidelite-promo-box mt-20">
                    <p>✨ Solde fidélité : <strong><?php echo $points_disponibles; ?></strong> points.</p>
                    <?php if ($points_disponibles >= 100): ?>
                        <?php if ($valeur_remise > 0): ?>
                            <p class="text-success text-bold mt-5">🎉 Remise Fidélité de 10 € Appliquée !</p>
                            <a href="panier.php?remise=non" class="btn btn-red btn-sm mt-5">Annuler la remise</a>
                        <?php else: ?>
                            <a href="panier.php?remise=oui" class="btn btn-orange mt-10">Utiliser 100 points (-10€)</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

                <hr class="mt-20 mb-20">
                
                <?php if($montant_remise_statut > 0 || $valeur_remise > 0): ?>
                    <p class="text-right text-barre">Sous-total brut : <?php echo number_format($total_panier, 2); ?> €</p>
                <?php endif; ?>

                <?php if($montant_remise_statut > 0): ?>
                    <p class="text-right text-orange text-bold">⭐ Avantage <?php echo strtoupper($role_client); ?> (-<?php echo $pourcentage_remise*100; ?>%) : - <?php echo number_format($montant_remise_statut, 2); ?> €</p>
                <?php endif; ?>

                <?php if($valeur_remise > 0): ?>
                    <p class="text-right text-success-normal">Remise Fidélité : - 10.00 €</p>
                <?php endif; ?>

                <h3 class="text-right mb-20">Nouveau Total : <span class="total-price-large"><?php echo number_format($total_a_payer, 2); ?> €</span></h3>

                <form action="paiement.php" method="post">
                    <?php if(!isset($_SESSION['modif_id'])): ?>
                        <div class="schedule-box">
                            <label class="form-label text-bold">Quand souhaitez-vous votre commande ?</label>
                            <select name="type_livraison" id="type_livraison" class="input-sm" onchange="toggleDate()">
                                <option value="immediat">Préparation immédiate</option>
                                <option value="programme">Programmer pour plus tard</option>
                            </select>
                            <div id="box-date" class="hidden mt-10">
                                <label class="form-label">Date et heure :</label>
                                <input type="datetime-local" name="date_livraison" class="input-sm">
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="flex-buttons mt-15">
                        <a href="produits.php" class="btn btn-blue w-100 text-center">Ajouter un plat</a>
                        <button type="submit" name="etape_paiement" class="btn btn-green w-100 text-center">Aller au Paiement</button>
                    </div>
                </form>
            <?php endif; ?>
        </section>
    </main>

    <script>
        function toggleDate() {
            const select = document.getElementById('type_livraison');
            if (select) {
                const box = document.getElementById('box-date');
                if (select.value === 'programme') {
                    box.classList.remove('hidden');
                } else {
                    box.classList.add('hidden');
                }
            }
        }
    </script>
    <script src="script.js?t=<?php echo time(); ?>"></script>
</body>
</html>