<?php 
session_start(); 
require("../includes/GestionBD.php"); 

// --- 1. SÉCURITÉ & RÉCUPÉRATION DE L'ID UTILISATEUR VISÉ ---

$parametre_key = 'user_id';
$ViewUserId = 0;

if(isset($_GET[$parametre_key])){
    $ViewUserId = (int) $_GET[$parametre_key];
}

// Redirection si l'ID n'est pas valide
if($ViewUserId <= 0){
    header('Location: ../index.php');
    exit();
}

// Redirection si l'utilisateur essaie de voir son propre profil via cette page
if(isset($_SESSION['user_id']) && $ViewUserId == $_SESSION['user_id']){
    header('Location: profil.php');
    exit();
}

$viewUserInfo = GetUserInfoById($ViewUserId);

if(!$viewUserInfo){
    die("Utilisateur non trouvé.");
}

$currentUserId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;

// --- 2. TRAITEMENT DES FORMULAIRES (Signalement & Ajout Note) ---

// Traitement du Signalement
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_send_report'])) {
    if($currentUserId == 0) {
        header('Location: connexion.php');
        exit();
    }
    
    $reason = htmlspecialchars(trim($_POST['reason']));
    $reportedUserId = intval($_POST['reported_user_id']); 
    $reporterId = $currentUserId;

    if (!empty($reason) && $reportedUserId != $reporterId) {
        if (AddReport($reason, $reportedUserId, $reporterId)) {
            header("Location: profilOther.php?user_id=" . $ViewUserId . "&succes=report");
            exit();
        } else {
            header("Location: profilOther.php?user_id=" . $ViewUserId . "&error=report_failed");
            exit();
        }
    } else {
        header("Location: profilOther.php?user_id=" . $ViewUserId . "&error=invalid_report");
        exit();
    }
}

// Traitement de l'Ajout de Note/Commentaire
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['btn_add_note'])) {
    if($currentUserId == 0) {
        header('Location: connexion.php');
        exit();
    }

    $noteVal = floatval($_POST['note_value']);
    
    // CORRECTION ICI : On ne met pas htmlspecialchars avant l'insertion en BD
    // On garde juste trim() pour enlever les espaces inutiles au début/fin
    $description = trim($_POST['note_description']);

    // Vérification basique (Note entre 1 et 5)
    if ($noteVal >= 1 && $noteVal <= 5 && !empty($description)) {
        // On insère le texte brut en base de données
        if (AddNote($noteVal, $description, $currentUserId, $ViewUserId)) {
            header("Location: profilOther.php?user_id=" . $ViewUserId . "&succes=note_added");
            exit();
        } else {
            header("Location: profilOther.php?user_id=" . $ViewUserId . "&error=note_failed");
            exit();
        }
    } else {
        header("Location: profilOther.php?user_id=" . $ViewUserId . "&error=invalid_note");
        exit();
    }
}

// --- 3. RÉCUPÉRATION DES DONNÉES ---
$averageNote = AverageUserNote($ViewUserId);
$userNotes = UserNotes($ViewUserId);

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

        <?php if(isset($_GET['succes'])): ?>
            <div class="msg-success" style="background:#d4edda; color:#155724; padding:15px; text-align:center; margin: 20px auto; max-width:850px; border-radius:8px;">
                <?php 
                    if($_GET['succes'] == 'report') echo "Signalement envoyé aux administrateurs.";
                    if($_GET['succes'] == 'note_added') echo "Votre avis a été publié avec succès.";
                ?>
            </div>
        <?php endif; ?>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="msg-error" style="background:#f8d7da; color:#721c24; padding:15px; text-align:center; margin: 20px auto; max-width:850px; border-radius:8px;">
                <?php 
                    if($_GET['error'] == 'invalid_note') echo "La note doit être comprise entre 1 et 5.";
                    else echo "Une erreur est survenue.";
                ?>
            </div>
        <?php endif; ?>

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
                
                <?php if($currentUserId > 0): ?>
                    <div class="add-comment-box">
                        <h3>Laisser un avis sur ce conducteur</h3>
                        <form method="post" action="">
                            <div class="input-group">
                                <label for="note_value">Note (de 1 à 5) :</label>
                                <input type="number" name="note_value" id="note_value" 
                                       min="1" max="5" step="0.01" 
                                       placeholder="Ex : 4.5" required>
                            </div>
                            <div class="input-group">
                                <label for="note_description">Votre commentaire :</label>
                                <textarea name="note_description" id="note_description" rows="3" 
                                          placeholder="Racontez votre expérience de covoiturage..." required></textarea>
                            </div>
                            <button type="submit" name="btn_add_note" class="save-btn">Publier mon avis</button>
                        </form>
                    </div>
                <?php endif; ?>

                <h2>Avis et Commentaires (<?php echo count($userNotes); ?>)</h2>
                <?php if (count($userNotes) > 0): ?>
                    <div class="comments-list">
                        <?php foreach ($userNotes as $note): 
                            $authorInfos = GetUserInfoById($note['author_note']);
                            $authorName = $authorInfos ? $authorInfos['first_name'] . " " . $authorInfos['last_name'] : "Utilisateur inconnu";
                            
                            $authorId = $note['author_note'];
                            $profilLink = ($authorId == $currentUserId) ? "profil.php" : "profilOther.php?user_id=" . $authorId;
                        ?>
                            <div class="comment-card">
                                <div class="comment-header">
                                    <div class="comment-info">
                                        <span class="comment-note"><?php echo number_format($note['note'], 1); ?>/5</span>
                                        <a href="<?php echo $profilLink; ?>" class="comment-author" style="text-decoration:none; color:#ff6600; font-weight:bold; font-style:normal;">
                                            <?php echo htmlspecialchars($authorName); ?>
                                        </a>
                                    </div>
                                    
                                    <?php if($currentUserId > 0 && $authorId != $currentUserId): ?>
                                        <button class="comment-report-btn" onclick="openReportModal(<?php echo $note['author_note']; ?>)" title="Signaler ce commentaire">
                                            <i class="fa-solid fa-triangle-exclamation"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                                
                                <p class="comment-text">"<?php echo htmlspecialchars($note['note_description']); ?>"</p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="no-comments">Aucun avis pour le moment. Soyez le premier à en laisser un !</p>
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