<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: connexion.php");
    exit();
}

if (!isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header("Location: produits.php");
    exit();
}

$fichier_css = "style.css"; 
if (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'sombre') {
    $fichier_css = "style-sombre.css";
}

$total_panier = 0;
$liste_plats = [];
foreach ($_SESSION['panier'] as $item) {
    $total_panier += ($item['prix'] * $item['quantite']);
    $liste_plats[] = $item['quantite'] . "x " . $item['nom']; 
}
$resume_commande = implode(", ", $liste_plats);

$remise = isset($_SESSION['remise_fidelite']) ? $_SESSION['remise_fidelite'] : 0;
$total_a_payer = max(0, $total_panier - $remise);

$message_paiement = "";
$paiement_valide = false;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $id_commande = rand(1000, 9999); 
    $id_transaction = "TX-" . rand(100000, 999999); 
    $date_transaction = date("d/m/Y H:i:s"); 
    $coordonnees_bancaires = "**** **** **** 1234"; 
    
    $nouvelle_commande = "\n" . $id_commande . ";" . $_SESSION['email'] . ";" . $resume_commande . ";" . $total_a_payer . ";A preparer;aucun;" . $_SESSION['adresse'];
    $fichier_cmd = fopen("data/commandes.txt", "a");
    if ($fichier_cmd) {
        fwrite($fichier_cmd, $nouvelle_commande);
        fclose($fichier_cmd);
    }
    
    $nouveau_paiement = "\n" . $id_transaction . ";" . $id_commande . ";" . $_SESSION['email'] . ";" . $coordonnees_bancaires . ";" . $total_a_payer . ";" . $date_transaction;
    $fichier_pay = fopen("data/paiements.txt", "a");
    if ($fichier_pay) {
        fwrite($fichier_pay, $nouveau_paiement);
        fclose($fichier_pay);
    }

    $fichier_users = "data/utilisateurs.txt";
    if (file_exists($fichier_users)) {
        $lignes = file($fichier_users, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nouvelles_lignes = [];
        
        $points_gagnes = floor($total_a_payer); 
        $points_perdus = ($remise > 0) ? 100 : 0; // Si remise utilisée, on enlève 100 points
        
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (count($cols) >= 7 && trim($cols[0]) === $_SESSION['email']) {
                $solde_actuel = (int)$cols[6];
                $nouveau_solde = max(0, $solde_actuel - $points_perdus + $points_gagnes);
                $cols[6] = $nouveau_solde;
                
                $_SESSION['points'] = $nouveau_solde;
            }
            $nouvelles_lignes[] = implode(";", $cols);
        }
        file_put_contents($fichier_users, implode("\n", $nouvelles_lignes));
    }
    
    $_SESSION['panier'] = [];
    unset($_SESSION['remise_fidelite']);
    $paiement_valide = true;
    
    $message_paiement = "✅ Paiement accepté via CYBank ! <br>Transaction n°<strong>$id_transaction</strong> enregistrée.<br>Votre solde fidélité a été mis à jour (+ $points_gagnes pts).";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Paiement - Les délices de fafa</title>
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
                <li><button onclick="basculerTheme()" style="background:none; border:none; font-size:1.5em; cursor:pointer;">🌗</button></li>
            </ul>
        </nav>
    </header>

    <main class="main-small">
        <section class="card auth-card" style="max-width: 500px; margin: 0 auto; padding: 20px;">
            <h2 class="text-center">💳 Portail Sécurisé CYBank</h2>
            <hr class="mb-20 mt-10">

            <?php if ($paiement_valide): ?>
                <p class="msg-success" style="color: #27ae60; text-align: center; line-height: 1.5;"><?php echo $message_paiement; ?></p>
                <div class="text-center mt-20" style="text-align: center;">
                    <a href="profil.php" class="btn" style="padding: 10px 20px; background-color: #27ae60; color: white; text-decoration: none; border-radius: 5px;">Aller sur mon profil</a>
                </div>
            <?php else: ?>
                <p class="text-center mb-20" style="text-align: center;">Montant final prélevé : <strong style="font-size: 1.5em; color: #e74c3c;"><?php echo number_format($total_a_payer, 2); ?> €</strong></p>
                
                <div style="background-color: rgba(128,128,128,0.1); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                    <p><strong>Contenu :</strong> <?php echo $resume_commande; ?></p>
                    <?php if($remise > 0): ?>
                        <p style="color: green; font-size: 0.9em; margin-top:5px;">🎁 Une remise fidélité de 10 € a été déduite du montant initial.</p>
                    <?php endif; ?>
                </div>

                <form action="paiement.php" method="post">
                    <input type="text" value="**** **** **** 1234" disabled style="background-color: rgba(0,0,0,0.05); width: 100%; padding: 10px; margin-bottom: 15px; text-align: center; font-weight:bold;">
                    <button type="submit" class="btn" style="width: 100%; padding: 12px; background-color: #27ae60; color: white; border: none; border-radius: 5px; font-weight: bold; cursor: pointer;">Confirmer le règlement</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
    <script src="script.js"></script>
</body>
</html>