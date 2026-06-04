<?php
session_start();

if (!isset($_SESSION['email']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit();
}

if (isset($_POST['action']) && $_POST['action'] === 'toggle_block') {
    $email_cible = $_POST['email'];
    $fichier_users = "data/utilisateurs.txt";
    $reponse = ['success' => false, 'nouveau_statut' => 0];

    if (file_exists($fichier_users)) {
        $lignes = file($fichier_users, FILE_IGNORE_NEW_LINES);
        $nouvelles_lignes = [];
        
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (count($cols) >= 8 && trim($cols[0]) === $email_cible) {
                // On inverse le statut de blocage (colonne 7)
                $cols[7] = (trim($cols[7]) === '1') ? '0' : '1';
                $reponse['nouveau_statut'] = $cols[7];
                $reponse['success'] = true;
            }
            $nouvelles_lignes[] = implode(";", $cols);
        }
        file_put_contents($fichier_users, implode("\n", $nouvelles_lignes));
    }
    
    header('Content-Type: application/json');
    echo json_encode($reponse);
    exit();
}

$liste_utilisateurs = [];
if (file_exists("data/utilisateurs.txt")) {
    $lignes = file("data/utilisateurs.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lignes as $ligne) {
        $cols = explode(";", $ligne);
        if (count($cols) >= 8) {
            $liste_utilisateurs[] = [
                'email' => trim($cols[0]),
                'role' => trim($cols[2]),
                'nom' => trim($cols[3]),
                'prenom' => trim($cols[4]),
                'points' => trim($cols[6]),
                'bloque' => trim($cols[7])
            ];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Administration - Les délices de fafa</title>
    <?php
    $fichier_css = "style.css";
    if (isset($_COOKIE['theme']) && $_COOKIE['theme'] == 'sombre') {
        $fichier_css = "style-sombre.css";
    }
    ?>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>">
</head>
<body class="page-admin">
    <header>
        <h1 class="header-title">Panneau d'Administration 🛡️</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil du site</a></li>
                <li><a href="produits.php">🍲 Gérer la Carte</a></li>
                <li><a href="deconnexion.php">🚪 Déconnexion</a></li>
                <li><button class="btn-theme" onclick="basculerTheme()" title="Changer le thème">🌗</button></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2 class="text-center">Gestion des Utilisateurs</h2>
        <p class="text-center mb-20">Gérez les profils, les statuts et les restrictions d'accès.</p>
        
        <section class="profil-block">
            <table class="profile-table">
                <tr class="table-header-row">
                    <th>Utilisateur</th>
                    <th>Rôle</th>
                    <th>Points</th>
                    <th>Actions Profil</th>
                    <th>Sécurité</th>
                </tr>
                
                <?php foreach ($liste_utilisateurs as $user): ?>
                <tr id="ligne-<?php echo md5($user['email']); ?>" class="<?php echo ($user['bloque'] == '1') ? 'ligne-bloquee' : ''; ?>">
                    <td>
                        <strong><?php echo htmlspecialchars($user['nom'] . " " . $user['prenom']); ?></strong><br>
                        <small class="text-muted-sm"><?php echo htmlspecialchars($user['email']); ?></small>
                    </td>
                    <td><span class="badge"><?php echo strtoupper(htmlspecialchars($user['role'])); ?></span></td>
                    <td><strong><?php echo htmlspecialchars($user['points']); ?></strong> pts</td>
                    <td>
                        <select class="select-sm" onchange="changerStatut('<?php echo htmlspecialchars($user['email']); ?>', this.value)">
                            <option value="">Modifier statut...</option>
                            <option value="premium">Passer Premium</option>
                            <option value="vip">Passer VIP </option>
                        </select>
                    </td>
                    <td>
                        <?php if ($user['bloque'] == '1'): ?>
                            <button onclick="bloquerUtilisateur('<?php echo htmlspecialchars($user['email']); ?>', this)" class="btn btn-green btn-sm w-100">Débloquer</button>
                        <?php else: ?>
                            <button onclick="bloquerUtilisateur('<?php echo htmlspecialchars($user['email']); ?>', this)" class="btn btn-red btn-sm w-100">Bloquer</button>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </section>
    </main>

    <script src="script.js"></script>
    <script>
        function bloquerUtilisateur(email, bouton) {
            bouton.disabled = true;
            let texteOriginal = bouton.innerText;
            bouton.innerText = "...";

            let formData = new FormData();
            formData.append('action', 'toggle_block');
            formData.append('email', email);

            fetch('admin.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                bouton.disabled = false;
                if (data.success) {
                    let ligne = bouton.closest('tr');
                    
                    if (data.nouveau_statut == '1') {
                        bouton.innerText = "Débloquer";
                        bouton.className = "btn btn-green btn-sm w-100";
                        ligne.classList.add('ligne-bloquee');
                    } else { 
                        bouton.innerText = "Bloquer";
                        bouton.className = "btn btn-red btn-sm w-100";
                        ligne.classList.remove('ligne-bloquee');
                    }
                } else {
                    alert("Erreur lors de la modification.");
                    bouton.innerText = texteOriginal;
                }
            })
            .catch(error => {
                alert("Erreur de connexion réseau.");
                bouton.disabled = false;
                bouton.innerText = texteOriginal;
            });
        }
        
        function changerStatut(email, nouveauStatut) {
            if(nouveauStatut !== "") {
                alert("Fonctionnalité prête à être développée avec Fetch pour le statut " + nouveauStatut);
            }
        }
    </script>
</body>
</html>