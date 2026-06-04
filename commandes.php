<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'restaurateur') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'modifier_commande') {
    $id_commande = $_POST['id_commande'];
    $nouveau_statut = $_POST['statut'];
    $nouveau_livreur = $_POST['livreur'];
    
    $fichier_cmd = "data/commandes.txt";
    $reponse = ['success' => false];
    
    if (file_exists($fichier_cmd)) {
        $lignes = file($fichier_cmd, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nouvelles_lignes = [];
        
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (count($cols) >= 7 && trim($cols[0]) === $id_commande) {
                $cols[4] = $nouveau_statut; 
                $cols[5] = $nouveau_livreur; 
                $reponse['success'] = true;
            }
            $nouvelles_lignes[] = implode(";", $cols);
        }
        file_put_contents($fichier_cmd, implode("\n", $nouvelles_lignes));
    }
    
    header('Content-Type: application/json');
    echo json_encode($reponse);
    exit();
}

$livreurs = [];
if (file_exists("data/utilisateurs.txt")) {
    $lignes_users = file("data/utilisateurs.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes_users as $ligne) {
        $cols = explode(";", $ligne);
        if (count($cols) >= 3 && trim($cols[2]) === 'livreur') {
            $livreurs[] = ['email' => $cols[0], 'nom' => $cols[3] . ' ' . $cols[4]];
        }
    }
}

$commandes = [];
if (file_exists("data/commandes.txt")) {
    $fichier = fopen("data/commandes.txt", "r");
    while (!feof($fichier)) {
        $ligne = trim(fgets($fichier));
        if (!empty($ligne)) {
            $commandes[] = explode(";", $ligne);
        }
    }
    fclose($fichier);
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
    <title>Cuisine - Les délices de fafa</title>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>">
</head>
<body>
    <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="deconnexion.php">🚪 Déconnexion</a></li>
                <li><button class="btn-theme" onclick="basculerTheme()" title="Changer le thème">🌗</button></li>
            </ul>
        </nav>
    </header>
    <main>
        <h2 class="text-center">👨‍🍳 Gestion des commandes</h2>
        <div class="card-grid mb-40">
            <?php foreach (array_reverse($commandes) as $cmd): ?>
                <article class="card <?php echo ($cmd[4] == 'A preparer') ? 'border-red' : 'border-orange'; ?>">
                    <h4>Commande #<?php echo htmlspecialchars($cmd[0]); ?></h4>
                    <p class="mb-10"><strong>Contenu :</strong> <?php echo htmlspecialchars($cmd[2]); ?></p>
                    <p class="mb-10"><strong>Prix :</strong> <?php echo htmlspecialchars($cmd[3]); ?> €</p>
                    
                    <hr class="mt-10 mb-10">
                    
                    <label class="form-label text-sm">Statut :</label>
                    <select id="statut_<?php echo htmlspecialchars($cmd[0]); ?>" class="input-sm mb-10">
                        <option value="Payée" <?php echo ($cmd[4]=='Payée')?'selected':''; ?>>Payée</option>
                        <option value="A preparer" <?php echo ($cmd[4]=='A preparer')?'selected':''; ?>>À préparer</option>
                        <option value="En preparation" <?php echo ($cmd[4]=='En preparation')?'selected':''; ?>>En préparation</option>
                        <option value="Prete" <?php echo ($cmd[4]=='Prete')?'selected':''; ?>>Prête</option>
                        <option value="En livraison" <?php echo ($cmd[4]=='En livraison')?'selected':''; ?>>En livraison</option>
                        <option value="Livree" <?php echo ($cmd[4]=='Livree')?'selected':''; ?>>Livrée</option>
                    </select>

                    <label class="form-label text-sm">Assigner un livreur :</label>
                    <select id="livreur_<?php echo htmlspecialchars($cmd[0]); ?>" class="input-sm mb-10">
                        <option value="aucun">-- Aucun livreur --</option>
                        <?php foreach ($livreurs as $liv): ?>
                            <option value="<?php echo htmlspecialchars($liv['email']); ?>" <?php echo ($cmd[5] == $liv['email']) ? 'selected' : ''; ?>>
                                🛵 <?php echo htmlspecialchars($liv['nom']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    
                    <button class="btn btn-green w-100 mt-10" onclick="modifierCommande('<?php echo htmlspecialchars($cmd[0]); ?>', this)">Enregistrer les modifications</button>
                </article>
            <?php endforeach; ?>
        </div>
    </main>
    
    <script>
        function modifierCommande(idCommande, bouton) {
            let statutChoisi = document.getElementById('statut_' + idCommande).value;
            let livreurChoisi = document.getElementById('livreur_' + idCommande).value;
            
            let texteOriginal = bouton.innerText;
            bouton.innerText = "Enregistrement... ⏳";
            bouton.disabled = true;
            
            let formData = new FormData();
            formData.append('action', 'modifier_commande');
            formData.append('id_commande', idCommande);
            formData.append('statut', statutChoisi);
            formData.append('livreur', livreurChoisi);
            
            fetch('commandes.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    bouton.innerText = "✅ Modifications enregistrées !";
                    bouton.className = "btn btn-blue w-100 mt-10";
                    
                    setTimeout(() => {
                        window.location.reload();
                    }, 1000);
                } else {
                    alert("Erreur lors de l'enregistrement de la commande.");
                    bouton.innerText = texteOriginal;
                    bouton.disabled = false;
                }
            })
            .catch(error => {
                alert("Erreur réseau. Impossible de contacter le serveur.");
                bouton.innerText = texteOriginal;
                bouton.disabled = false;
            });
        }
    </script>
    <script src="script.js"></script>
</body>
</html>