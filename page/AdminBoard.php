<?php
session_start();
require("../includes/GestionBD.php");

// SÉCURITÉ : Vérification Admin
if (!isset($_SESSION['user_id']) || !isset($_SESSION['admin_flag']) || $_SESSION['admin_flag'] !== 'Y') {
    header('Location: accueil.php');
    exit();
}

$currentAdminId = $_SESSION['user_id']; // ID de l'admin connecté
$msgSuccess = "";
$msgError = "";

// TRAITEMENT DES FORMULAIRES
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Récupération des IDs communs
    $reportId = isset($_POST['reporting_id']) ? intval($_POST['reporting_id']) : 0;
    $reportedId = isset($_POST['reported_id']) ? intval($_POST['reported_id']) : 0;

    // 2. SÉCURITÉ CRITIQUE : Un admin ne peut pas agir sur lui-même
    if ($reportedId === $currentAdminId) {
        $msgError = "SÉCURITÉ : Vous ne pouvez pas traiter un signalement vous concernant (Conflit d'intérêts).";
    } 
    else {
        // A. Actions Signalements
        if (isset($_POST['action_report'])) {
            $action = $_POST['action_report'];
            
            // Changer statut (En attente <-> Traité)
            if ($action === 'toggle_status') {
                $currentStatus = intval($_POST['current_status']);
                $newStatus = ($currentStatus === 0) ? 1 : 0;
                UpdateReportStatus($reportId, $newStatus);
            }
            
            // Supprimer UNIQUEMENT l'avis signalé
            if ($action === 'delete_notes') {
                if (isset($_POST['reporter_id'])) {
                    $reporterId = intval($_POST['reporter_id']);
                    
                    if (DeleteTargetedNote($reportedId, $reporterId)) {
                        $msgSuccess = "L'avis signalé a été supprimé.";
                        UpdateReportStatus($reportId, 1);
                    } else {
                        $msgError = "Erreur lors de la suppression de l'avis.";
                    }
                } else {
                    $msgError = "Erreur technique : ID du signalant manquant.";
                }
            }

            // --- BLACKLISTER ---
            if ($action === 'blacklist_user') {
                $mailToBan = $_POST['user_mail'];
                
                $reasonInput = isset($_POST['ban_reason']) ? trim($_POST['ban_reason']) : '';
                $reason = !empty($reasonInput) ? $reasonInput : "Suite à de multiples signalements et violation des CGU.";
                
                $date = date('Y-m-d');

                if (AddMailBL($mailToBan, $reason, $date)) {
                    if (deleteUser($reportedId)) {
                        $msgSuccess = "Utilisateur blacklisté et données supprimées avec succès.";
                    } else {
                        $msgError = "Utilisateur ajouté à la Blacklist, mais erreur lors de la suppression des données.";
                    }
                } else {
                    $msgError = "Erreur : Utilisateur déjà blacklisté ou problème technique.";
                }
            }
        }

        // --- DÉBANNIR ---
        if (isset($_POST['action_blacklist']) && $_POST['action_blacklist'] === 'unban') {
            $mailToUnban = $_POST['mail_unban'];
            
            if (RemoveFromBlacklist($mailToUnban)) {
                $msgSuccess = "Utilisateur $mailToUnban retiré de la liste noire.";
            } else {
                $msgError = "Erreur lors du débannissement.";
            }
        }
    }
}

// RÉCUPÉRATION DONNÉES
$rawReports = GetAllReportsWithDetails();
$groupedReports = [];

foreach ($rawReports as $report) {
    $uId = $report['user_reported'];
    if (!isset($groupedReports[$uId])) {
        $groupedReports[$uId] = [
            'user_info' => [
                'id' => $uId,
                'name' => $report['reported_firstname'] . ' ' . $report['reported_lastname'],
                'mail' => $report['reported_mail']
            ],
            'reports' => []
        ];
    }
    $groupedReports[$uId]['reports'][] = $report;
}

