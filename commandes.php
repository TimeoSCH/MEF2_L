<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

// Sécurité : seul un restaurateur peut accéder à cette page
if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'restaurateur') {
    header("Location: index.php");
    exit();
}

// --- SÉCURITÉ : ÉJECTION IMMÉDIATE DES COMPTES BLOQUÉS ---
if (isset($_SESSION['email']) && file_exists("data/utilisateurs.txt")) {
    $lignes_verif = file("data/utilisateurs.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes_verif as $ligne) {
        $cols = explode(";", $ligne);
        if (trim($cols[0]) === $_SESSION['email']) {
            if (isset($cols[7]) && trim($cols[7]) === 'bloque') {
                session_destroy();
                header("Location: connexion.php?erreur=bloque");
                exit();
            }
        }
    }
}

$fichier_cmd = "data/commandes.txt";

// --- GESTION DES MISES À JOUR ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id_commande'])) {
    $id_modif = $_POST['id_commande'];
    $nouveau_statut = $_POST['statut'];
    $nouveau_livreur = $_POST['livreur'];

    if (file_exists($fichier_cmd)) {
        $lignes = file($fichier_cmd, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nouvelles_lignes = [];
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (trim($cols[0]) === $id_modif) {
                $cols[4] = $nouveau_statut; 
                $cols[5] = $nouveau_livreur; 
            }
            $nouvelles_lignes[] = implode(";", $cols);
        }
        file_put_contents($fichier_cmd, implode("\n", $nouvelles_lignes));
    }
    header("Location: commandes.php");
    exit();
}

// --- RÉCUPÉRATION DES LIVREURS ET DES VIP/PREMIUM ---
$liste_livreurs = [];
$roles_clients = []; 
$fichier_users = "data/utilisateurs.txt";

if (file_exists($fichier_users)) {
    $lignes_users = file($fichier_users, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes_users as $ligne) {
        $cols = explode(";", $ligne);
        if (count($cols) >= 3) {
            $email_user = trim($cols[0]);
            $role_user = strtolower(trim($cols[2]));
            
            if ($role_user === 'livreur') {
                $liste_livreurs[] = $email_user;
            }
            $roles_clients[$email_user] = $role_user; 
        }
    }
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
    <title>Espace Cuisinier - Les délices de fafa</title>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>?t=<?php echo time(); ?>">
</head>
<body class="page-restaurateur">
    <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <?php 
                $role_nav = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';
                
                if ($role_nav === 'admin'): ?>
                    <li><a href="produits.php">🍲 La Carte</a></li>
                    <li><a href="admin.php" class="text-success text-bold">🛡️ Tous les Profils</a></li>
                    <li><a href="deconnexion.php">🚪 Déconnexion</a></li>
                <?php elseif (in_array($role_nav, ['client', 'vip', 'premium'])): ?>
                    <li><a href="index.php">🏠 Accueil</a></li>
                    <li><a href="produits.php">🍲 La Carte</a></li>
                    <?php if (basename($_SERVER['PHP_SELF']) === 'produits.php'): ?>
                        <li>
                            <a href="panier.php" class="lien-panier-actif">
                                🛒 Mon Panier <span id="compteur-panier"><?php echo (isset($_SESSION['panier']) && is_array($_SESSION['panier']) && count($_SESSION['panier']) > 0) ? "(".array_sum(array_column($_SESSION['panier'], 'quantite')).")" : "(0)"; ?></span>
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
        <h2 class="text-center mb-20 text-green">👨‍🍳 Gestion des commandes</h2>
        
        <div class="commandes-grid">
            <?php
            if (file_exists($fichier_cmd)) {
                $lignes = file($fichier_cmd, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                $lignes = array_reverse($lignes); 
                
                foreach ($lignes as $ligne) {
                    $cols = explode(";", $ligne);
                    if (count($cols) >= 5) {
                        $id = htmlspecialchars(trim($cols[0]));
                        $client = htmlspecialchars(trim($cols[1]));
                        $plats = htmlspecialchars(trim($cols[2]));
                        $prix = htmlspecialchars(trim($cols[3]));
                        $statut = trim($cols[4]);
                        $livreur_assigne = isset($cols[5]) ? trim($cols[5]) : 'aucun';
                        $moment = (isset($cols[7]) && !empty(trim($cols[7]))) ? trim($cols[7]) : 'Immediat';
                        
                        // -- VÉRIFICATION VIP/PREMIUM --
                        $role_du_client = isset($roles_clients[$client]) ? $roles_clients[$client] : 'client';
                        $etoile_priorite = '';
                        if ($role_du_client === 'vip' || $role_du_client === 'premium') {
                            $etoile_priorite = "<span class='badge-priorite'>⭐ PRIORITÉ ".strtoupper($role_du_client)."</span>";
                        }

                        // -- AFFICHAGE DE L'HEURE --
                        if ($moment === 'Immediat') {
                            $affichage_moment = "<span class='moment-immediat'>⚡ Immédiat</span>";
                        } else {
                            $affichage_moment = "<span class='moment-retarde'>⏰ $moment</span>";
                        }
                        ?>
                        
                        <div class="commande-card card">
                            <h3>Commande #<?= $id ?></h3>
                            <?= $etoile_priorite ?>
                            
                            <p><strong>Contenu :</strong> <?= $plats ?></p>
                            <p><strong>Prix :</strong> <?= $prix ?> €</p>
                            <p><strong>Heure :</strong> <?= $affichage_moment ?></p>
                            
                            <hr>
                            
                            <form action="commandes.php" method="post">
                                <input type="hidden" name="id_commande" value="<?= $id ?>">
                                
                                <label>Statut :</label>
                                <select name="statut">
                                    <option value="A preparer" <?= ($statut=='A preparer')?'selected':'' ?>>À préparer</option>
                                    <option value="Prete" <?= ($statut=='Prete')?'selected':'' ?>>Prête</option>
                                    <option value="En livraison" <?= ($statut=='En livraison')?'selected':'' ?>>En livraison</option>
                                    <option value="Livree" <?= ($statut=='Livree')?'selected':'' ?>>Livrée</option>
                                </select>
                                
                                <label>Assigner un livreur :</label>
                                <select name="livreur">
                                    <option value="aucun">-- Aucun livreur --</option>
                                    <?php foreach ($liste_livreurs as $l): ?>
                                        <option value="<?= $l ?>" <?= ($livreur_assigne==$l)?'selected':'' ?>>🛵 <?= explode('@', $l)[0] ?></option>
                                    <?php endforeach; ?>
                                </select>
                                
                                <button type="submit">Enregistrer les modifications</button>
                            </form>
                        </div>
                        
                        <?php
                    }
                }
            } else {
                echo "<p class='text-center'>Aucune commande pour le moment.</p>";
            }
            ?>
        </div>
    </main>
    <script src="script.js?t=<?php echo time(); ?>"></script>
</body>
</html>