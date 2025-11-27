<?php

session_start();

// RÉCUPÉRATION DES PARAM. 
$depart = $_POST['depart'] ?? '';
$destination = $_POST['destination'] ?? '';
$date = $_POST['date'] ?? '';
$start = $_POST['start'] ?? '';
$end = $_POST['end'] ?? '';
$places = $_POST['places'] ?? '1';

$errors = isset($_GET['errors']) ? explode('|', $_GET['errors']) : [];
$success = isset($_GET['success']);

// Récupération des coordonnées envoyées par le formulaire
$departData = [
    'name' => $depart,
    'lat'  => $_POST['depart_lat'] ?? null,
    'lon'  => $_POST['depart_lon'] ?? null
];

$destinationData = [
    'name' => $destination,
    'lat'  => $_POST['destination_lat'] ?? null,
    'lon'  => $_POST['destination_lon'] ?? null
];
?>





<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StudyGo - Covoiturage étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <link rel="stylesheet" href="../css/styleCreerTrajet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
</head>
<body>
    <?php require ('../includes/header.php'); ?>

    <h1 class="TitreCreer" style="text-align:center; margin:30px auto;">Créer un trajet</h1>

    <!-- Hero Section -->
    <section class="hero">
        <div class="title">Itinéraire</div>
        <div class="card">
            <form class="itinerary" method="post" action="CreerTrajet.php">
                <input type="hidden" name="date" value="<?= htmlspecialchars($date) ?>">
                <input type="hidden" name="places" value="<?= htmlspecialchars($places) ?>">
                <input type="hidden" name="start" value="<?= htmlspecialchars($start) ?>">
                <input type="hidden" name="end" value="<?= htmlspecialchars($end) ?>">

                <div class="field">
                    <div class="labelOrange">Départ</div>
                    <input type="text" id="depart" name="depart" placeholder="Saisir un arrêt ou une adresse" class="form-input" value="<?= htmlspecialchars($depart) ?>">
                    <div id="suggestions-depart" class="suggestions"></div>
                    <input type="hidden" id="depart_lat" name="depart_lat" value="<?= htmlspecialchars($departData['lat']) ?>">
                    <input type="hidden" id="depart_lon" name="depart_lon" value="<?= htmlspecialchars($departData['lon']) ?>">
                </div>

                <div class="swap">
                    <button type="submit" name="action" value="swap" title="Inverser"> ⇄ </button>
                </div>

                <div class="field">
                    <div class="labelOrange">Destination</div>
                    <input type="text" id="destination" name="destination" placeholder="Saisir un arrêt ou une adresse" class="form-input" value="<?= htmlspecialchars($destination) ?>">
                    <div id="suggestions-destination" class="suggestions"></div>
                    <input type="hidden" id="destination_lat" name="destination_lat" value="<?= htmlspecialchars($destinationData['lat']) ?>">
                    <input type="hidden" id="destination_lon" name="destination_lon" value="<?= htmlspecialchars($destinationData['lon']) ?>">
                </div>
            </form>
        </div>
    </section>




    <!-- Autocomplétion -->
    <script src="autocomplete.js"></script>












    <!-- Carte -->
    <div class="map-container"><div id="map"></div></div>
    <script>
    // Initialisation de la carte
    const map = L.map('map').setView([49.8942, 2.2957], 13);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    // Fonction utilitaire : vérifie que lat/lon sont valides
    function isValidCoord(lat, lon) {
        return lat !== null && lon !== null && lat !== "" && lon !== ""
            && !isNaN(lat) && !isNaN(lon);
    }

    // Récupération depuis les variables PHP
    const depart = {
        name: <?= json_encode($departData['name']) ?>,
        lat: parseFloat(<?= json_encode($departData['lat']) ?>),
        lon: parseFloat(<?= json_encode($departData['lon']) ?>)
    };

    const destination = {
        name: <?= json_encode($destinationData['name']) ?>,
        lat: parseFloat(<?= json_encode($destinationData['lat']) ?>),
        lon: parseFloat(<?= json_encode($destinationData['lon']) ?>)
    };

    const markers = [];

    // Affichage du marqueur de départ
    if (isValidCoord(depart.lat, depart.lon)) {
        markers.push(
            L.marker([depart.lat, depart.lon])
                .addTo(map)
                .bindPopup("Départ : " + depart.name)
        );
    }

    // Affichage du marqueur de destination
    if (isValidCoord(destination.lat, destination.lon)) {
        markers.push(
            L.marker([destination.lat, destination.lon])
                .addTo(map)
                .bindPopup("Destination : " + destination.name)
        );
    }

    // Tracé de la ligne + recentrage automatique
    if (isValidCoord(depart.lat, depart.lon) && isValidCoord(destination.lat, destination.lon)) {
        const latlngs = [
            [depart.lat, depart.lon],
            [destination.lat, destination.lon]
        ];

        L.polyline(latlngs, { color: "red", weight: 4 }).addTo(map);

        map.fitBounds(latlngs, { padding: [50, 50] });
    } else {
        // Sinon recentrage standard
        map.setView([49.8942, 2.2957], 13);
    }
</script>












    <section class="hero">
        <div class="card">
            <div class="title">Informations de trajet</div>

            <form class="trip-form" method="post" action="CreerTrajet.php">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="depart" value="<?= htmlspecialchars($depart) ?>">
                <input type="hidden" name="destination" value="<?= htmlspecialchars($destination) ?>">
                
                <div class="form-group">
                    <label for="date" class="form-label">
                        <span class="icon"></span> Date du trajet
                    </label>
                    <input type="date" id="date" name="date" class="form-input" value="<?= htmlspecialchars($date) ?>">
                </div>

                <div class="form-group time-range">
                    <label class="form-label">
                        <span class="icon"></span> Départ : de
                    </label>
                    <input type="time" name="start" class="form-input time-input" value="<?= htmlspecialchars($start) ?>">
                    <span class="time-separator">à</span>
                    <input type="time" name="end" class="form-input time-input" value="<?= htmlspecialchars($end) ?>">
                </div>

                <div class="form-group">
                    <label for="places" class="form-label">
                        <span class="icon"></span> Nombre de places dispo.
                    </label>
                    <select id="places" name="places" class="form-input">
                        <option value="1" <?= $places === '1' ? 'selected' : '' ?>>1</option>
                        <option value="2" <?= $places === '2' ? 'selected' : '' ?>>2</option>
                        <option value="3" <?= $places === '3' ? 'selected' : '' ?>>3</option>
                        <option value="4" <?= $places === '4' ? 'selected' : '' ?>>4+</option>
                    </select>
                </div>

        </div>
    </section>

    <div class="infosCertif">
                    <div class="petittitle">Je certifie que les informations ci-dessus sont exactes</div>
                    <label class="custom-checkbox">
                        <input type="checkbox" name="certify">
                        <span class="checkmark"></span>
                    </label>
     </div>

                <?php if (!empty($errors)): ?>
                    <div class="errors">
                        <?php foreach ($errors as $error): ?>
                            <p style="color: red;"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success">
                        <p style="color: green;">Trajet créé avec succès !</p>
                    </div>
                <?php endif; ?>

                <div class="actions">
                   <button class="secondary" type="submit" name="action" value="reset">
                        Réinitialiser
                    </button>
                    <button class="primary" type="submit">Créer le trajet</button>
                </div>
               </form>

    <div class="full-image">
        <img src="https://img.freepik.com/vecteurs-libre/illustration-concept-abstrait-stylo-numerique_335657-2281.jpg">
    </div>

    <?php require("../includes/footer.php")?>
</body>
</html>