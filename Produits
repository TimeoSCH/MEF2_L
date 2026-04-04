<?php
session_start();

if (!isset($_SESSION['panier'])) {
    $_SESSION['panier'] = [];
}

$message_panier = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_plat'])) {
    $id = $_POST['id_plat'];
    $nom = $_POST['nom_plat'];
    $prix = $_POST['prix_plat'];

    $trouve = false;
    foreach ($_SESSION['panier'] as &$item) {
        if ($item['id'] == $id) {
            $item['quantite'] += 1;
            $trouve = true;
            break;
        }
    }

    if (!$trouve) {
        $_SESSION['panier'][] = [
            'id' => $id,
            'nom' => $nom,
            'prix' => $prix,
            'quantite' => 1
        ];
    }
    
    $message_panier = "✅ " . $nom . " a été ajouté à votre panier !";
}

$entrees = [];
$plats = [];
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
                    'prix' => $infos[4],
                    'allergene' => $infos[5],
                    'image' => $infos[6]
                ];
                if ($item['categorie'] == 'entree') {
                    $entrees[] = $item;
                } elseif ($item['categorie'] == 'plat') {
                    $plats[] = $item;
                } elseif ($item['categorie'] == 'dessert' || $item['categorie'] == 'boisson') {
                    $desserts_boissons[] = $item;
                }
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
    <title>La Carte - Les délices de fafa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
   <header>
        <h1 class="header-title">Les délices de fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="produits.php">🍲 La Carte</a></li>
                <li><a href="panier.php" class="link-panier">🛒 Mon Panier (<?php echo array_sum(array_column($_SESSION['panier'], 'quantite')); ?>)</a></li>
                <li><a href="inscription.php">📝 Inscription</a></li>
                <li><a href="connexion.php">🔑 Connexion</a></li>
                <li><a href="profil.php">👤 Mon Profil</a></li>
                <li><a href="commandes.php">👨‍🍳 Cuisine</a></li>
                <li><a href="livraison.php">🛵 Livraison</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <h2 class="text-center mb-20">Notre Carte Marocaine</h2>
        
        <?php if (!empty($message_panier)): ?>
            <p class="msg-success">
                <?php echo $message_panier; ?>
            </p>
        <?php endif; ?>

        <div class="layout-produits">
            <aside class="filtres">
                <h3 class="filter-title">Filtrer par</h3>
                <h4 class="filter-subtitle">Catégories</h4>
                <ul>
                    <li><input type="checkbox" id="cat-entrees"> <label for="cat-entrees">Entrées</label></li>
                    <li><input type="checkbox" id="cat-plats"> <label for="cat-plats">Plats</label></li>
                    <li><input type="checkbox" id="cat-desserts"> <label for="cat-desserts">Desserts & Boissons</label></li>
                </ul>
                <h4 class="filter-subtitle">Allergènes</h4>
                <ul>
                    <li><input type="checkbox" id="sans-gluten"> <label for="sans-gluten">Sans Gluten</label></li>
                    <li><input type="checkbox" id="vegetarien"> <label for="vegetarien">Végétarien</label></li>
                </ul>
                <button class="btn w-100 mt-15">Appliquer</button>
            </aside>

            <section class="section-menu section-full-width">
                
                <h3 class="section-title">Les Entrées</h3>
                <div class="card-grid mb-40">
                    <?php foreach ($entrees as $entree): ?>
                    <article class="card">
                        <img src="<?php echo $entree['image']; ?>" alt="<?php echo $entree['nom']; ?>">
                        <h4><?php echo $entree['nom']; ?></h4>
                        <p><?php echo $entree['description']; ?></p>
                        <p class="mt-10"><strong><?php echo $entree['prix']; ?> €</strong></p>
                        
                        <form action="produits.php" method="post">
                            <input type="hidden" name="id_plat" value="<?php echo $entree['id']; ?>">
                            <input type="hidden" name="nom_plat" value="<?php echo $entree['nom']; ?>">
                            <input type="hidden" name="prix_plat" value="<?php echo $entree['prix']; ?>">
                            <button type="submit" class="btn w-100 mt-10">Ajouter</button>
                        </form>
                    </article>
                    <?php endforeach; ?>
                </div>

                <h3 class="section-title">Les Plats</h3>
                <div class="card-grid mb-40">
                    <?php foreach ($plats as $plat): 
                        $is_coeur = ($plat['id'] == 'P1' || $plat['id'] == 'P2');
                    ?>
                    <article class="card <?php echo $is_coeur ? 'card-coeur' : ''; ?>">
                        <?php if ($is_coeur): ?>
                            <span class="badge-coeur">❤️ Coup de cœur</span>
                        <?php endif; ?>
                        <img src="<?php echo $plat['image']; ?>" alt="<?php echo $plat['nom']; ?>">
                        <h4><?php echo $plat['nom']; ?></h4>
                        <p><?php echo $plat['description']; ?></p>
                        <p class="mt-10"><strong><?php echo $plat['prix']; ?> €</strong></p>
                        
                        <form action="produits.php" method="post">
                            <input type="hidden" name="id_plat" value="<?php echo $plat['id']; ?>">
                            <input type="hidden" name="nom_plat" value="<?php echo $plat['nom']; ?>">
                            <input type="hidden" name="prix_plat" value="<?php echo $plat['prix']; ?>">
                            <button type="submit" class="btn w-100 mt-10">Ajouter</button>
                        </form>
                    </article>
                    <?php endforeach; ?>
                </div>

                <h3 class="section-title">Les Desserts & Boissons</h3>
                <div class="card-grid">
                    <?php foreach ($desserts_boissons as $db): 
                        $is_coeur = ($db['id'] == 'B1');
                    ?>
                    <article class="card <?php echo $is_coeur ? 'card-coeur' : ''; ?>">
                        <?php if ($is_coeur): ?>
                            <span class="badge-coeur">❤️ Coup de cœur</span>
                        <?php endif; ?>
                        <img src="<?php echo $db['image']; ?>" alt="<?php echo $db['nom']; ?>">
                        <h4><?php echo $db['nom']; ?></h4>
                        <p><?php echo $db['description']; ?></p>
                        <p class="mt-10"><strong><?php echo $db['prix']; ?> €</strong></p>
                        
                        <form action="produits.php" method="post">
                            <input type="hidden" name="id_plat" value="<?php echo $db['id']; ?>">
                            <input type="hidden" name="nom_plat" value="<?php echo $db['nom']; ?>">
                            <input type="hidden" name="prix_plat" value="<?php echo $db['prix']; ?>">
                            <button type="submit" class="btn w-100 mt-10">Ajouter</button>
                        </form>
                    </article>
                    <?php endforeach; ?>
                </div>

            </section>
        </div>
    </main>
    <footer>
        <p>&copy; 2025-2026 Les délices de fafa - Projet Creative Yumland</p>
    </footer>
</body>
</html>
