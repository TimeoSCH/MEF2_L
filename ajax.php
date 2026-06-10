<?php
session_start();
header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$action = isset($data['action']) ? $data['action'] : '';
$fichier_users = "data/utilisateurs.txt";

// 1. MODIFICATION DU PROFIL
if ($action === 'modifier_profil') {
    if (!isset($_SESSION['email'])) {
        echo json_encode(['success' => false, 'message' => 'Erreur: Non connecté.']);
        exit;
    }
    
    $nom = trim($data['nom']);
    $prenom = trim($data['prenom']);
    $adresse = trim($data['adresse']);
    
    if (file_exists($fichier_users)) {
        $lignes = file($fichier_users, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nouvelles_lignes = [];
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (count($cols) >= 7 && trim($cols[0]) === $_SESSION['email']) {
                $cols[3] = $nom;
                $cols[4] = $prenom;
                $cols[5] = $adresse;
                $_SESSION['nom'] = $nom;
                $_SESSION['prenom'] = $prenom;
                $_SESSION['adresse'] = $adresse;
            }
            $nouvelles_lignes[] = implode(";", $cols);
        }
        file_put_contents($fichier_users, implode("\n", $nouvelles_lignes));
        echo json_encode(['success' => true, 'message' => '✅ Vos informations ont été mises à jour !']);
        exit;
    }
}

// 2. BLOCAGE D'UN UTILISATEUR
if ($action === 'bloquer_utilisateur') {
    if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
        exit;
    }
    
    $email_cible = trim($data['email']);
    
    if (file_exists($fichier_users)) {
        $lignes = file($fichier_users, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nouvelles_lignes = [];
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (trim($cols[0]) === $email_cible && strtolower(trim($cols[2])) !== 'admin') {
                $cols = array_pad($cols, 8, "");
                $cols[7] = 'bloque';
            }
            $nouvelles_lignes[] = implode(";", $cols);
        }
        file_put_contents($fichier_users, implode("\n", $nouvelles_lignes));
        echo json_encode(['success' => true, 'message' => 'Utilisateur bloqué avec succès.']);
        exit;
    }
}

// 3. DÉBLOCAGE D'UN UTILISATEUR (NOUVEAU)
if ($action === 'debloquer_utilisateur') {
    if (!isset($_SESSION['role']) || strtolower($_SESSION['role']) !== 'admin') {
        echo json_encode(['success' => false, 'message' => 'Accès refusé.']);
        exit;
    }
    
    $email_cible = trim($data['email']);
    
    if (file_exists($fichier_users)) {
        $lignes = file($fichier_users, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $nouvelles_lignes = [];
        foreach ($lignes as $ligne) {
            $cols = explode(";", $ligne);
            if (trim($cols[0]) === $email_cible && strtolower(trim($cols[2])) !== 'admin') {
                $cols = array_pad($cols, 8, "");
                $cols[7] = 'actif'; // On remplace "bloque" par "actif"
            }
            $nouvelles_lignes[] = implode(";", $cols);
        }
        file_put_contents($fichier_users, implode("\n", $nouvelles_lignes));
        echo json_encode(['success' => true, 'message' => 'Utilisateur débloqué avec succès.']);
        exit;
    }
}

echo json_encode(['success' => false, 'message' => 'Action inconnue.']);
?>