<?php
session_start();
$message_succes = "";
$message_erreur = "";

// 1. Vérifier si le formulaire a été soumis via la méthode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 2. Récupérer les données du formulaire
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $adresse = trim($_POST['adresse']);
    $mdp = trim($_POST['password']);
    
    // On vérifie que les champs ne sont pas vides
    if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($mdp)) {
        
        // 3. Préparer la ligne de texte avec les séparateurs ";"
        // Format attendu : email;mot_de_passe;role;nom;prenom;adresse;points
        $nouvelle_ligne = "\n" . $email . ";" . $mdp . ";client;" . $nom . ";" . $prenom . ";" . $adresse . ";0";
        
        // 4. Ouvrir le fichier en mode "a" (append = ajouter à la fin du fichier)
        $fichier = fopen("data/utilisateurs.txt", "a");
        
        if ($fichier) {
            fwrite($fichier, $nouvelle_ligne); // Écriture dans le fichier
            fclose($fichier); // Fermeture du fichier (très important !)
            
            $message_succes = "Votre compte a bien été créé ! Vous pouvez vous connecter.";
        } else {
            $message_erreur = "Erreur système : impossible de sauvegarder les données.";
        }
    } else {
        $message_erreur = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Les délices de fafa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
   <header>
        <h1 class="header-title">
            Les délices de fafa 🇲🇦
        </h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="produits.php">🍲 La Carte</a></li>
                <li><a href="connexion.php">🔑 Connexion</a></li>
                <li><a href="profil.php">👤 Mon Profil</a></li>
            </ul>
        </nav>
    </header>
    <main class="main-small">
        
        <h2 class="text-center mb-20">Créer un compte</h2>

        <?php if (!empty($message_succes)): ?>
            <p class="text-center mb-15" style="color: #27ae60; font-weight: bold;">
                <?php echo $message_succes; ?> <br>
                <a href="connexion.php" class="btn mt-10">Aller à la connexion</a>
            </p>
        <?php endif; ?>

        <?php if (!empty($message_erreur)): ?>
            <p class="text-center mb-15 link-secondary-bold"><?php echo $message_erreur; ?></p>
        <?php endif; ?>

        <form action="inscription.php" method="post">
            <label class="form-label">Nom :</label>
            <input type="text" name="nom" required>
            
            <label class="form-label">Prénom :</label>
            <input type="text" name="prenom" required>
            
            <label class="form-label">Adresse E-mail :</label>
            <input type="email" name="email" required>
            
            <label class="form-label">Adresse complète :</label>
            <textarea name="adresse" rows="3" required></textarea>
            
            <label class="form-label">Mot de passe :</label>
            <input type="password" name="password" required>
            
            <button type="submit" class="btn w-100 mt-10">S'inscrire</button>
        </form>
    </main>
</body>
</html>
