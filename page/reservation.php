<?php
session_start();

// 1. Sécurité connexion
if (!isset($_SESSION['user_id'])) {
    header('Location: connexion.php');
    exit();
}

require("../includes/GestionBD.php");
$userId = $_SESSION['user_id'];

// 2. Traitement des formulaires (Annulations)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
    $depart = htmlspecialchars($data['start_adress']);
    $arrivee = htmlspecialchars($data['arrival_adress']);
    $nbInscrits = count($data['liste_participants']);
    $nbPlaces = $data['number_place'];
    $journeyId = $data['journey_id'];
    
    echo '<article class="card">';
    echo '  <div class="card-header">';
    echo '    <div class="organizer">';
    echo '      <img src="'.$avatar.'" class="avatar" alt="Photo de profil" />';
    echo '      <div class="organizer-info">';
    echo '        <h3>'.$nom.'</h3>';
    
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
        // FORMULAIRE ANNULATION TRAJET (Organisateur)
        echo '<form method="POST" style="flex:1;" onsubmit="return confirm(\'Êtes-vous sûr de vouloir supprimer ce trajet ? Cela annulera toutes les réservations associées.\');">';
        echo '  <input type="hidden" name="journey_id" value="'.$journeyId.'">';
        echo '  <input type="hidden" name="action" value="delete_trip">';
        echo '  <button type="submit" class="btn btn-outline" style="width:100%;">Annuler le trajet</button>';
        echo '</form>';
        
        // BOUTON VOIR PARTICIPANTS
        $styleBtn = ($nbInscrits > 0) ? 'btn-filled' : 'btn-outline';
        // Note: Le bouton est en dehors du formulaire
        echo '    <button class="btn '.$styleBtn.' toggle-btn" style="flex:1;">Voir Participants</button>';
        
    } else {
        // FORMULAIRE ANNULATION RESERVATION (Passager)
        echo '<form method="POST" style="flex:1;" onsubmit="return confirm(\'Êtes-vous sûr de vouloir annuler votre réservation ?\');">';
        echo '  <input type="hidden" name="journey_id" value="'.$journeyId.'">';
        echo '  <input type="hidden" name="action" value="cancel_reservation">';
        echo '  <button type="submit" class="btn btn-outline" style="width:100%;">Annuler réservation</button>';
        echo '</form>';
        
        echo '    <button class="btn btn-filled" style="flex:1;">Contacter</button>';
    }

    echo '  </div>';
    
    // LISTE DES PARTICIPANTS (Cachée par défaut)
    if ($isOrganizer && $nbInscrits > 0) {
        echo '<div class="participants-list" style="display:none;">';
        foreach($data['liste_participants'] as $p) {
            echo '<div class="participant">';
            echo '  <img src="../images/Profil_Picture.png" class="avatar-small" alt="Avatar"/>';
            echo '  <span>'.htmlspecialchars($p['first_name'].' '.$p['last_name']).'</span>';
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
        /* Petit correctif pour aligner les formulaires et boutons */
        .card-actions form {
            margin: 0;
            padding: 0;
            display: flex;
        }
    </style>
</head>
<body>
    <?php require("../includes/header.php") ?>

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
                // On vérifie si l'élément cliqué a la classe toggle-btn
                if (e.target && e.target.classList.contains('toggle-btn')) {
                    e.preventDefault(); // Empêche un comportement par défaut si c'était un lien
                    
                    const btn = e.target;
                    const card = btn.closest('.card');
                    const liste = card.querySelector('.participants-list');
                    
                    if(liste) {
                        if (getComputedStyle(liste).display === 'none') {
                            liste.style.display = 'flex'; // Flex pour correspondre au CSS
                            btn.textContent = 'Masquer';
                        } else {
                            liste.style.display = 'none';
                            btn.textContent = 'Voir Participants';
                        }
                    }
                }
            });
        });
    </script>
</body>
</html>