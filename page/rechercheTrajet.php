<?php

session_start();

require "../includes/pdoSAE3.php";
require "../includes/GestionBD.php";


$reservation_message = "";

// Traitement de la réservation
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action']) && $_POST['action'] === 'reserver') {
    
    // On récupère l'ID du trajet et de l'utilisateur
    $id_trajet_a_reserver = intval($_POST['journey_id']);
    
    // ATTENTION : On suppose que l'ID utilisateur est stocké en session après connexion.
    $id_user = $_SESSION['user_id'] ?? null; 

    if ($id_user && $id_trajet_a_reserver) {
        try {
            // 1. Vérifier s'il reste de la place
            $places_restantes = RemainingSeats($id_trajet_a_reserver);
            
            if ($places_restantes > 0) {
                // 2. Ajouter la réservation
                if(AddReservation($id_user, $id_trajet_a_reserver)) {
                    
                    // --- REDIRECTION APRÈS SUCCÈS (PRG) ---
                    $params = http_build_query([
                        'depart' => $_POST['depart'],
                        'destination' => $_POST['destination'],
                        'date' => $_POST['date'],
                        'heure' => $_POST['heure'],
                        'msg' => 'success' // Indicateur de succès
                    ]);
                    
                    header("Location: rechercheTrajet.php?" . $params);
                    exit();
                    // ------------------------------------

                } else {
                    // Si AddReservation retourne false (ce qui ne devrait pas arriver 
                    // si votre fonction lève une exception, mais par sécurité)
                    $params = http_build_query([
                        'depart' => $_POST['depart'],
                        'destination' => $_POST['destination'],
                        'date' => $_POST['date'],
                        'heure' => $_POST['heure'],
                        'msg' => 'error' // Indicateur d'erreur
                    ]);
                    header("Location: rechercheTrajet.php?" . $params);
                    exit();
                }
            } else {
                $reservation_message = "<div style='color:red; text-align:center;'>Désolé, ce trajet est complet.</div>";
            }
        } catch (Exception $e) {
            // En cas d'exception (doublon, erreur SQL, etc.)
            $params = http_build_query([
                'depart' => $_POST['depart'],
                'destination' => $_POST['destination'],
                'date' => $_POST['date'],
                'heure' => $_POST['heure'],
                'msg' => 'error' // Indicateur d'erreur
            ]);
            header("Location: rechercheTrajet.php?" . $params);
            exit();
        }
    } else {
        $reservation_message = "<div style='color:red; text-align:center;'>Vous devez être connecté pour réserver.</div>";
    }
}


// --- MODIFICATION ICI : On accepte $_POST (formulaire) OU $_GET (lien accueil) ---
$depart      = $_POST['depart'] ?? $_GET['depart'] ?? '';
$destination = $_POST['destination'] ?? $_GET['destination'] ?? '';
$date        = $_POST['date'] ?? '';
$heure       = $_POST['heure'] ?? '';

$user_id = $_POST['user_id'] ?? '';
$journey_id = $_POST['journey_id'] ?? '';

$results = [];
$errors_submit = [];

