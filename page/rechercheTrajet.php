<?php

session_start();

require "../includes/pdoSAE3.php";
require "../includes/GestionBD.php";



$depart      = $_POST['depart'] ?? '';
$destination = $_POST['destination'] ?? '';
$date        = $_POST['date'] ?? '';
$heure       = $_POST['heure'] ?? '';

$results = [];
$errors_submit = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (empty($depart) && empty($destination)) {
        $errors_submit[] = "Il faut au minimum mettre un départ ou une destination.";
    }

    if (empty($date))  $date  = date('Y-m-d');
    if (empty($heure)) $heure = date('H:i');

    if (empty($errors_submit)) {
        $datetime = $date . ' ' . $heure . ':00';
        $results = SearchJourneys($depart, $destination, $datetime);
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

<section>
    <h1 class="title">Réserver un trajet</h1>
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

<h1 class="title">Correspondances trouvées :</h1>

<?php if (!empty($results)): ?>
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
                    <span class="places-badge">
                        <?= htmlspecialchars($trajet['number_place']) ?> place(s) dispo
                    </span>

                    <button class="btn-reserver">Réserver</button>
                    <button class="btn-map" onclick="location.href='#map'">Voir la carte</button>
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
