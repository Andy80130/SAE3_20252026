<?php
session_start();

// 1. Sécurité connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

require '../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable('../');
$dotenv->load();

require("../includes/GestionBD.php");
$userId = $_SESSION['user_id'];

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception as PHPMailerException;

// 2. Traitement des formulaires
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. Annulations (Trajet ou Réservation)
    if (isset($_POST['action']) && isset($_POST['journey_id'])) {
        $jId = intval($_POST['journey_id']);

        if ($_POST['action'] === 'delete_trip') {
            // L'organisateur supprime le trajet
            deleteJourney($jId);
        }
        elseif ($_POST['action'] === 'cancel_reservation') {
            // Le passager annule sa réservation
            cancelReservation($userId, $jId);
        }

        // On recharge la page pour voir les changements immédiatement
        header("Location: reservation.php");
        exit();
    }

    // B. Envoi d'un message (Contacter)
    if (isset($_POST['btn_send_Contact'])) {
        $mail = new PHPMailer(true);
        // On récupère l'email du destinataire via le champ caché du modal
        $destinataire = $_POST['Contact_user_id'];

        try {
            // Configuration SMTP
            $mail->isSMTP();
            $mail->Host = $_ENV['SMTP_HOST'];
            $mail->SMTPAuth = true;
            $mail->Username = $_ENV['SMTP_USERNAME'];
            $mail->Password = $_ENV['SMTP_PASSWORD'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = $_ENV['SMTP_PORT'];
            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                ],
            ];

            // Infos de l'expéditeur (Session)
            $nomExp = isset($_SESSION['nom']) ? $_SESSION['nom'] : 'Utilisateur';
            $prenomExp = isset($_SESSION['prenom']) ? $_SESSION['prenom'] : '';
            $mailExp = isset($_SESSION['mail']) ? $_SESSION['mail'] : '';

            // Destinataire et contenu
            $mail->setFrom($_ENV['SMTP_USERNAME'], 'StudyGo');
            $mail->addAddress($destinataire);
            $mail->Subject = 'Vous avez un message de '. $nomExp . ' ' . $prenomExp .' !';
            $mail->isHTML(true);

            // Nettoyage du message utilisateur
            $messageContent = htmlspecialchars(trim($_POST['message']), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $mailExp = htmlspecialchars($mailExp, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

            // Corps du mail en HTML
            $mail->isHTML(true);
            $mail->CharSet = 'UTF-8';
            // Note : Assurez-vous que le chemin de l'image est correct par rapport à l'exécution du script
            $mail->addEmbeddedImage('../images/Logo_StudyGo.png', 'logo');
            
            $mail->Body = "<p>" . $messageContent . "</p><br><br>" .
                "<p>Pour le recontacter, vous pouvez lui envoyer un mail à : " . $mailExp . "<br><br>" .
                "Cordialement, l'équipe de StudyGo.</p>
                 <img src='cid:logo' alt='Logo' style='max-width:300px;'/>";
            
            $mail->send();
            header("Location: reservation.php?msg=mailSucces");
            exit();
        } catch (PHPMailerException $e) {
            header("Location: reservation.php?msg=mailFailed");
            exit();
        }
    }
}

// 3. Récupération des données pour l'affichage
$currentUser = GetUserInfo($_SESSION['mail']);
$myFullName = $currentUser ? $currentUser['first_name'] . ' ' . $currentUser['last_name'] : "Moi";

// Trajets organisés
$trajets_organises = GetOrganizedJourneys($userId);
foreach ($trajets_organises as &$trajet) {
    $trajet['liste_participants'] = GetJourneyParticipants($trajet['journey_id']);
    $trajet['nom_affichage'] = $myFullName;
}
unset($trajet);

// Trajets réservés
$trajets_reserves = GetReservedJourneysDetails($userId);
foreach ($trajets_reserves as &$res) {
    $res['nom_affichage'] = $res['first_name'] . ' ' . $res['last_name'];
    $res['liste_participants'] = [];
}
unset($res);

// Fonction date en français
function dateToFrench($dateSQL) {
    $timestamp = strtotime($dateSQL);
    $jours = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
    $mois = ['', 'Jan', 'Fév', 'Mar', 'Avr', 'Mai', 'Juin', 'Juil', 'Août', 'Sep', 'Oct', 'Nov', 'Déc'];

    $jourSemaine = $jours[date('w', $timestamp)];
    $jourMois = date('d', $timestamp);
    $moisStr = $mois[date('n', $timestamp)];
    $heure = date('H:i', $timestamp);

    return "$heure, $jourSemaine $jourMois $moisStr";
}

