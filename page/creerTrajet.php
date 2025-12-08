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










//Mise en BD du trajet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'creation') {
    $errors_submit = [];



    // Valid
    if (empty($depart) || empty($destination)) $errors_submit[] = "Les adresses de départ et d'arrivée sont requises.";
    if (empty($date)) $errors_submit[] = "La date est requise.";
    if (empty($start)) $errors_submit[] = "L'heure d'attente et de départ est requise.";
    if (!isset($_POST['certify'])) $errors_submit[] = "Veuillez certifier l'exactitude des informations.";



    //verif la date d'heure valides ( par rappport à mtnt): 
    $date_actuelle = new DateTime();
    $trajet_date = $date . ' ' . $start;
    $start_trajet = new DateTime($date . ' ' . $start);
    $end_trajet = new DateTime($date . ' ' . $end);

    if (new DateTime($trajet_date) <= $date_actuelle) {
        $errors_submit[] = "La date et l'heure du trajet doivent supérieur à la date actuelle.";
    }

    if($start_trajet >= $end_trajet){
        $errors_submit[] = "L'heure de fin doit être supérieure à l'heure de début.";
    }







    //pas + de 15min d'attente
    if($start_trajet < $end_trajet){
        $interval = $start_trajet->diff($end_trajet);
        $minutes_diff = ($interval->h * 60) + $interval->i;

        if($minutes_diff > 15){
            $errors_submit[] = "Le temps d'attente ne peut pas dépasser 15 minutes.";
        }
    }

    //pas au moins 5min d'attente
    if($start_trajet < $end_trajet){
        $interval = $start_trajet->diff($end_trajet);
        $minutes_diff = ($interval->h * 60) + $interval->i;
        if($minutes_diff < 5){
            $errors_submit[] = "Le temps d'attente doit être au moins de 5 minutes.";
        }
    }







    // Vérif des adresses
    $IUT_Address = "IUT Amiens, Avenue des Facultés, Salouël, Amiens, Somme, Hauts-de-France, France métropolitaine, 80480, France";

    if(trim($depart) === trim($destination)){
        $errors_submit[] = "Le départ et la destination ne peuvent pas être identiques.";
    }

    if(trim($depart) !== $IUT_Address && trim($destination) !== $IUT_Address){
        $errors_submit[] = "Le départ ou la destination doit etre l'iut d'amiens";
    }




    // Récupération ID
    $driver_id = $_SESSION['user_id'] ?? 1;
    $driver_mail = $_SESSION['mail'] ?? '';

    if(!empty($driver_mail))
    {
        $userInfo = GetUserInfo($driver_mail);
        if($userInfo == null || empty($userInfo['vehicle_model'])){
                        $errors_submit[] = "Vous devez posséder un véhicule pour créer un trajet. Veuillez en ajouter un dans votre profil.";
        }
    } 
    

    //Verif de si il possède bien un véhicule


    if (empty($error_submit)) {
        $start_datetime = $date . ' ' . $end . ':00';

        $valDepBD  = tronqueAdressePourBD($depart);
        $valDestBD = tronqueAdressePourBD($destination);

        $result = AddJourney($valDepBD, $valDestBD, $start_datetime, (int)$places, (int)$driver_id);
        if ($result) {
            header('Location: CreerTrajet.php?success=1');
            exit;
        } else {
            $errors_submit[] = "Veuillez vous connectez à votre compte pour créer un trajet";
        }
    }






    // Gère les erreurs
    if (!empty($errors_submit)) {
        $errors = $errors_submit;
    }
}


// Calcul de la durée
$dureeMinutes = "--";
if ($departData['lat'] && $destinationData['lat']) {
        $dureeMinutes = 1;
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

    <?php//STOP MODIF?>

    <section class="hero" id="sectionDuree" style="display:none;">
    <div class="card">
        <div class="title">Durée approximative</div>
        <div id="dureeTrajet" style="font-size: 1.2em; font-weight: bold; color: #333;">
            ...
        </div>
    </div>
</section>

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
document.addEventListener('DOMContentLoaded', function() {

    // Initialisation de la carte
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

    // Validation coordonnées
    function hasCoords(pt) {
        return pt.lat && pt.lon && !isNaN(pt.lat) && !isNaN(pt.lon);
    }

    const latlngs = [];

    // Marker Départ
    if (hasCoords(depart)) {
        L.marker([depart.lat, depart.lon])
            .addTo(map)
            .bindPopup("Départ : " + depart.name);

        latlngs.push([depart.lat, depart.lon]);
    }

    // Marker Destination
    if (hasCoords(destination)) {
        L.marker([destination.lat, destination.lon])
            .addTo(map)
            .bindPopup("Destination : " + destination.name);

        latlngs.push([destination.lat, destination.lon]);
    }

    // Trace si 2 points
    if (latlngs.length === 2) {

        const routing = L.Routing.control({
            waypoints: [
                L.latLng(depart.lat, depart.lon),
                L.latLng(destination.lat, destination.lon)
            ],
            lineOptions: {
                styles: [{ color: 'red', opacity: 0.8, weight: 3 }]
            },
            show: false,
            addWaypoints: false,
            draggableWaypoints: false,
            fitSelectedRoutes: 'smart'
        })
        .on('routesfound', function(e) {
            const route = e.routes[0];
            const dureeSec = route.summary.totalTime;
            const dureeMin = Math.round(dureeSec / 60);

            // Affichage durée
            document.getElementById('dureeTrajet').textContent = dureeMin + " minutes";
            document.getElementById('sectionDuree').style.display = "block";
        })
        .addTo(map);

    } else if (latlngs.length === 1) {
        map.setView(latlngs[0], 14);
    }

});
</script>
</body>
</html>