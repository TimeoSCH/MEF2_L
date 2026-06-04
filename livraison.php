<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'livreur') {
    header("Location: index.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'terminer_livraison') {
    $id_commande = $_POST['id_commande'];
    $fichier_txt = "data/commandes.txt";
    $reponse = ['success' => false];

    if (file_exists($fichier_txt)) {
        $lignes = file($fichier_txt, FILE_IGNORE_NEW_LINES);
        $nouvelles_lignes = [];
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (count($cols) >= 7 && trim($cols[0]) === $id_commande && trim($cols[5]) === $_SESSION['email']) {
                $cols[4] = 'Livree'; 
                $reponse['success'] = true;
            }
            $nouvelles_lignes[] = implode(";", $cols);
        }
        file_put_contents($fichier_txt, implode("\n", $nouvelles_lignes));
    }
    
    header('Content-Type: application/json');
    echo json_encode($reponse);
    exit(); 
}

$ma_course = null;
if (file_exists("data/commandes.txt")) {
    $fichier = fopen("data/commandes.txt", "r");
    while (!feof($fichier)) {
        $ligne = trim(fgets($fichier));
        if (!empty($ligne)) {
            $infos = explode(";", $ligne);
            if (count($infos) >= 7 && trim($infos[5]) == $_SESSION['email'] && trim($infos[4]) == 'En livraison') {
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Livraison - Les délices de fafa</title>
    
    <?php
    $fichier_css = "style.css"; 
    if (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'sombre') {
        $fichier_css = "style-sombre.css";
    }
    ?>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>">
</head>
<body class="page-panier">
    <header>
        <h1 class="header-title">🛵 Livraison Fafa</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="deconnexion.php">🚪 Déconnexion</a></li>
                <li><button class="btn-theme" onclick="basculerTheme()" title="Changer le thème">🌗</button></li>
            </ul>
        </nav>
    </header>

    <main class="livreur-main flex-justify-center">
        <div class="card delivery-card">
            
            <h2 class="text-center">Course en cours</h2>
            <hr class="mb-15">
            
            <?php if ($ma_course): ?>
                
                <div class="info-box">
                    <p class="text-big">📍 <?php echo htmlspecialchars($ma_course[6]); ?></p>
                    <p><strong>Bâtiment :</strong> B, Étage 3, Porte Gauche</p>
                    <p><strong>Digicode :</strong> 48A5 🔑</p>
                    <p><strong>Client :</strong> <?php echo htmlspecialchars($ma_course[1]); ?></p>
                    <p><strong>Contact :</strong> 06 12 34 56 78</p>
                    <hr class="mt-10 mb-10">
                    <p><strong>Sac :</strong> <?php echo htmlspecialchars($ma_course[2]); ?></p>
                </div>
                
                <div class="action-buttons">
                    <a href="https://www.google.com/maps/search/?api=1&query=<?php echo urlencode($ma_course[6]); ?>" target="_blank" class="btn-xxl btn-gps">🗺️ Ouvrir GPS</a>
                    
                    <a href="tel:0612345678" class="btn-xxl btn-tel">📞 Appeler le client</a>
                </div>
                
                <hr class="mt-30 mb-30">
                
                <button id="btn-terminer" onclick="validerLivraison('<?php echo $ma_course[0]; ?>')" class="btn-xxl btn-valid">✅ LIVRAISON TERMINÉE</button>
            
            <?php else: ?>
                <div class="text-center mt-40 mb-40">
                    <p class="filter-title">Aucune commande ne vous est assignée pour le moment. ☕</p>
                    <button onclick="window.location.reload()" class="btn btn-blue mt-20">Actualiser</button>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <script>
        function validerLivraison(idCommande) {
            if (!confirm("Confirmez-vous que la commande a été remise au client ?")) return;

            const btn = document.getElementById('btn-terminer');
            btn.disabled = true;
            btn.innerText = "Validation... ⏳";
            
            btn.style.opacity = "0.7"; 

            const formData = new FormData();
            formData.append('action', 'terminer_livraison');
            formData.append('id_commande', idCommande);

            fetch('livraison.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert("Course terminée avec succès ! Beau travail.");
                    window.location.reload(); 
                } else {
                    alert("Erreur système lors de la validation.");
                    btn.disabled = false;
                    btn.innerText = "✅ LIVRAISON TERMINÉE";
                    btn.style.opacity = "1";
                }
            })
            .catch(error => {
                alert("Réseau instable. Veuillez réessayer.");
                btn.disabled = false;
                btn.innerText = "✅ LIVRAISON TERMINÉE";
                btn.style.opacity = "1";
            });
        }
    </script>
    <script src="script.js"></script>
</body>
</html>