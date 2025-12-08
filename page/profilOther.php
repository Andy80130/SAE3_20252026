<?php 
session_start(); 
require("../includes/GestionBD.php"); 
require("../includes/pdoSAE3.php");

// Affichage selon les infos de l'utilisateur en question

$parametre_key = 'user_id';
$ViewUserId = 0;

// [1] CORRECTION : Utiliser le tableau superglobal $_GET
if(isset($_GET[$parametre_key])){
    $ViewUserId = (int) $_GET[$parametre_key]; // [2] CORRECTION : Utiliser $_GET
}

if($ViewUserId <= 0){
    // Utilisation d'un die() est approprié ici si l'ID est requis.
    die("ID utilisateur invalide ou manquant.");  
}

// Récupération ID
global $db;
$ViewUserMail = null;

try{
    $sql_get_mail = "SELECT mail FROM Users WHERE user_id = :id";
    $stmt_mail = $db->prepare($sql_get_mail);
    $stmt_mail->bindParam(':id', $ViewUserId, PDO::PARAM_INT);
    $stmt_mail->execute();
    $ViewUserMail = $stmt_mail->fetchColumn();
}
catch (PDOException $e) {
    die("Erreur lors de la récupération de l'email : " . $e->getMessage());
}

$viewUserInfo = null;
// [3] CORRECTION : Utiliser $ViewUserMail
if($ViewUserMail){ 
    $viewUserInfo = GetUserInfo($ViewUserMail);
    if(!$viewUserInfo){
        die("Utilisateur non trouvé.");
    }
}

$averageNote = AverageUserNote($ViewUserId);
$userNotes = UserNotes($ViewUserId);
// Note: $viewUserInfo est la variable correcte contenant les données
?>


<!DOCTYPE html>
<html lang="fr">
<head>
    <link rel="stylesheet" href="../css/styleProfil.css">
</head>
<body>
    <?php require("../includes/header.php") ?>

    <?php if(isset($_GET['succes'])): ?>
    <?php endif; ?>

    <h1 class="Titre">Profil de <?= htmlspecialchars($viewUserInfo['first_name'] ?? 'Utilisateur')?></h1>
    
    <div class="profile-container">

        <div class="profile-header">
            <div class="profile-info-block">
                <div class="profile-name">
                    <?= htmlspecialchars($viewUserInfo['first_name'] . " " . $viewUserInfo['last_name']); ?>
                </div>
                
                </div>

            </div>
        
        <section class="vehicle-info">
            <h2>Véhicule du conducteur</h2>
            <p>Modèle : <strong><?= htmlspecialchars($viewUserInfo['vehicle_model'] ?? 'Non renseigné') ?></strong></p>
            <p>Couleur : <strong><?= htmlspecialchars($viewUserInfo['vehicle_color'] ?? 'Non renseigné') ?></strong></p>
        </section>

        </div>
    </body>
</html>