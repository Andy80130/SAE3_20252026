<?php
session_start();


require "../includes/pdoSAE3.php";
require "../includes/GestionBD.php";




// Tronque adresse pour BD
function tronqueAdressePourBD(string $address): string {
    $chaineTravail = trim($address);
    

    //1er champs
    $pos1 = strpos($chaineTravail, ',');
    if ($pos1 === false) {
        // Pas de virgule trouvée, on retourne la chaîne telle quelle.
        return $chaineTravail;
    }
    
    //2e
    $pos2 = strpos($chaineTravail, ',', $pos1 + 1);
    if ($pos2 === false) {
        // Moins de 2 virgules,pareil
        return $chaineTravail;
    }

//3e
$pos3 = strpos($chaineTravail, ',', $pos2 + 1);
    
    // Si la 3ème virgule est trouvée:
    if ($pos3 !== false) {
        //je retourne les 3 premiers champs et pas ceux d'après
        return trim(substr($chaineTravail, 0, $pos3));
    }
    

    //Retour direct si moins de 3 virgules ( ça n'arrive jamais')
    return $chaineTravail;
}







function calculerDistanceKm($lat1, $lon1, $lat2, $lon2) {
    if (empty($lat1) || empty($lon1) || empty($lat2) || empty($lon2)) return null;

    $r_lat1 = deg2rad((float)$lat1);
    $r_lon1 = deg2rad((float)$lon1);
    $r_lat2 = deg2rad((float)$lat2);
    $r_lon2 = deg2rad((float)$lon2);

    $rayonTerre = 6371; // km
    $deltaLat = $r_lat2 - $r_lat1;
    $deltaLon = $r_lon2 - $r_lon1;

    $a = sin($deltaLat/2) * sin($deltaLat/2) +
         cos($r_lat1) * cos($r_lat2) *
         sin($deltaLon/2) * sin($deltaLon/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));

    return $rayonTerre * $c;
}






//ERREURS
$errors = isset($_GET['errors']) ? explode('|', $_GET['errors']) : [];
$success = isset($_GET['success']);




// Valeurs par défaut ou recup par post
$depart      = $_POST['depart'] ?? '';
$destination = $_POST['destination'] ?? '';
$date        = $_POST['date'] ?? '';
$start       = $_POST['start'] ?? '';
$end         = $_POST['end'] ?? '';
$places      = $_POST['places'] ?? '1';





// Coord.
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







// Calcul de la durée
$dureeMinutes = "--";
if ($departData['lat'] && $destinationData['lat']) {
    $distanceKm = calculerDistanceKm($departData['lat'], $departData['lon'], $destinationData['lat'], $destinationData['lon']);
    if ($distanceKm !== null) {
        // Estimation : 30km/h de moyenne * 1.5 pour marge d'erreur/traffic
        $dureeMinutes = round(($distanceKm / 30) * 60 * 1.5);
    }
}







//Mise en BD du trajet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'creation') {
    $errors_submit = [];



    // Valid
    if (empty($depart) || empty($destination)) $errors_submit[] = "Les adresses de départ et d'arrivée sont requises.";
    if (empty($date)) $errors_submit[] = "La date est requise.";
    if (empty($start)) $errors_submit[] = "L'heure de départ est requise.";
    if (!isset($_POST['certify'])) $errors_submit[] = "Veuillez certifier l'exactitude des informations.";


    // Vérif
    if (empty($departData['lat']) || empty($destinationData['lat'])) {
        $errors_submit[] = "Veuillez sélectionner des adresses valides proposées.";
    }




    // Récupération ID
    $driver_id = $_SESSION['user_id'] ?? 1; 

    if (empty($errors_submit)) {
        $start_datetime = $date . ' ' . $start . ':00';
        

        //Je transforme mes chaines dep et arrivée pour avoir
        //de beaux affichages en BD



        $valDepBD=tronqueAdressePourBD($depart);
        $valDestBD=tronqueAdressePourBD($destination);


        //AJOUT
        $result = AddJourney($valDepBD, $valDestBD, $start_datetime, (int)$places, (int)$driver_id);
        if ($result) {
            header('Location: CreerTrajet.php?success=1');
            exit;
        } else {
            $errors_submit[] = "Erreur technique lors de l'enregistrement en base de données.";
        }




    }
    //Gere les erreurs
    if (!empty($errors_submit)) {
        $errors = $errors_submit;
    }
}

