<?php
session_start();

// RÉCUPÉRATION DES PARAM. 

$depart = $_GET['depart'] ?? ($_POST['depart'] ?? 'iut');
$destination = $_GET['destination'] ?? ($_POST['destination'] ?? 'gare');
$date = $_GET['date'] ?? '';
$start = $_GET['start'] ?? '';
$end = $_GET['end'] ?? '';
$places = $_GET['places'] ?? '1';

$errors = isset($_GET['errors']) ? explode('|', $_GET['errors']) : [];
$success = isset($_GET['success']);

// Petit jeu de valeur en vif
$locations = [
    'iut' => [
        'name' => "IUT d'Amiens",
        'lat' => 49.8942,
        'lon' => 2.2957
    ],
    'gare' => [
        'name' => "Gare d'Amiens",
        'lat' => 49.8892,
        'lon' => 2.3057
    ],
    'behencourt' => [
        'name' => "Mairie Béhencourt",
        'lat' => 49.8992,
        'lon' => 2.2857
    ],
    'pont-noyelles' => [
        'name' => "Pont de Noyelles",
        'lat' => 49.8850,
        'lon' => 2.2900
    ],
    'st-fuscien' => [
        'name' => "Mairie de Saint-Fuscien",
        'lat' => 49.9050,
        'lon' => 2.3100
    ]
];

// Récupération des coordonnées pour départ et destination
$departData = $locations[$depart] ?? $locations['iut'];
$destinationData = $locations[$destination] ?? $locations['gare'];
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>StudyGo - Covoiturage étudiant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <link rel="stylesheet" href="../css/styleCreerTrajet.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
</head>

<body>
    <!-- Header -->
    <?php require ('../includes/header.php'); ?>

    <h1 class="TitreCreer" style="text-align:center; margin:30px auto; width:100%;">
        Creer un trajet
    </h1>

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
                    <select name="depart" onchange="this.form.submit()">
                    <?php foreach ($locations as $key => $data): ?>
                    <option value="<?= htmlspecialchars($key) ?>" 
                    <?= $depart === $key ? 'selected' : '' ?>>
                    <?= htmlspecialchars($data['name']) ?>
                    </option>
                    <?php endforeach; ?>
                    </select>
                </div>

                <div class="swap">
                    <button type="submit" name="action" value="swap" title="Inverser"> ⇄ </button>
                </div>

                <div class="field">
                    <select name="destination" onchange="this.form.submit()">
                    <?php foreach ($locations as $key => $data): ?>
                    <option value="<?= htmlspecialchars($key) ?>" 
                    <?= $destination === $key ? 'selected' : '' ?>>
                    <?= htmlspecialchars($data['name']) ?>
                    </option>
                    <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </section>

    <div class="map-container">
        <div id="map"></div>
    </div>
     

    <script>
        // Initialisation de la carte
        var map = L.map('map').setView([49.8942, 2.2957], 13);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap contributors'
        }).addTo(map);

        // Icône orange pour le départ
        var orangeIcon = L.divIcon({
            className: 'custom-icon',
            html: '<div style="background: #ff6600; width: 30px; height: 30px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white;"></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });

        // Icône verte pour la destination
        var greenIcon = L.divIcon({
            className: 'custom-icon',
            html: '<div style="background: #00cc66; width: 30px; height: 30px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); border: 3px solid white;"></div>',
            iconSize: [30, 30],
            iconAnchor: [15, 30]
        });

        // Données PHP injectées en JS
        var departLat = <?= $departData['lat'] ?>;
        var departLon = <?= $departData['lon'] ?>;
        var departName = <?= json_encode($departData['name']) ?>;

        var destLat = <?= $destinationData['lat'] ?>;
        var destLon = <?= $destinationData['lon'] ?>;
        var destName = <?= json_encode($destinationData['name']) ?>;

        // Ajout des marqueurs
        L.marker([departLat, departLon], {icon: orangeIcon})
            .addTo(map)
            .bindPopup('<b>Départ:</b><br>' + departName);

        L.marker([destLat, destLon], {icon: greenIcon})
            .addTo(map)
            .bindPopup('<b>Destination:</b><br>' + destName);

        // Tracer une ligne entre les deux points
        L.polyline([
            [departLat, departLon],
            [destLat, destLon]
        ], {
            color: '#ff6600',
            weight: 3,
            opacity: 0.7,
            dashArray: '10, 10'
        }).addTo(map);

        // Centrer la carte sur les deux points
        var bounds = L.latLngBounds([
            [departLat, departLon],
            [destLat, destLon]
        ]);
        map.fitBounds(bounds, { padding: [50, 50] });
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


                
            </form>
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
                    <button class="secondary" type="reset">Réinitialiser</button>
                    <button class="primary" type="submit">Créer le trajet</button>
                </div>

    <div class="full-image">
        <img src="https://img.freepik.com/vecteurs-libre/illustration-concept-abstrait-stylo-numerique_335657-2281.jpg">
    </div>

    <?php require("../includes/footer.php")?>
</body>
</html>