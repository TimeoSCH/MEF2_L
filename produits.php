<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
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


if (isset($_POST['action']) && $_POST['action'] === 'ajouter_panier') {
    $id = $_POST['id_plat'];
    $nom = $_POST['nom_plat'];
    $prix = $_POST['prix_plat'];

    $trouve = false;
    foreach ($_SESSION['panier'] as &$item_panier) { 
        if ($item_panier['id'] == $id) {
            $item_panier['quantite'] += 1;
            $trouve = true;
            break;
        }
    }
    unset($item_panier); 

    if (!$trouve) {
        $_SESSION['panier'][] = ['id' => $id, 'nom' => $nom, 'prix' => $prix, 'quantite' => 1];
    }
    
    // On calcule le nouveau nombre total d'articles dans le panier
    $total_articles = array_sum(array_column($_SESSION['panier'], 'quantite'));

    // On renvoie les données en JSON au Javascript
    header('Content-Type: application/json');
    echo json_encode([
        "success" => true,
        "message" => "✅ " . $nom . " a été ajouté à votre panier !",
        "total_articles" => $total_articles
    ]);
    exit();
}

// ------------------------------------------------------------------
// 2. REQUÊTE ASYNCHRONE : FILTRER LA CARTE SANS RECHARGER LA PAGE
// ------------------------------------------------------------------
if (isset($_POST['action']) && $_POST['action'] === 'filtrer_asynchrone') {
    $filtres = json_decode($_POST['filtres'], true);
    $html_resultat = "";
    
    if (file_exists("data/plats.txt")) {
        $fichier = fopen("data/plats.txt", "r");
        while (!feof($fichier)) {
            $ligne = trim(fgets($fichier));
            if (!empty($ligne)) {
                $infos = explode(";", $ligne);
                if (count($infos) >= 7) {
                    $categorie = strtolower($infos[1]);
                    $tags = strtolower($infos[5] . ' ' . $infos[3]);
                    
                    $correspond_aux_filtres = true;
                    
                    if (!empty($filtres)) {
                        foreach ($filtres as $mot_cle) {
                            if (strpos($categorie, $mot_cle) === false && strpos($tags, $mot_cle) === false) {
                                $correspond_aux_filtres = false;
                                break;
                            }
                        }
                    }
                    
                    if ($correspond_aux_filtres) {
                        $prix_format = number_format((float)$infos[4], 2);
                        $id = htmlspecialchars($infos[0]);
                        $nom = htmlspecialchars($infos[2]);
                        $img = htmlspecialchars($infos[6]);
                        $desc = htmlspecialchars($infos[3]);
                        
                        // Le formulaire généré ici utilise aussi l'ajout asynchrone (onsubmit)
                        $html_resultat .= "
                        <article class='card plat-card' data-prix='{$infos[4]}'>
                            <img src='{$img}' alt='{$nom}'>
                            <h4>{$nom}</h4>
                            <p>{$desc}</p>
                            <p class='mt-10'><strong>{$prix_format} €</strong></p>
                            <form onsubmit='ajouterAuPanierAjax(event)'>
                                <input type='hidden' name='id_plat' value='{$id}'>
                                <input type='hidden' name='nom_plat' value='{$nom}'>
                                <input type='hidden' name='prix_plat' value='{$infos[4]}'>
                                <button type='submit' class='btn w-100 mt-10'>Ajouter</button>
                            </form>
                        </article>";
                    }
                }
            }
        }
        fclose($fichier);
    }
    
    if (empty($html_resultat)) {
        $html_resultat = "<p class='text-center w-100'>Aucun plat ne correspond à vos critères. 😔</p>";
    } else {
        $html_resultat = "<div class='card-grid mb-40'>" . $html_resultat . "</div>";
    }
    
    header('Content-Type: application/json');
    echo json_encode(["html" => $html_resultat]);
    exit();
}

// --- CHARGEMENT NORMAL DE LA PAGE ---
$entrees = [];
$plats_principaux = [];
$desserts_boissons = [];

