<?php 
session_start(); 
require("../includes/GestionBD.php"); 

// Affichage selon les infos de l'utilisateur en question
$parametre_key = 'user_id';
$ViewUserId = 0;

if(isset($_GET[$parametre_key])){
    $ViewUserId = (int) $_GET[$parametre_key];
}

if($ViewUserId <= 0){
    die("ID utilisateur invalide ou manquant.");  
}

// REMPLACEMENT DE TOUT LE BLOC SQL PAR LA FONCTION
$viewUserInfo = GetUserInfoById($ViewUserId);

if(!$viewUserInfo){
    die("Utilisateur non trouvé.");
}

$averageNote = AverageUserNote($ViewUserId);
$userNotes = UserNotes($ViewUserId);

// PLUS DE SQL POUR LES AUTEURS ICI NON PLUS
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil de <?= htmlspecialchars($viewUserInfo['first_name'] ?? 'Utilisateur')?></title>
    <link rel="stylesheet" href="../css/styleProfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require("../includes/header.php") ?>

    <main class="main-content">

        <h1 class="Titre">Profil de <?= htmlspecialchars($viewUserInfo['first_name'] ?? 'Utilisateur')?></h1>
        
        <div class="profile-container">
            
            <div class="profile-header">
                <img src="../images/Profil_Picture.png" alt="Photo" class="profile-photo">

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
                        <span class="rating-number">Note : <?php echo number_format($averageNote, 1); ?>/5</span>
                    </div>
                </div>
            </div>
            
            <section class="vehicle-info">
                <h2>Véhicule du conducteur</h2>
                <div class="input-group">
                    <label>Modèle :</label>
                    <p><strong><?= htmlspecialchars($viewUserInfo['vehicle_model'] ?? 'Non renseigné') ?></strong></p>
                </div>
                <div class="input-group">
                    <label>Couleur :</label>
                    <p><strong><?= htmlspecialchars($viewUserInfo['vehicle_color'] ?? 'Non renseigné') ?></strong></p>
                </div>
            </section>

            <section class="comments-section">
                <h2>Avis et Commentaires (<?php echo count($userNotes); ?>)</h2>
                <?php if (count($userNotes) > 0): ?>
                    <div class="comments-list">
                        <?php foreach ($userNotes as $note): 
                            // UTILISATION DE LA FONCTION DANS LA BOUCLE
                            $authorInfos = GetUserInfoById($note['author_note']);
                            $authorName = $authorInfos ? $authorInfos['first_name'] . " " . $authorInfos['last_name'] : "Utilisateur inconnu";
                            
                            $authorId = $note['author_note'];
                            $currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
                            $profilLink = ($authorId == $currentUserId) ? "profil.php" : "profilOther.php?user_id=" . $authorId;
                        ?>
                            <div class="comment-card">
                                <div class="comment-header">
                                    <div class="comment-info">
                                        <span class="comment-note">Note : <?php echo $note['note']; ?>/5</span>
                                        <a href="<?php echo $profilLink; ?>" class="comment-author" style="text-decoration:none; color:#ff6600; font-weight:bold; font-style:normal;">
                                            <?php echo htmlspecialchars($authorName); ?>
                                        </a>
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
    </main>

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

    <?php require("../includes/footer.php") ?>

    <script>
        function openReportModal(authorId) {
            document.getElementById("reportModal").style.display = "block";
            document.getElementById("modal_reported_user_id").value = authorId;
        }

        function closeReportModal() {
            document.getElementById("reportModal").style.display = "none";
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById("reportModal")) {
                closeReportModal();
            }
        }
    </script>
</body>
</html>