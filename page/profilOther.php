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
    //UPDATE
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
                
                <div class="rating-container">
                    <div class="cars-wrapper">
                        <?php 
                        $roundedNote = round($averageNote);
                        for ($i = 1; $i <= 5; $i++) {
                            echo ($i <= $roundedNote) ? 
                                '<i class="fa-solid fa-car car-icon filled"></i>' : 
                                '<i class="fa-solid fa-car car-icon empty"></i>';
                        }
                        ?>
                    </div>
                    <span class="rating-number"><?php echo number_format($averageNote, 1); ?>/5</span>
                </div>

                </div>

            </div>
        
        <section class="vehicle-info">
            <h2>Véhicule du conducteur</h2>
            <p>Modèle : <strong><?= htmlspecialchars($viewUserInfo['vehicle_model'] ?? 'Non renseigné') ?></strong></p>
            <p>Couleur : <strong><?= htmlspecialchars($viewUserInfo['vehicle_color'] ?? 'Non renseigné') ?></strong></p>
        </section>

        <section class="comments-section">
            <h2>Avis et Commentaires (<?php echo count($userNotes); ?>)</h2>
            <?php if (count($userNotes) > 0): ?>
                <div class="comments-list">
                    <?php foreach ($userNotes as $note): ?>
                        <div class="comment-card">
                            <div class="comment-header">
                                <div class="comment-info">
                                    <span class="comment-note">Note : <?php echo $note['note']; ?>/5</span>
                                    <span class="comment-author">Utilisateur n°<?php echo $note['author_note']; ?></span>
                                </div>
                                <button class="comment-report-btn" onclick="openReportModal(<?php echo $note['author_note']; ?>)" title="Signaler ce commentaire">
                                    <i class="fa-solid fa-triangle-exclamation"></i>
                                </button>
                            </div>
                            <p class="comment-text">"<?php echo htmlspecialchars($note['note_description']); ?>"</p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="no-comments">Aucun avis pour le moment.</p>
            <?php endif; ?>
        </section>

    </div>

    <div id="reportModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeReportModal()">&times;</span>
            <h2>Signaler un commentaire</h2>
            <p>Pourquoi souhaitez-vous signaler cet avis ?</p>
            
            <form action="" method="post">
                <input type="hidden" id="modal_reported_user_id" name="reported_user_id" value="">
                
                <textarea name="reason" rows="4" placeholder="Insultes, spam, contenu inapproprié..." required></textarea>
                
                <button type="submit" name="btn_send_report" class="modal-submit-btn">Envoyer le signalement</button>
            </form>
        </div>
    </div>

        </div>
    </body>
</html>