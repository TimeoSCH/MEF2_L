<?php
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: connexion.php");
    exit();
}

$msg_succes = "";
$fichier_utilisateurs = "data/utilisateurs.txt";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['enregistrer_infos'])) {
    $nom_saisi = trim($_POST['nom']);
    $prenom_saisi = trim($_POST['prenom']);
    $adresse_saisi = trim($_POST['adresse']);

    if (file_exists($fichier_utilisateurs)) {
        $lignes = file($fichier_utilisateurs, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nouvelles_lignes = [];
        
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (count($cols) >= 7 && trim($cols[0]) === $_SESSION['email']) {
                $cols[3] = $nom_saisi; 
                $cols[4] = $prenom_saisi; 
                $cols[5] = $adresse_saisi; 
                
                $_SESSION['nom'] = $nom_saisi;
                $_SESSION['prenom'] = $prenom_saisi;
                $_SESSION['adresse'] = $adresse_saisi;
            }
            $nouvelles_lignes[] = implode(";", $cols);
        }
        file_put_contents($fichier_utilisateurs, implode("\n", $nouvelles_lignes));
        $msg_succes = "✅ Vos informations ont été mises à jour !";
        
        header("Location: profil.php");
        exit();
    }
}

$points_fidelite = 0;
if (file_exists($fichier_utilisateurs)) {
    $lignes = file($fichier_utilisateurs, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $cols = explode(";", $ligne);
        if (count($cols) >= 7 && trim($cols[0]) === $_SESSION['email']) {
            $points_fidelite = (int)trim($cols[6]);
            $_SESSION['points'] = $points_fidelite; 
            break;
        }
    }
}

$mes_commandes = [];
$fichier_commandes = "data/commandes.txt";

if (file_exists($fichier_commandes)) {
    $lignes = file($fichier_commandes, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $cols = explode(";", $ligne);
        if (count($cols) >= 5 && trim($cols[1]) === $_SESSION['email']) {
            $mes_commandes[] = [
                'id' => htmlspecialchars(trim($cols[0])),
                'plats' => htmlspecialchars(trim($cols[2])),
                'prix' => htmlspecialchars(trim($cols[3])),
                'statut' => htmlspecialchars(trim($cols[4]))
            ];
        }
    }
    $mes_commandes = array_reverse($mes_commandes);
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
    <title>Mon Profil - Les délices de fafa</title>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>">
</head>
<body class="page-profil">
    <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
    <ul>
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <li><a href="produits.php">🍲 La Carte</a></li>
            <li><a href="admin.php" class="text-success text-bold">🛡️ Tous les Profils</a></li>
            <li><a href="deconnexion.php">🚪 Déconnexion</a></li>

        <?php elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'client'): ?>
            <li><a href="index.php">🏠 Accueil</a></li>
            <li><a href="produits.php">🍲 La Carte</a></li>
            
            <?php if (basename($_SERVER['PHP_SELF']) === 'produits.php'): ?>
                <li>
                    <a href="panier.php">
                        🛒 Mon Panier 
                        <?php echo (isset($_SESSION['panier']) && count($_SESSION['panier']) > 0) ? "(".array_sum(array_column($_SESSION['panier'], 'quantite')).")" : "(0)"; ?>
                    </a>
                </li>
            <?php endif; ?>
            
            <li><a href="profil.php">👤 Mon Profil</a></li>
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
        <h2 class="text-center">Bienvenue, <?php echo htmlspecialchars($_SESSION['prenom']); ?> ! 👋</h2>
        
        <?php if(!empty($msg_succes)): ?>
            <p class="msg-success-bold"><?php echo $msg_succes; ?></p>
        <?php endif; ?>

        <div class="profil-container">
            <section class="profil-block">
                <h3>Mes Informations <span>✏️</span></h3>
                <hr class="mb-15">
                
                <?php if (isset($_GET['action']) && $_GET['action'] === 'editer'): ?>
                    <form action="profil.php" method="post">
                        <div class="mb-10">
                            <label><strong>Nom :</strong></label>
                            <input type="text" name="nom" value="<?php echo htmlspecialchars($_SESSION['nom']); ?>" required class="input-sm w-100">
                        </div>
                        <div class="mb-10">
                            <label><strong>Prénom :</strong></label>
                            <input type="text" name="prenom" value="<?php echo htmlspecialchars($_SESSION['prenom']); ?>" required class="input-sm w-100">
                        </div>
                        <div class="mb-10">
                            <label><strong>Adresse de livraison :</strong></label>
                            <input type="text" name="adresse" value="<?php echo htmlspecialchars($_SESSION['adresse']); ?>" required class="input-sm w-100">
                        </div>
                        <button type="submit" name="enregistrer_infos" class="btn btn-green mt-15 w-100">💾 Enregistrer les modifications</button>
                        <a href="profil.php" class="btn btn-red mt-10 w-100 btn-block">Annuler</a>
                    </form>
                <?php else: ?>
                    <p><strong>Nom :</strong> <?php echo htmlspecialchars($_SESSION['nom']); ?></p>
                    <p><strong>Prénom :</strong> <?php echo htmlspecialchars($_SESSION['prenom']); ?></p>
                    <p><strong>Email :</strong> <?php echo htmlspecialchars($_SESSION['email']); ?></p>
                    <p><strong>Adresse :</strong> <?php echo htmlspecialchars($_SESSION['adresse']); ?></p>
                    <p class="fidelite-box">
                        ⭐ <strong>Solde Fidélité :</strong> <span class="fidelite-score"><?php echo $points_fidelite; ?></span> points
                    </p>
                    
                    <a href="profil.php?action=editer" class="btn btn-blue mt-15 w-100 btn-block">Modifier mes infos</a>
                <?php endif; ?>
            </section>

            <section class="profil-block">
                <h3>Mon historique de commandes</h3>
                <hr class="mb-15">
                <table class="profile-table">
                    <tr class="table-header-row">
                        <th>N° Cmd</th>
                        <th>Plats</th>
                        <th>Prix</th>
                        <th>Statut</th>
                    </tr>
                    <?php if (empty($mes_commandes)): ?>
                        <tr><td colspan="4" class="text-center">Aucune commande passée.</td></tr>
                    <?php else: ?>
                        <?php foreach ($mes_commandes as $cmd): ?>
                            <tr>
                                <td>#<?php echo $cmd['id']; ?></td>
                                <td><?php echo $cmd['plats']; ?></td>
                                <td><?php echo number_format((float)$cmd['prix'], 2); ?> €</td>
                                <td><span class="badge"><?php echo $cmd['statut']; ?></span></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </table>
            </section>
        </div>
    </main>
    <script src="script.js"></script>
</body>
</html>