// Fonction d'affichage d'une carte
function afficherCarte($data, $isOrganizer = false) {
    $dateStr = dateToFrench($data['start_date']);
    $avatar = '../images/Profil_Picture.png';

    $nom = htmlspecialchars($data['nom_affichage']);
    $mail = htmlspecialchars($data['mail'] ?? '');
    
    $depart = htmlspecialchars($data['start_adress']);
    $arrivee = htmlspecialchars($data['arrival_adress']);
    $nbInscrits = count($data['liste_participants']);
    $nbPlaces = $data['number_place'];
    $journeyId = $data['journey_id'];

    // --- Gestion du lien vers le profil du conducteur ---
    $DriverID = $data['driver_id'] ?? 0;
    $currentUserID = $_SESSION['user_id'] ?? 0;

    if ($DriverID == $currentUserID) {
        $profilURL = "profil.php";
    } else {
        $profilURL = "profilOther.php?user_id=" . urlencode($DriverID);
    }
    
    echo '<article class="card">';
    echo '  <div class="card-header">';
    echo '    <div class="organizer">';

    echo '      <img src="'.$avatar.'" class="avatar" alt="Photo de profil" />';
    
    echo '      <div class="organizer-info">';
    
    // Le lien entoure uniquement le nom du conducteur
    echo '        <h3><a href="'.$profilURL.'" title="Voir le profil">'.$nom.'</a></h3>';

    if ($isOrganizer) {
        echo '<span class="participants-count">Inscrits : '.$nbInscrits.' / '.$nbPlaces.'</span>';
    }

    echo '      </div>';
    echo '    </div>';
    echo '  </div>';

    echo '  <div class="trip-details">';
    echo '    <p><strong>Départ :</strong> '.$depart.'</p>';
    echo '    <p><strong>Arrivée :</strong> '.$arrivee.'</p>';
    echo '    <p><strong>Date :</strong> '.$dateStr.'</p>';
    echo '  </div>';

    echo '  <div class="card-actions">';

    if ($isOrganizer) {
        // --- ORGANISATEUR ---
        echo '<form method="POST" style="flex:1;" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer ce trajet ? Cela annulera toutes les réservations associées.\');">';
        echo '  <input type="hidden" name="journey_id" value="'.$journeyId.'">';
        echo '  <input type="hidden" name="action" value="delete_trip">';
        echo '  <button type="submit" class="btn btn-outline" style="width:100%;">Annuler le trajet</button>';
        echo '</form>';

        $styleBtn = ($nbInscrits > 0) ? 'btn-filled' : 'btn-outline';
        echo '    <button class="btn '.$styleBtn.' toggle-btn" style="flex:1;">Voir Participants</button>';

    } else {
        // --- PASSAGER ---
        echo '<form method="POST" style="flex:1;" onsubmit="return confirm(\'Êtes-vous sûr de vouloir annuler votre réservation ?\');">';
        echo '  <input type="hidden" name="journey_id" value="'.$journeyId.'">';
        echo '  <input type="hidden" name="action" value="cancel_reservation">';
        echo '  <button type="submit" class="btn btn-outline" style="width:100%;">Annuler réservation</button>';
        echo '</form>';

        echo '    <button type="button" class="btn btn-filled" style="flex:1;" onclick="openContactModal(\''.$mail.'\')">Contacter</button>';
    }

    echo '  </div>';

    // --- LISTE DES PARTICIPANTS ---
    if ($isOrganizer && $nbInscrits > 0) {
        echo '<div class="participants-list" style="display:none;">';
        foreach($data['liste_participants'] as $p) {
            
            // Logique pour le lien du participant
            $pId = $p['user_id'];
            $currentId = $_SESSION['user_id'] ?? 0;
            
            // Lien vers le bon profil
            if ($pId == $currentId) {
                $linkProfil = "profil.php";
            } else {
                $linkProfil = "profilOther.php?user_id=" . $pId;
            }

            echo '<div class="participant">';
            echo '  <img src="../images/Profil_Picture.png" class="avatar-small" alt="Avatar"/>';
            
            // MODIFICATION ICI : On laisse le CSS gérer le style
            echo '  <a href="'.$linkProfil.'" title="Voir le profil">';
            echo      htmlspecialchars($p['first_name'].' '.$p['last_name']);
            echo '  </a>';
            
            echo '</div>';
        }
        echo '</div>';
    }
    echo '</article>';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyGo - Mes Trajets</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="../css/styleReservation.css" />
    <style>
        .section-contenu { display: none; }
        .section-contenu.active { display: block; }
        .empty-message {
            text-align: center;
            color: #666;
            margin-top: 20px;
            font-style: italic;
        }
        .card-actions form {
            margin: 0;
            padding: 0;
            display: flex;
        }
    </style>
</head>
<body>
<?php require("../includes/header.php") ?>

<?php if (isset($_GET['msg']) && $_GET['msg'] === 'mailSucces'): ?>
    <div class="msg-success" style="background:green; color:white; padding:10px; text-align:center; margin-bottom:15px;">
        <p>Message envoyé !</p>
    </div>
<?php elseif (isset($_GET['msg']) && $_GET['msg'] === 'mailFailed'): ?>
    <div class="msg-success" style="background:red; color:white; padding:10px; text-align:center; margin-bottom:15px;">
        <p>Echec de l'envoi du message.</p>
    </div>
<?php endif; ?>

<main>
    <h1 class="page-title">Mes trajets et réservations</h1>

    <div class="tabs-primary">
        <button class="tab active" onclick="changerOnglet('organises', this)">Mes trajets organisés</button>
        <button class="tab" onclick="changerOnglet('reserves', this)">Mes trajets réservés</button>
    </div>

    <div id="bloc-organises" class="section-contenu active">
        <h2 class="section-title">Vous organisez <?php echo count($trajets_organises); ?> trajet(s)</h2>

        <?php
        if (count($trajets_organises) > 0) {
            foreach($trajets_organises as $trajet) {
                afficherCarte($trajet, true);
            }
        } else {
            echo '<p class="empty-message">Aucun trajet organisé pour le moment.</p>';
        }
        ?>

        <div class="illustration-container">
            <img src="https://cdni.iconscout.com/illustration/premium/thumb/carpooling-service-app-illustration-download-in-svg-png-gif-file-formats--online-booking-sharing-share-ride-taxi-pack-vehicle-illustrations-4609653.png?f=webp" alt="Illustration Trajet" />
        </div>
    </div>

    <div id="bloc-reserves" class="section-contenu">
        <h2 class="section-title">Vous avez réservé <?php echo count($trajets_reserves); ?> trajet(s)</h2>

        <?php
        if (count($trajets_reserves) > 0) {
            foreach($trajets_reserves as $trajet) {
                afficherCarte($trajet, false);
            }
        } else {
            echo '<p class="empty-message">Aucune réservation en cours.</p>';
        }
        ?>

        <div class="illustration-container">
            <img src="../images/RechercheTrajetFin.png" alt="Illustration Réservation" style="max-width:200px"/>
        </div>
    </div>

    <div id="ContactModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeContactModal()">&times;</span>
            <h2>Envoyer un mail</h2>

            <form action="" method="post">
                <input type="hidden" id="modal_Contact_user_id" name="Contact_user_id" value="">

                <textarea name="message" rows="10" placeholder="Bonjour..." required></textarea>

                <button type="submit" name="btn_send_Contact" class="modal-submit-btn">Envoyer le mail</button>
            </form>
        </div>
    </div>

</main>

<?php require("../includes/footer.php") ?>

<script>
    // Gestion des onglets
    function changerOnglet(choix, boutonClique) {
        document.querySelectorAll('.tabs-primary .tab').forEach(btn => btn.classList.remove('active'));
        boutonClique.classList.add('active');
        document.querySelectorAll('.section-contenu').forEach(div => div.classList.remove('active'));
        document.getElementById('bloc-' + choix).classList.add('active');
    }

    // Gestion du bouton "Voir Participants"
    document.addEventListener('DOMContentLoaded', function() {
        document.body.addEventListener('click', function(e) {
            if (e.target && e.target.classList.contains('toggle-btn')) {
                e.preventDefault();
                const btn = e.target;
                const card = btn.closest('.card');
                const liste = card.querySelector('.participants-list');

                if(liste) {
                    if (getComputedStyle(liste).display === 'none') {
                        liste.style.display = 'flex';
                        btn.textContent = 'Masquer';
                    } else {
                        liste.style.display = 'none';
                        btn.textContent = 'Voir Participants';
                    }
                }
            }
        });
    });

    // Fermeture Modal au clic en dehors
    window.onclick = function(event) {
        if (event.target == document.getElementById("ContactModal")) {
            closeContactModal();
        }
    }

    function closeContactModal() {
        document.getElementById("ContactModal").style.display = "none";
    }

    function openContactModal(mail) {
        document.getElementById("ContactModal").style.display = "block";
        document.getElementById("modal_Contact_user_id").value = mail;
    }
</script>
</body>
</html>