$blacklist = GetAllBlacklist();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StudyGo - Administration</title>
    <link rel="stylesheet" href="../css/styleAdminBoard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    
    <?php require("../includes/header.php") ?>

    <div class="admin-container">
        <h1 class="Titre">Panneau d'Administration</h1>

        <?php if($msgSuccess): ?>
            <div class="alert success"><?php echo $msgSuccess; ?></div>
        <?php endif; ?>
        <?php if($msgError): ?>
            <div class="alert error"><?php echo $msgError; ?></div>
        <?php endif; ?>

        <section class="admin-section">
            <h2><i class="fa-solid fa-triangle-exclamation"></i> Gestion des Signalements</h2>
            
            <?php if (empty($groupedReports)): ?>
                <p class="empty-state" style="text-align:center; padding:20px; color:#777;">Aucun signalement en cours.</p>
            <?php else: ?>
                <div class="reports-grid">
                    <?php foreach ($groupedReports as $group): ?>
                        <?php 
                            $uInfo = $group['user_info']; 
                            // Séparation des statuts
                            $pendingReports = array_filter($group['reports'], fn($r) => $r['status'] == 0);
                            $processedReports = array_filter($group['reports'], fn($r) => $r['status'] == 1);
                            
                            // Vérification : Est-ce l'admin connecté ?
                            $isSelf = ($uInfo['id'] === $currentAdminId);
                        ?>
                        
                        <div class="user-report-card">
                            <div class="user-report-header">
                                <div class="user-identity">
                                    <img src="../images/Profil_Picture.png" alt="User">
                                    <div>
                                        <h3>
                                            <?php echo htmlspecialchars($uInfo['name']); ?>
                                            <?php if($isSelf) echo " <small style='color:red'>(Vous)</small>"; ?>
                                        </h3>
                                        <span class="user-mail"><?php echo htmlspecialchars($uInfo['mail']); ?></span>
                                    </div>
                                </div>
                                <div class="user-actions-header">
                                    <?php if ($isSelf): ?>
                                        <span style="font-size:12px; color:#999; font-style:italic;">
                                            <i class="fa-solid fa-lock"></i> Gestion impossible
                                        </span>
                                    <?php else: ?>
                                        <form method="post" onsubmit="let reason = prompt('Veuillez saisir le motif du bannissement pour <?php echo addslashes($uInfo['name']); ?> :'); if(reason === null) return false; this.ban_reason.value = reason; return true;">
                                            <input type="hidden" name="action_report" value="blacklist_user">
                                            <input type="hidden" name="user_mail" value="<?php echo htmlspecialchars($uInfo['mail']); ?>">
                                            <input type="hidden" name="reported_id" value="<?php echo $uInfo['id']; ?>">
                                            <input type="hidden" name="reporting_id" value="0">
                                            <input type="hidden" name="ban_reason" value="">
                                            
                                            <button type="submit" class="btn-icon red" title="Blacklister et Supprimer">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <div class="reports-list">
                                <?php foreach ($pendingReports as $rep): ?>
                                    <div class="report-item">
                                        <div class="report-content">
                                            <strong>Raison :</strong> "<?php echo htmlspecialchars($rep['report_cause']); ?>"<br>
                                            <small>Par : <?php echo htmlspecialchars($rep['reporter_firstname'].' '.$rep['reporter_lastname']); ?></small>
                                        </div>
                                        
                                        <div class="report-actions">
                                            <?php if ($isSelf): ?>
                                                <span style="font-size:12px; color:#aaa;">Conflit d'intérêts</span>
                                            <?php else: ?>
                                                <form method="post">
                                                    <input type="hidden" name="action_report" value="toggle_status">
                                                    <input type="hidden" name="reporting_id" value="<?php echo $rep['reporting_id']; ?>">
                                                    <input type="hidden" name="reported_id" value="<?php echo $uInfo['id']; ?>">
                                                    <input type="hidden" name="current_status" value="0">
                                                    <button type="submit" class="btn-small orange">
                                                        <i class="fa-solid fa-clock"></i> En attente
                                                    </button>
                                                </form>
                                                
                                                <form method="post" onsubmit="return confirm('Supprimer uniquement cet avis ?');">
                                                    <input type="hidden" name="action_report" value="delete_notes">
                                                    <input type="hidden" name="reporting_id" value="<?php echo $rep['reporting_id']; ?>">
                                                    <input type="hidden" name="reported_id" value="<?php echo $uInfo['id']; ?>">
                                                    <input type="hidden" name="reporter_id" value="<?php echo $rep['reporter_id']; ?>">
                                                    
                                                    <button type="submit" class="btn-small grey" title="Supprimer l'avis signalé">
                                                        <i class="fa-solid fa-trash-can"></i> Avis
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>

                                <?php if (!empty($processedReports)): ?>
                                    <details class="history-details">
                                        <summary class="history-summary">
                                            <span><i class="fa-solid fa-history"></i> Historique traité (<?php echo count($processedReports); ?>)</span>
                                            <i class="fa-solid fa-chevron-down arrow-icon"></i>
                                        </summary>
                                        <div class="history-content">
                                            <?php foreach ($processedReports as $rep): ?>
                                                <div class="report-item processed">
                                                    <div class="report-content">
                                                        <strong>Raison :</strong> "<?php echo htmlspecialchars($rep['report_cause']); ?>"<br>
                                                        <small>Par : <?php echo htmlspecialchars($rep['reporter_firstname'].' '.$rep['reporter_lastname']); ?></small>
                                                    </div>
                                                    
                                                    <div class="report-actions">
                                                        <?php if (!$isSelf): ?>
                                                            <form method="post">
                                                                <input type="hidden" name="action_report" value="toggle_status">
                                                                <input type="hidden" name="reporting_id" value="<?php echo $rep['reporting_id']; ?>">
                                                                <input type="hidden" name="reported_id" value="<?php echo $uInfo['id']; ?>">
                                                                <input type="hidden" name="current_status" value="1">
                                                                <button type="submit" class="btn-small green">
                                                                    <i class="fa-solid fa-check"></i> Traité
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </details>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

        <section class="admin-section">
            <h2><i class="fa-solid fa-skull"></i> Liste Noire (Utilisateurs Bannis)</h2>
            <div class="table-container">
                <table class="blacklist-table">
                    <thead>
                        <tr>
                            <th>Email</th>
                            <th>Raison</th>
                            <th>Date du ban</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($blacklist) > 0): ?>
                            <?php foreach ($blacklist as $banned): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($banned['mail']); ?></td>
                                    <td><?php echo htmlspecialchars($banned['reason']); ?></td>
                                    <td><?php echo htmlspecialchars($banned['ban_date']); ?></td>
                                    <td>
                                        <form method="post" onsubmit="return confirm('Débannir cet utilisateur ?');">
                                            <input type="hidden" name="action_blacklist" value="unban">
                                            <input type="hidden" name="mail_unban" value="<?php echo htmlspecialchars($banned['mail']); ?>">
                                            <button type="submit" class="btn-unban">
                                                <i class="fa-solid fa-unlock"></i> Débannir
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" style="text-align:center; padding:15px;">La liste noire est vide.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
    <?php require("../includes/footer.php") ?>
</body>
</html>