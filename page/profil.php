<?php
session_start();
require("../includes/GestionBD.php"); 

// --- 1. SÉCURITÉ & DONNÉES SESSION ---

if (!isset($_SESSION['user_id']) || !isset($_SESSION['mail'])) {
    header('Location: connexion.php');
    exit();
}

$userId = $_SESSION['user_id'];

// --- 2. TRAITEMENT DES FORMULAIRES ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_update_vehicle'])) {
    try {
        if (!TrajetExist($_SESSION['user_id'])) {
            $modele = htmlspecialchars(trim($_POST['modele']));
            $couleur = htmlspecialchars(trim($_POST['couleur']));

            if (UpdateVehicleInfo($userId, $modele, $couleur)) {
                header("Location: profil.php?succes=vehicule");
                exit();
            } else {
                header("Location: profil.php?error=update_failed");
                exit();
            }
        }else {
            header("Location: profil.php?error=trajetExist");
            exit();
        }
    }catch (Exception $e){
        $err = $e->getMessage();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_send_report'])) {
    $reason = htmlspecialchars(trim($_POST['reason']));
    $reportedUserId = intval($_POST['reported_user_id']);
    $reporterId = $userId;

    if (!empty($reason) && $reportedUserId != $reporterId) {
        if (AddReport($reason, $reportedUserId, $reporterId)) {
            header("Location: profil.php?succes=report");
            exit();
        } else {
            header("Location: profil.php?error=report_failed");
            exit();
        }
    } else {
        header("Location: profil.php?error=invalid_report");
        exit();
    }
}

// --- 3. RÉCUPÉRATION DES DONNÉES ---
$userInfo = GetUserInfo($_SESSION['mail']); 
$averageNote = AverageUserNote($userId);
$userNotes = UserNotes($userId); 

// PLUS AUCUNE REQUÊTE SQL ICI
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>StudyGo - Profil</title>
    <link rel="stylesheet" href="../css/styleProfil.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php require("../includes/header.php") ?>

    <main class="main-content">

        <?php if(isset($_GET['succes'])): ?>
            <div class="msg-success" style="background:#d4edda; color:#155724; padding:15px; text-align:center; margin: 20px auto; max-width:800px; border-radius:8px;">
                <?php 
                    if($_GET['succes'] == 'vehicule') echo "Infos véhicule mises à jour !";
                    if($_GET['succes'] == 'report') echo "Signalement envoyé aux administrateurs.";
                ?>
            </div>
        <?php endif; ?>

        <?php if(isset($_GET['error'])): ?>
            <div class="msg-error" style="background-color:#f8d7da; color:#721c24; padding:15px; text-align:center; margin: 20px auto; max-width:800px; border-radius:8px;">
                <?php
                if($_GET['error'] == 'trajetExist') echo "Vous ne pouvez pas changer de véhicule
                 en ayant créé un trajet avec celui-ci !";
                ?>
            </div>
        <?php endif; ?>

        <h1 class="Titre">Profil</h1>
        
        <div class="profile-container">

            <div class="profile-header">
                <img src="../images/Profil_Picture.png" alt="Photo" class="profile-photo">
                
                <div class="profile-info-block">
                    <div class="profile-name">
                        <?php echo htmlspecialchars($userInfo['first_name'] . " " . $userInfo['last_name']); ?>
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

                <?php if (isset($_SESSION['admin_flag']) && $_SESSION['admin_flag'] === 'Y'): ?>
                    <a href="AdminBoard.php" class="admin-dashboard-btn">
                        <i class="fa-solid fa-screwdriver-wrench"></i> AdminBoard
                    </a>
                <?php endif; ?>

            </div>

            <section class="vehicle-info">
                <h2>Informations sur le véhicule</h2>
                
                <form method="post" action="">
                    <div class="input-group">
                        <label for="modele">Modèle</label>
                        <input type="text" id="modele" name="modele" 
                               value="<?php echo htmlspecialchars($userInfo['vehicle_model'] ?? ''); ?>" 
                               placeholder="Ex: Clio 4">
                    </div>
                    <div class="input-group">
                        <label for="couleur">Couleur</label>
                        <input type="text" id="couleur" name="couleur" 
                               value="<?php echo htmlspecialchars($userInfo['vehicle_color'] ?? ''); ?>" 
                               placeholder="Ex: Rouge">
                    </div>
                    <button type="submit" name="btn_update_vehicle" class="save-btn">Enregistrer</button>
                </form>
            </section>

            <section class="comments-section">
                <h2>Avis et Commentaires (<?php echo count($userNotes); ?>)</h2>
                <?php if (count($userNotes) > 0): ?>
                    <div class="comments-list">
                        <?php foreach ($userNotes as $note): 
                            // UTILISATION DE LA NOUVELLE FONCTION
                            $authorInfos = GetUserInfoById($note['author_note']);
                            $authorName = $authorInfos ? $authorInfos['first_name'] . " " . $authorInfos['last_name'] : "Utilisateur inconnu";
                            
                            $authorId = $note['author_note'];
                            $profilLink = ($authorId == $_SESSION['user_id']) ? "profil.php" : "profilOther.php?user_id=" . $authorId;
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