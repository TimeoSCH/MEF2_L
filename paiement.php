<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['email']) || !isset($_SESSION['panier']) || empty($_SESSION['panier'])) {
    header("Location: index.php");
    exit();
}

if (isset($_POST['etape_paiement']) && isset($_POST['type_livraison'])) {
    $_SESSION['type_livraison'] = $_POST['type_livraison'];
    $_SESSION['date_livraison'] = $_POST['date_livraison'];
}

$role_client = 'client';
if (file_exists("data/utilisateurs.txt")) {
    $lignes = file("data/utilisateurs.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $cols = explode(";", $ligne);
        if (trim($cols[0]) === $_SESSION['email']) {
            $role_client = strtolower(trim($cols[2]));
            break;
        }
    }
}

$pourcentage_remise = 0;
if ($role_client === 'vip') $pourcentage_remise = 0.20;
elseif ($role_client === 'premium') $pourcentage_remise = 0.10;

$total_panier = 0;
$liste_plats = [];
foreach ($_SESSION['panier'] as $item) {
    $total_panier += ($item['prix'] * $item['quantite']);
    $liste_plats[] = $item['quantite'] . "x " . $item['nom']; 
}
$resume_commande = implode(", ", $liste_plats);

$montant_remise_statut = $total_panier * $pourcentage_remise;
$total_apres_statut = $total_panier - $montant_remise_statut;

$remise = isset($_SESSION['remise_fidelite']) ? $_SESSION['remise_fidelite'] : 0;
$total_a_payer = max(0, $total_apres_statut - $remise);

$is_modif = isset($_SESSION['modif_id']);
$diff = 0;
if ($is_modif) {
    $diff = $total_a_payer - $_SESSION['modif_ancien_prix'];
}

$message_paiement = "";
$paiement_valide = false;

if (isset($_POST['confirmer_paiement'])) {
    $id_transaction = "TX-" . rand(100000, 999999); 
    $date_transaction = date("d/m/Y H:i:s"); 
    
    if ($is_modif) {
        $fichier_cmd = "data/commandes.txt";
        if (file_exists($fichier_cmd)) {
            $lignes = file($fichier_cmd, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $nouvelles_lignes = [];
            foreach ($lignes as $ligne) {
                $cols = explode(";", $ligne);
                if (count($cols) >= 5 && trim($cols[0]) === $_SESSION['modif_id']) {
                    $cols[2] = $resume_commande; 
                    $cols[3] = $total_a_payer;   
                }
                $nouvelles_lignes[] = implode(";", $cols);
            }
            file_put_contents($fichier_cmd, implode("\n", $nouvelles_lignes));
        }

        if ($diff > 0) {
            $nouveau_paiement = "\n" . $id_transaction . ";" . $_SESSION['modif_id'] . ";" . $_SESSION['email'] . ";****1234;" . $diff . ";" . $date_transaction;
            file_put_contents("data/paiements.txt", $nouveau_paiement, FILE_APPEND);
        }
        
        $message_paiement = "✅ Votre commande #".$_SESSION['modif_id']." a été mise à jour avec succès !";
        unset($_SESSION['modif_id']);
        unset($_SESSION['modif_ancien_prix']);

    } else {
        $id_commande = rand(1000, 9999); 
        $moment = "Immediat";
        if (isset($_SESSION['type_livraison']) && $_SESSION['type_livraison'] === 'programme' && !empty($_SESSION['date_livraison'])) {
            $moment = "Prevu le " . date("d/m/Y H:i", strtotime($_SESSION['date_livraison']));
        }
        
        $nouvelle_commande = "\n" . $id_commande . ";" . $_SESSION['email'] . ";" . $resume_commande . ";" . $total_a_payer . ";A preparer;aucun;" . $_SESSION['adresse'] . ";" . $moment;
        file_put_contents("data/commandes.txt", $nouvelle_commande, FILE_APPEND);
        
        $nouveau_paiement = "\n" . $id_transaction . ";" . $id_commande . ";" . $_SESSION['email'] . ";****1234;" . $total_a_payer . ";" . $date_transaction;
        file_put_contents("data/paiements.txt", $nouveau_paiement, FILE_APPEND);
        
        $fichier_users = "data/utilisateurs.txt";
        if (file_exists($fichier_users)) {
            $lignes = file($fichier_users, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $nouvelles_lignes = [];
            $points_gagnes = floor($total_a_payer); 
            $points_perdus = ($remise > 0) ? 100 : 0; 
            
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
        $message_paiement = "✅ Paiement accepté ! <br>Transaction n°<strong>$id_transaction</strong>.";
    }
    
    $_SESSION['panier'] = [];
    unset($_SESSION['remise_fidelite']);
    unset($_SESSION['type_livraison']);
    unset($_SESSION['date_livraison']);
    $paiement_valide = true;
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
    <title>Paiement - Les délices de fafa</title>
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
                <?php endif; ?>
                <li><button class="btn-theme" onclick="basculerTheme()">🌗</button></li>
            </ul>
        </nav>
    </header>

    <main class="main-small">
        <section class="card auth-card card-paiement">
            <h2 class="text-center">💳 Validation de Commande</h2>
            <hr class="mb-20 mt-10">

            <?php if ($paiement_valide): ?>
                <p class="msg-success"><?php echo $message_paiement; ?></p>
                <div class="text-center mt-20">
                    <a href="profil.php" class="btn btn-green">Retour au profil</a>
                </div>
            <?php else: ?>
                <div class="resume-commande-box">
                    <p><strong>Contenu :</strong> <?php echo $resume_commande; ?></p>
                    <p class="mt-5"><strong>Nouveau total :</strong> <?php echo number_format($total_a_payer, 2); ?> €</p>
                    
                    <?php if($montant_remise_statut > 0): ?>
                        <p class="text-orange text-sm mt-5">⭐ Avantage <?php echo strtoupper($role_client); ?> appliqué : - <?php echo number_format($montant_remise_statut, 2); ?> €</p>
                    <?php endif; ?>
                    <?php if($remise > 0): ?>
                        <p class="text-success text-sm mt-5">🎁 Une remise fidélité de 10 € a été déduite.</p>
                    <?php endif; ?>

                    <?php if ($is_modif): ?>
                        <hr class="mt-10 mb-10">
                        <p class="text-sm">Ancien montant payé : <?php echo number_format($_SESSION['modif_ancien_prix'], 2); ?> €</p>
                        <?php if ($diff > 0): ?>
                            <p class="text-orange mt-5">Différence à régler : + <?php echo number_format($diff, 2); ?> €</p>
                        <?php elseif ($diff <= 0): ?>
                            <p class="text-success mt-5">Reste à payer : 0.00 €</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

               <form action="paiement.php" method="post">
                    <?php if (!$is_modif || ($is_modif && $diff > 0)): ?>
                        <input type="text" value="**** **** **** 1234" disabled class="input-bancaire">
                        <button type="submit" name="confirmer_paiement" class="btn btn-green-large w-100">
                            <?php echo $is_modif ? "Payer la différence (".number_format($diff, 2)." €)" : "Confirmer le règlement"; ?>
                        </button>
                    <?php else: ?>
                        <button type="submit" name="confirmer_paiement" class="btn btn-orange w-100 btn-block btn-paiement-modif">Valider la modification (Gratuit)</button>
                    <?php endif; ?>
                </form>
            <?php endif; ?>
        </section>
    </main>
    <script src="script.js?t=<?php echo time(); ?>"></script>
</body>
</html>