<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();

if (!isset($_SESSION['email']) || strtolower($_SESSION['role']) !== 'admin') {
    header("Location: index.php");
    exit();
}

$fichier_users = "data/utilisateurs.txt";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action']) && $_POST['action'] === 'changer_role') {
    $email_cible = $_POST['email_cible'];
    $nouveau_role = strtolower($_POST['nouveau_role']);

    if (file_exists($fichier_users)) {
        $lignes = file($fichier_users, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nouvelles_lignes = [];
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (trim($cols[0]) === $email_cible && strtolower(trim($cols[2])) !== 'admin') {
                $cols[2] = $nouveau_role; 
            }
            $nouvelles_lignes[] = implode(";", $cols);
        }
        file_put_contents($fichier_users, implode("\n", $nouvelles_lignes));
    }
    header("Location: admin.php");
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
    <title>Panneau d'Administration - Les délices de fafa</title>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>?t=<?php echo time(); ?>">
</head>
<body class="page-admin">
    <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil du site</a></li>
                <li><a href="produits.php">🍲 Gérer la Carte</a></li>
                <li><a href="deconnexion.php">🚪 Déconnexion</a></li>
                <li><button class="btn-theme" onclick="basculerTheme()">🌗</button></li>
            </ul>
        </nav>
    </header>

    <main class="admin-main">
        <h2 class="text-center mb-10 mt-20">Gestion des Utilisateurs</h2>
        <p class="text-center mb-20">Gérez les profils, les statuts et les restrictions d'accès.</p>

        <section class="card admin-section">
            <table class="profile-table admin-table">
                <thead>
                    <tr class="table-header-row admin-header-row">
                        <th>Utilisateur</th>
                        <th>Rôle</th>
                        <th>Points</th>
                        <th>Actions Profil</th>
                        <th>Sécurité</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (file_exists($fichier_users)) {
                        $lignes = file($fichier_users, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                        foreach ($lignes as $ligne) {
                            $cols = explode(";", $ligne);
                            if (count($cols) >= 7) {
                                $email = htmlspecialchars(trim($cols[0]));
                                $prenom = htmlspecialchars(trim($cols[4]));
                                $nom = htmlspecialchars(trim($cols[3]));
                                $role = strtolower(trim($cols[2]));
                                $points = (int)$cols[6];
                                $est_bloque = (isset($cols[7]) && trim($cols[7]) === 'bloque');

                                echo "<tr>";
                                echo "<td><strong>$prenom $nom</strong><br><span class='text-muted-sm'>$email</span></td>";
                                echo "<td><span class='badge-role role-$role'>".strtoupper($role)."</span></td>";
                                echo "<td class='points-cell'><strong>$points</strong> pts</td>";
                                
                                // Menu des rôles
                                echo "<td>";
                                if ($role !== 'admin' && $role !== 'restaurateur' && $role !== 'livreur') {
                                    echo "<form action='admin.php' method='post' class='form-inline'>
                                            <input type='hidden' name='action' value='changer_role'>
                                            <input type='hidden' name='email_cible' value='$email'>
                                            <select name='nouveau_role' class='input-sm select-role' onchange='this.form.submit()'>
                                                <option value='client' ".($role=='client'?'selected':'').">Client normal</option>
                                                <option value='premium' ".($role=='premium'?'selected':'').">Premium (-10%)</option>
                                                <option value='vip' ".($role=='vip'?'selected':'').">VIP (-20%)</option>
                                            </select>
                                          </form>";
                                } elseif ($role !== 'admin') {
                                    echo "<span class='text-muted-sm text-italic'>Employé</span>";
                                } else {
                                    echo "<span class='text-muted-sm text-italic'>Intouchable</span>";
                                }
                                echo "</td>";

                                // Bouton de Sécurité
                                echo "<td>";
                                if ($role !== 'admin') {
                                    if ($est_bloque) {
                                        echo "<button type='button' class='btn-action-admin btn-debloquer' onclick=\"basculerBlocageAjax('$email', 'debloquer', this)\">Débloquer ✅</button>";
                                    } else {
                                        echo "<button type='button' class='btn-action-admin btn-bloquer' onclick=\"basculerBlocageAjax('$email', 'bloquer', this)\">Bloquer 🚫</button>";
                                    }
                                }
                                echo "</td>";
                                
                                echo "</tr>";
                            }
                        }
                    }
                    ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        async function basculerBlocageAjax(email, typeAction, btnElement) {
            const actionAjax = typeAction === 'bloquer' ? 'bloquer_utilisateur' : 'debloquer_utilisateur';
            const msgConfirm = typeAction === 'bloquer' ? "Voulez-vous vraiment bloquer " + email + " ?" : "Voulez-vous débloquer " + email + " ?";
            
            if(!confirm(msgConfirm)) return;
            
            try {
                const response = await fetch('ajax.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: actionAjax, email: email })
                });
                const result = await response.json();
                
                if(result.success) {
                    if(typeAction === 'bloquer') {
                        btnElement.className = 'btn-action-admin btn-debloquer';
                        btnElement.innerHTML = 'Débloquer ✅';
                        btnElement.setAttribute('onclick', `basculerBlocageAjax('${email}', 'debloquer', this)`);
                    } else {
                        btnElement.className = 'btn-action-admin btn-bloquer';
                        btnElement.innerHTML = 'Bloquer 🚫';
                        btnElement.setAttribute('onclick', `basculerBlocageAjax('${email}', 'bloquer', this)`);
                    }
                } else {
                    alert(result.message);
                }
            } catch (error) {
                console.error('Erreur Fetch:', error);
            }
        }
    </script>
    <script src="script.js?t=<?php echo time(); ?>"></script>
</body>
</html>