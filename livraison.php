<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'livreur') {
    header("Location: index.php");
    exit();
}

$commande_en_cours = null;
$client_info = null;

$fichier_cmd = "data/commandes.txt";
if (file_exists($fichier_cmd)) {
    $lignes = file($fichier_cmd, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $cols = explode(";", $ligne);
        if (count($cols) >= 6 && trim($cols[4]) === 'En livraison' && trim($cols[5]) === $_SESSION['email']) {
            $commande_en_cours = [
                'id' => trim($cols[0]),
                'client_email' => trim($cols[1]),
                'plats' => trim($cols[2])
            ];
            break;
        }
    }
}

if ($commande_en_cours) {
    $fichier_users = "data/utilisateurs.txt";
    if (file_exists($fichier_users)) {
        $lignes_users = file($fichier_users, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lignes_users as $ligne) {
            $cols_user = explode(";", $ligne);
            if (trim($cols_user[0]) === $commande_en_cours['client_email']) {
                $client_info = [
                    'nom' => trim($cols_user[3]),
                    'prenom' => trim($cols_user[4]),
                    'adresse' => trim($cols_user[5])
                ];
                break;
            }
        }
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['terminer_livraison']) && $commande_en_cours) {
    $lignes_cmd = file($fichier_cmd, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $nouvelles_lignes = [];
    foreach ($lignes_cmd as $ligne) {
        $cols = explode(";", $ligne);
        if (trim($cols[0]) === $commande_en_cours['id']) {
            $cols[4] = 'Livree'; 
        }
        $nouvelles_lignes[] = implode(";", $cols);
    }
    file_put_contents($fichier_cmd, implode("\n", $nouvelles_lignes));
    header("Location: livraison.php");
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
    <title>Espace Livreur - Les délices de fafa</title>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>">
</head>
<body class="page-livreur">
    <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="deconnexion.php">🚪 Déconnexion</a></li>
                <li><button class="btn-theme" onclick="basculerTheme()">🌗</button></li>
            </ul>
        </nav>
    </header>

    <main class="main-small">
        <h2 class="text-center mb-20">🛵 Espace Livreur</h2>

        <?php if ($commande_en_cours && $client_info): ?>
            <section class="card auth-card">
                <h3 class="text-center mb-15">Course en cours (Cmd #<?php echo $commande_en_cours['id']; ?>)</h3>
                <hr class="mb-15">
                
                <div class="mb-20">
                    <p class="text-large text-bold text-orange mb-10">
                        📍 <?php echo htmlspecialchars($client_info['adresse']); ?>
                    </p>
                    
                    <p><strong>Client :</strong> <?php echo htmlspecialchars($client_info['prenom']) . " " . htmlspecialchars($client_info['nom']); ?></p>
                    <p><strong>Email :</strong> <?php echo htmlspecialchars($commande_en_cours['client_email']); ?></p>
                    
                    <hr class="mt-15 mb-15">
                    <p class="text-muted-sm"><strong>Sac :</strong> <?php echo htmlspecialchars($commande_en_cours['plats']); ?></p>
                </div>

                <div class="flex-buttons livraison-actions">
                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($client_info['adresse']); ?>" target="_blank" class="btn btn-blue w-100 text-center">🗺️ Ouvrir GPS</a>
                    
                    <a href="mailto:<?php echo htmlspecialchars($commande_en_cours['client_email']); ?>" class="btn btn-orange w-100 text-center">✉️ Contacter le client</a>
                    
                    <form action="livraison.php" method="post" class="w-100">
                        <button type="submit" name="terminer_livraison" class="btn btn-green-large w-100 mt-10">✅ LIVRAISON TERMINÉE</button>
                    </form>
                </div>
            </section>
        <?php else: ?>
            <section class="card auth-card text-center">
                <p class="mb-20">Aucune commande ne vous est assignée pour le moment.</p>
                <a href="livraison.php" class="btn btn-blue">🔄 Actualiser</a>
            </section>
        <?php endif; ?>
    </main>
    <script src="script.js?t=<?php echo time(); ?>"></script>
</body>
</html>