if (file_exists("data/plats.txt")) {
    $fichier = fopen("data/plats.txt", "r");
    while (!feof($fichier)) {
        $ligne = trim(fgets($fichier));
        if (!empty($ligne)) {
            $infos = explode(";", $ligne);
            if (count($infos) >= 7) {
                $item = [
                    'id' => $infos[0],
                    'categorie' => $infos[1],
                    'nom' => $infos[2],
                    'description' => $infos[3],
                    'prix' => (float)$infos[4],
                    'allergene' => $infos[5],
                    'image' => $infos[6]
                ];
                
                if ($item['categorie'] == 'entree') {
                    $entrees[] = $item;
                } elseif ($item['categorie'] == 'plat') {
                    $plats_principaux[] = $item;
                } elseif ($item['categorie'] == 'dessert' || $item['categorie'] == 'boisson') {
                    $desserts_boissons[] = $item;
                }
            }
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
    <title>La Carte - Les délices de fafa</title>
    <link id="theme-style" rel="stylesheet" href="<?php echo $fichier_css; ?>?t=<?php echo time(); ?>">
</head>
<body class="page-produits">
   <header>
        <h1 class="header-title">Les délices de Fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <?php 
                $role_nav = isset($_SESSION['role']) ? strtolower(trim($_SESSION['role'])) : '';
                
                if ($role_nav === 'admin'): 
                ?>
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
        <h2 class="text-center mb-20">Notre Carte Marocaine</h2>
        
        <div id="msg-panier-ajax" class="msg-success-bold" style="display: none; text-align: center; margin-bottom: 20px;"></div>
        
        <div class="layout-produits">
            <aside class="filtres">
                <h3 class="filter-title">Trier & Filtrer</h3>
                <hr class="mt-10 mb-10">
                <h4 class="filter-subtitle text-sm">Trier par :</h4>
                <select id="tri_produits" class="select-sort" onchange="trierPlats()">
                    <option value="defaut">Ordre par défaut</option>
                    <option value="prix_croissant">Prix : Croissant</option>
                    <option value="prix_decroissant">Prix : Décroissant</option>
                </select>

                <hr class="mt-10 mb-10">

                <div class="filter-group">
                    <h4 class="filter-subtitle text-sm">Catégories</h4>
                    <ul>
                        <li><input type="checkbox" value="entree" class="filtre-box"> <label>Entrées</label></li>
                        <li><input type="checkbox" value="plat" class="filtre-box"> <label>Plats</label></li>
                        <li><input type="checkbox" value="dessert" class="filtre-box"> <label>Desserts</label></li>
                    </ul>
                </div>

                <div class="filter-group">
                    <h4 class="filter-subtitle text-sm">Régimes & Allergènes</h4>
                    <ul>
                        <li><input type="checkbox" value="vegetarien" class="filtre-box"> <label>Végétarien</label></li>
                        <li><input type="checkbox" value="sans-gluten" class="filtre-box"> <label>Sans Gluten</label></li>
                    </ul>
                </div>
                
                <div class="filter-group">
                    <h4 class="filter-subtitle text-sm">Saveurs</h4>
                    <ul>
                        <li><input type="checkbox" value="sale" class="filtre-box"> <label>Salé</label></li>
                        <li><input type="checkbox" value="sucre" class="filtre-box"> <label>Sucré</label></li>
                        <li><input type="checkbox" value="epice" class="filtre-box"> <label>Épicé</label></li>
                    </ul>
                </div>

                <button class="btn btn-blue w-100 mt-15" onclick="filtrerAsynchrone()" id="btn-filtre">Appliquer les filtres</button>
            </aside>

            <section class="section-menu" id="conteneur-plats">
                <?php if(!empty($entrees)): ?>
                    <h3 class="section-title">Les Entrées</h3>
                    <div class="card-grid mb-40">
                        <?php foreach ($entrees as $plat): ?>
                            <?php inclure_carte_plat($plat); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if(!empty($plats_principaux)): ?>
                    <h3 class="section-title">Les Plats</h3>
                    <div class="card-grid mb-40">
                        <?php foreach ($plats_principaux as $plat): ?>
                            <?php inclure_carte_plat($plat); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if(!empty($desserts_boissons)): ?>
                    <h3 class="section-title">Les Desserts & Boissons</h3>
                    <div class="card-grid mb-40">
                        <?php foreach ($desserts_boissons as $plat): ?>
                            <?php inclure_carte_plat($plat); ?>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
        </div>
    </main>

    <?php
    function inclure_carte_plat($plat) {
        $prix_format = number_format($plat['prix'], 2);
        echo "
        <article class='card plat-card' data-prix='{$plat['prix']}'>
            <img src='{$plat['image']}' alt='{$plat['nom']}'>
            <h4>{$plat['nom']}</h4>
            <p>{$plat['description']}</p>
            <p class='mt-10'><strong>{$prix_format} €</strong></p>
            <form onsubmit='ajouterAuPanierAjax(event)'>
                <input type='hidden' name='id_plat' value='{$plat['id']}'>
                <input type='hidden' name='nom_plat' value='{$plat['nom']}'>
                <input type='hidden' name='prix_plat' value='{$plat['prix']}'>
                <button type='submit' class='btn w-100 mt-10'>Ajouter</button>
            </form>
        </article>";
    }
    ?>

    <script>
        // 1. Fonction pour ajouter au panier sans rechargement
        function ajouterAuPanierAjax(event) {
            event.preventDefault(); // Bloque le rechargement brutal de la page
            
            const form = event.target;
            const formData = new FormData(form);
            formData.append('action', 'ajouter_panier'); // On indique à PHP ce qu'on veut faire

            fetch('produits.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Met à jour le (0) à côté du panier dans le menu
                    const spanPanier = document.getElementById('compteur-panier');
                    if (spanPanier) {
                        spanPanier.innerText = "(" + data.total_articles + ")";
                    }
                    
                    // Affiche le message vert de succès
                    const msgBox = document.getElementById('msg-panier-ajax');
                    msgBox.innerText = data.message;
                    msgBox.style.display = 'block';
                    
                    // Fait disparaître le message après 3 secondes
                    setTimeout(() => { msgBox.style.display = 'none'; }, 3000);
                }
            })
            .catch(error => console.error('Erreur réseau:', error));
        }

        // 2. Fonction de Tri
        function trierPlats() {
            const select = document.getElementById('tri_produits').value;
            const conteneur = document.getElementById('conteneur-plats');
            const plats = Array.from(conteneur.getElementsByClassName('plat-card'));

            if (select === 'prix_croissant') {
                plats.sort((a, b) => parseFloat(a.dataset.prix) - parseFloat(b.dataset.prix));
                conteneur.innerHTML = '<div class="card-grid mb-40"></div>';
                const grid = conteneur.querySelector('.card-grid');
                plats.forEach(plat => grid.appendChild(plat));
            } else if (select === 'prix_decroissant') {
                plats.sort((a, b) => parseFloat(b.dataset.prix) - parseFloat(a.dataset.prix));
                conteneur.innerHTML = '<div class="card-grid mb-40"></div>';
                const grid = conteneur.querySelector('.card-grid');
                plats.forEach(plat => grid.appendChild(plat));
            } else {
                window.location.reload();
            }
        }

        // 3. Fonction de Filtre Asynchrone
        function filtrerAsynchrone() {
            const checkboxes = document.querySelectorAll('.filtre-box:checked');
            let valeursCochees = [];
            checkboxes.forEach((cb) => valeursCochees.push(cb.value));

            if (valeursCochees.length === 0) {
                window.location.reload();
                return;
            }

            const btn = document.getElementById('btn-filtre');
            btn.innerText = "Recherche... ⏳";

            let formData = new FormData();
            formData.append('action', 'filtrer_asynchrone');
            formData.append('filtres', JSON.stringify(valeursCochees));

            fetch('produits.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                document.getElementById('conteneur-plats').innerHTML = data.html;
                btn.innerText = "Appliquer les filtres";
                
                if (document.getElementById('tri_produits').value !== 'defaut') {
                    trierPlats();
                }
            })
            .catch(error => {
                alert('Erreur réseau.');
                btn.innerText = "Appliquer les filtres";
            });
        }
    </script>
    <script src="script.js?t=<?php echo time(); ?>"></script>
</body>
</html>