?>








<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>StudyGo - Créer un trajet</title>
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <link rel="stylesheet" href="../css/styleCreerTrajet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
</head>
<body>
    
    <?php require ('../includes/header.php'); ?>

    <h1 class="TitreCreer" style="text-align:center; margin:30px auto;">Créer un trajet</h1>

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
                    <input type="text" id="depart" name="depart" placeholder="Saisir un arrêt ou une adresse" class="form-input" value="<?= htmlspecialchars($depart) ?>" autocomplete="off">
                    <div id="suggestions-depart" class="suggestions"></div>
                    <input type="hidden" id="depart_lat" name="depart_lat" value="<?= htmlspecialchars($departData['lat']) ?>">
                    <input type="hidden" id="depart_lon" name="depart_lon" value="<?= htmlspecialchars($departData['lon']) ?>">
                </div>

                <div class="field">
                    <div class="labelOrange">Destination</div>
                    <input type="text" id="destination" name="destination" placeholder="Saisir un arrêt ou une adresse" class="form-input" value="<?= htmlspecialchars($destination) ?>" autocomplete="off">
                    <div id="suggestions-destination" class="suggestions"></div>
                    <input type="hidden" id="destination_lat" name="destination_lat" value="<?= htmlspecialchars($destinationData['lat']) ?>">
                    <input type="hidden" id="destination_lon" name="destination_lon" value="<?= htmlspecialchars($destinationData['lon']) ?>">
                </div>

                <div class="affiche">
                    <button type="submit" name="action" value="map_refresh" title="Calculer l'itinéraire">Afficher le trajet</button>
                </div>
            </form>
        </div>
    </section>

    <div class="map-container">
        <div id="map"></div>
    </div>

    <?php if ($dureeMinutes !== "--"): ?>
    <section class="hero">
        <div class="card">
            <div class="title">Durée approximative</div>
            <div id="dureeTrajet" style="font-size: 1.2em; font-weight: bold; color: #333;">
                <?= $dureeMinutes ?> minutes
            </div>
        </div>
    </section>
    <?php endif; ?>

    <section class="hero">
        <div class="card">
            <div class="title">Informations de trajet</div>

            <form class="trip-form" method="post" action="CreerTrajet.php">
                <input type="hidden" name="action" value="creation">
                
                <input type="hidden" name="depart" value="<?= htmlspecialchars($depart) ?>">
                <input type="hidden" name="depart_lat" value="<?= htmlspecialchars($departData['lat']) ?>">
                <input type="hidden" name="depart_lon" value="<?= htmlspecialchars($departData['lon']) ?>">
                
                <input type="hidden" name="destination" value="<?= htmlspecialchars($destination) ?>">
                <input type="hidden" name="destination_lat" value="<?= htmlspecialchars($destinationData['lat']) ?>">
                <input type="hidden" name="destination_lon" value="<?= htmlspecialchars($destinationData['lon']) ?>">

                <div class="form-group">
                    <label for="date" class="form-label">Date du trajet</label>
                    <input type="date" id="date" name="date" class="form-input" value="<?= htmlspecialchars($date) ?>">
                </div>

                <div class="form-group time-range">
                    <label class="form-label">Départ : de</label>
                    <input type="time" name="start" class="form-input time-input" value="<?= htmlspecialchars($start) ?>">
                    <span class="time-separator">à</span>
                    <input type="time" name="end" class="form-input time-input" value="<?= htmlspecialchars($end) ?>">
                </div>

                <div class="form-group">
                    <label for="places" class="form-label">Places disponibles</label>
                    <select id="places" name="places" class="form-input">
                        <option value="1" <?= $places === '1' ? 'selected' : '' ?>>1</option>
                        <option value="2" <?= $places === '2' ? 'selected' : '' ?>>2</option>
                        <option value="3" <?= $places === '3' ? 'selected' : '' ?>>3</option>
                        <option value="4" <?= $places === '4' ? 'selected' : '' ?>>4+</option>
                    </select>
                </div>

                <div class="infosCertif">
                    <div class="petittitle">Je certifie que les informations ci-dessus sont exactes</div>
                    <label class="custom-checkbox">
                        <input type="checkbox" name="certify">
                        <span class="checkmark"></span>
                    </label>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="errors" style="background: #ffe6e6; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                        <?php foreach ($errors as $error): ?>
                            <p style="color: #d93025; margin: 0;">• <?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success" style="background: #e6fffa; padding: 10px; border-radius: 5px; margin-bottom: 15px;">
                        <p style="color: #00796b; margin: 0;">Trajet créé avec succès !</p>
                    </div>
                <?php endif; ?>

                <div class="actions">
                    <button type="button" class="secondary" onclick="reinitialiserDonnees()">Réinitialiser</button>
                    <button class="primary" type="submit">Créer le trajet</button>
                </div>
            </form>
        </div>
    </section>

    <div class="full-image">
        <img src="https://img.freepik.com/vecteurs-libre/illustration-concept-abstrait-stylo-numerique_335657-2281.jpg" alt="Illustration Trajet">
    </div>

    <?php require("../includes/footer.php")?>

    <script src="../js/autocomplete.js"></script>
    <script src="../js/reinitialiser.js"></script>
    <script src="../js/CreerTrajet.js"></script>

    <script>


        // Map leaflet ( openmaps)
        document.addEventListener('DOMContentLoaded', function() {
            // Initialisation centrée par défaut (Amiens ou autre)
            const map = L.map('map').setView([49.8942, 2.2957], 13);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap contributors'
            }).addTo(map);

            // Données PHP injectées dans JS
            const depart = {
                name: <?= json_encode($departData['name']) ?>,
                lat: <?= json_encode($departData['lat']) ?>,
                lon: <?= json_encode($departData['lon']) ?>
            };

            const destination = {
                name: <?= json_encode($destinationData['name']) ?>,
                lat: <?= json_encode($destinationData['lat']) ?>,
                lon: <?= json_encode($destinationData['lon']) ?>
            };

            function hasCoords(pt) {
                return pt.lat && pt.lon && !isNaN(pt.lat) && !isNaN(pt.lon);
            }

            const markers = [];
            const latlngs = [];

            // Marker Départ
            if (hasCoords(depart)) {
                const mk1 = L.marker([depart.lat, depart.lon]).addTo(map).bindPopup("Départ : " + depart.name);
                markers.push(mk1);
                latlngs.push([depart.lat, depart.lon]);
            }

            // Marker Destination
            if (hasCoords(destination)) {
                const mk2 = L.marker([destination.lat, destination.lon]).addTo(map).bindPopup("Destination : " + destination.name);
                markers.push(mk2);
                latlngs.push([destination.lat, destination.lon]);
            }

            // Tracé
            if (latlngs.length === 2) {
            // Utilisation de Leaflet Routing Machine pour un tracé routier réaliste
            L.Routing.control({
            waypoints: [
                L.latLng(depart.lat, depart.lon),
                L.latLng(destination.lat, destination.lon)
            ],
        // Style tracé
        lineOptions: {
            styles: [{ color: 'red', opacity: 0.8, weight: 3 }]
        },
        show: false, 



        // Empêcher l'ajout ou le déplacement
        addWaypoints: false, 
        draggableWaypoints: false,
        // Centrer
        fitSelectedRoutes: 'smart' 
    }).addTo(map);

    // Note : Le L.Routing.control gère déjà le centrage (fitBounds).
    
} else if (latlngs.length === 1) {
    // Si un seul point est déf
    map.setView(latlngs[0], 14);
}
        });
    </script>
</body>
</html>