// --- MODIFICATION ICI : Condition élargie pour accepter les GET ---
if ($_SERVER["REQUEST_METHOD"] === "POST" || (isset($_GET['depart']) || isset($_GET['destination']))) {

    if (empty($depart) && empty($destination)) {
        // On n'affiche pas d'erreur si on arrive juste sur la page sans recherche
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
             $errors_submit[] = "Il faut au minimum mettre un départ ou une destination.";
        }
    } else {
        if (empty($date))  $date  = date('Y-m-d');
        if (empty($heure)) {
            $dateTime = new DateTime();
            $dateTime->modify('+1 hour');
            $heure = $dateTime->format('H:i');
        }

    if (empty($errors_submit)) {
        $datetime = $date . ' ' . $heure . ':00';
        $current_user = $_SESSION['user_id'] ?? null;
        $results = SearchJourneys($depart, $destination, $datetime, $current_user);
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyGo - Recherche de Trajets</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <link rel="stylesheet" href="../css/styleAccueil.css">
    <link rel="stylesheet" href="../css/styleRechercheTrajet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
</head>
<body>

<?php require ('../includes/header.php'); ?>

<?php
// Vérification et affichage des messages de retour après redirection (PRG)
if (isset($_GET['msg'])) {
    $message = '';
    $style = '';
    
    if ($_GET['msg'] === 'success') {
        $message = "Votre trajet a bien été réservé !";
        $style = 'background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb;'; // Vert
    } elseif ($_GET['msg'] === 'error') {
        $message = "Une erreur s'est produite lors de la réservation. Veuillez réessayer ou vérifier votre statut.";
        $style = 'background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb;'; // Rouge
    }
    
    if ($message) {
        echo "<div style='" . $style . " padding: 10px; margin: 15px auto; border-radius: 5px; text-align: center; max-width: 600px;'>";
        echo "<strong>" . htmlspecialchars($message) . "</strong>";
        echo "</div>";
    }
}
?>
<section>
    <div id="intro">
        <p>
            Rejoignez un trajet en un instant grâce à notre recherche
            intuitive. Trouvez un conducteur qui se rend dans la même
            direction, consultez les avis et réservez votre place.
            Voyager avec la communauté devient rapide, économique et
            pratique.
        </p>
        <img src="../images/rechercheTrajetIntro.png" width="300px" class="image">
    </div>
</section>

<section>
    <h1 class="title">Renseignez votre recherche</h1>

    <form class="search" method="post" action="rechercheTrajet.php">

        <div class="form-group">
            <label for="depart">Départ</label>
            <input id="depart" name="depart" type="text" placeholder="ex : Amiens, Gare routière" value="<?= htmlspecialchars($depart) ?>">
        </div>

        <div class="form-group">
            <label for="arrivee">Destination</label>
            <input id="arrivee" name="destination" type="text" placeholder="ex : Amiens, IUT" value="<?= htmlspecialchars($destination) ?>">
        </div>

        <div class="form-group-inline">
            <div>
                <label for="dateStart">Date</label>
                <input id="dateStart" name="date" type="date" value="<?= htmlspecialchars($date) ?>">
            </div>

            <div>
                <label for="timeStart">Heure de départ</label>
                <input id="timeStart" name="heure" type="time" value="<?= htmlspecialchars($heure) ?>">
            </div>
        </div>

        <?php if (!empty($errors_submit)): ?>
            <div class="errors">
                <?php foreach ($errors_submit as $error): ?>
                    <p><?= htmlspecialchars($error) ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div class="search-btn-position">
            <button class="search-btn" type="submit">Rechercher</button>
        </div>
    </form>
</section>

<?php if (!empty($results)): ?>
    <h1 class="title">Correspondances trouvées :</h1>
    <div class="results-container">
        <?php foreach ($results as $trajet): ?>
            
            <?php 
                $dateObj = new DateTime($trajet['start_date']);
                $heureDepart = $dateObj->format('H:i');
                $dateDepart = $dateObj->format('d/m/Y');
            ?>

            <div class="trajet-card">
                
                <div class="trajet-info">
                    <div class="trajet-time">
                        <?= $heureDepart ?>
                        <span style="font-size:0.6em; color:#888; font-weight:normal;">le <?= $dateDepart ?></span>
                    </div>
                    <div class="trajet-route">
                        <div class="route-step">
                            Départ : <strong><?= htmlspecialchars($trajet['depart']) ?></strong>
                        </div>
                        <div class="route-step">
                            Arrivée : <strong><?= htmlspecialchars($trajet['destination']) ?></strong>
                        </div>
                    </div>
                </div>

                <div class="trajet-driver">
                    <div class="driver-info">
                        <img src="../images/Profil_Picture.png" class="driver-avatar" alt="Avatar">
                        
                        <span class="driver-name"><?= htmlspecialchars($trajet['driver_name']) ?></span>
                    </div>
                    <div class="car-info">
                        <img src="../images/Voiture_orange.png" class="icon-voiture-small" alt="Voiture">
                        <span><?= htmlspecialchars($trajet['vehicle_model']) ?></span>
                    </div>
                    <small style="color:#999;"><?= htmlspecialchars($trajet['vehicle_color']) ?></small>
                </div>

<div class="trajet-action">
    
    <?php
        $id_trajet = $trajet['journey_id'];
        $places_disponibles = RemainingSeats($id_trajet);
    ?>

    <span class="places-badge">
        <?= htmlspecialchars($places_disponibles) ?> place(s) dispo
    </span>

    <form method="post" action="rechercheTrajet.php">
        <input type="hidden" name="journey_id" value="<?= $id_trajet ?>">
        <input type="hidden" name="action" value="reserver">
        
        <?php if ($places_disponibles > 0): ?>
            <button class="btn-reserver" type="submit">Réserver</button>
        <?php else: ?>
            <button class="btn-reserver complet" type="button" disabled>Complet</button>
        <?php endif; ?>
    </form>
</div>

            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <?php endif; ?>


<?php require ('../includes/footer.php'); ?>

<script>
    var map = L.map('map').setView([49.8942, 2.2957], 14);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    var orangeIcon = L.divIcon({
        className: 'custom-icon',
        html: '<div style="background: #ff6600; width: 30px; height: 30px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white;"></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });

    var blueIcon = L.divIcon({
        className: 'custom-icon',
        html: '<div style="background: blue; width: 30px; height: 30px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white;"></div>',
        iconSize: [30, 30],
        iconAnchor: [15, 30]
    });

    var arrayMarkers = [];

    function voirCarte(positionBgn, positionEnd){
        arrayMarkers.forEach(m => m.remove());

        var begin = L.marker(positionBgn, {icon: orangeIcon});
        var end = L.marker(positionEnd, {icon: blueIcon});

        begin.addTo(map).bindPopup("Départ");
        end.addTo(map).bindPopup("Arrivée");

        arrayMarkers = [begin, end];

        var bounds = L.latLngBounds([positionBgn, positionEnd]);
        map.fitBounds(bounds, {padding: [50, 50]});
    }
</script>

</body>
</html>