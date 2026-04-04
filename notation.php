<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'client') {
    header("Location: connexion.php");
    exit();
}

$message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $message = "Merci pour votre évaluation ! Vos points de fidélité ont été mis à jour.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Notation - Les délices de fafa</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1 class="header-title">Les délices de fafa 🇲🇦</h1>
        <nav class="main-nav">
            <ul>
                <li><a href="index.php">🏠 Accueil</a></li>
                <li><a href="profil.php">👤 Mon Profil</a></li>
            </ul>
        </nav>
    </header>
    <main class="main-small">
        <section class="card auth-card panier-card">
            <h2 class="text-center">Évaluer votre commande</h2>
            <p class="text-center mb-20">Commande du 01/02/2026</p>
            
            <?php if ($message): ?>
                <p class="msg-success"><?php echo $message; ?></p>
                <div class="text-center mt-15"><a href="profil.php" class="btn">Retour au profil</a></div>
            <?php else: ?>
                <form action="notation.php" method="post">
                    <label class="form-label">Note sur 5 étoiles :</label>
                    <select name="note" required class="notation-select">
                        <option value="5">⭐⭐⭐⭐⭐ - Excellent</option>
                        <option value="4">⭐⭐⭐⭐ - Très bien</option>
                        <option value="3">⭐⭐⭐ - Moyen</option>
                        <option value="2">⭐⭐ - Décevant</option>
                        <option value="1">⭐ - Mauvais</option>
                    </select>

                    <label class="form-label mt-15">Laissez un commentaire :</label>
                    <textarea name="commentaire" rows="4" placeholder="Votre avis..."></textarea>

                    <button type="submit" class="btn btn-green w-100 mt-15">Envoyer mon avis</button>
                </form>
            <?php endif; ?>
        </section>
    </main>
</body>
</html>
