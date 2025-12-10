<?php
session_start();

require "../includes/pdoSAE3.php";
require "../includes/GestionBD.php";

// Tronque adresse pour BD
function tronqueAdressePourBD(string $address): string {
    $chaineTravail = trim($address);
    $pos1 = strpos($chaineTravail, ',');
    if ($pos1 === false) return $chaineTravail;
    
    $pos2 = strpos($chaineTravail, ',', $pos1 + 1);
    if ($pos2 === false) return $chaineTravail;

    $pos3 = strpos($chaineTravail, ',', $pos2 + 1);
    if ($pos3 !== false) return trim(substr($chaineTravail, 0, $pos3));
    
    return $chaineTravail;
}

//ERREURS
$errors = isset($_GET['errors']) ? explode('|', $_GET['errors']) : [];
$success = isset($_GET['success']);

// Valeurs
$depart      = $_POST['depart'] ?? '';
$destination = $_POST['destination'] ?? '';
$date        = $_POST['date'] ?? '';
$start       = $_POST['start'] ?? '';
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

    // Validations de base
    if (empty($depart) || empty($destination)) $errors_submit[] = "Les adresses de départ et d'arrivée sont requises.";
    if (empty($date)) $errors_submit[] = "La date est requise.";
    if (empty($start)) $errors_submit[] = "L'heure de départ est requise.";
    if (!isset($_POST['certify'])) $errors_submit[] = "Veuillez certifier l'exactitude des informations.";

    // Validation Date/Heure (Supérieur à maintenant)
    $date_actuelle = new DateTime();
    $trajet_date_str = $date . ' ' . $start;
    $trajet_datetime = new DateTime($trajet_date_str);

    if ($trajet_datetime <= $date_actuelle) {
        $errors_submit[] = "La date et l'heure du trajet doivent être supérieures à la date actuelle.";
    }

    // Validation Adresses IUT
    $IUT_Address = "IUT Amiens, Avenue des Facultés, Salouël, Amiens, Somme, Hauts-de-France, France métropolitaine, 80480, France";
    if(trim($depart) === trim($destination)) $errors_submit[] = "Le départ et la destination ne peuvent pas être identiques.";
    if(trim($depart) !== $IUT_Address && trim($destination) !== $IUT_Address) $errors_submit[] = "Le départ ou la destination doit être l'IUT d'Amiens";

    // Validation Véhicule Conducteur
    $driver_id = $_SESSION['user_id'] ?? 1;
    $driver_mail = $_SESSION['mail'] ?? '';

    if(!empty($driver_mail)) {
        $userInfo = GetUserInfo($driver_mail);
        if($userInfo == null || empty($userInfo['vehicle_model'])){
            $errors_submit[] = "Vous devez posséder un véhicule pour créer un trajet.";
        }
    } 

    // Enregistrement
    if (empty($errors_submit)) {
        // MODIFICATION : On utilise uniquement $start pour l'heure de départ
        $start_datetime_db = $date . ' ' . $start . ':00';
        
        $valDepBD  = tronqueAdressePourBD($depart);
        $valDestBD = tronqueAdressePourBD($destination);

        $result = AddJourney($valDepBD, $valDestBD, $start_datetime_db, (int)$places, (int)$driver_id);
        if ($result) {
            header('Location: CreerTrajet.php?success=1');
            exit;
        } else {
            $errors_submit[] = "Erreur lors de la création.";
        }
    }

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <link rel="stylesheet" href="../css/styleCreerTrajet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <script src="https://unpkg.com/leaflet-routing-machine/dist/leaflet-routing-machine.js"></script>
</head>
<body>
    
    <?php require ('../includes/header.php'); ?>

    <main class="main-content">

        <h1 class="TitreCreer">Créer un trajet</h1>

        <section class="hero">
            <div class="card">
                <div class="title">Itinéraire</div>
                
                    <div class="itinerary">
                        <div class="field">
                            <div class="labelOrange">Départ</div>
                                <input type="text" id="depart" placeholder="Saisir un arrêt ou une adresse" class="form-input" value="<?= htmlspecialchars($depart) ?>" autocomplete="off">
                            <div id="suggestions-depart" class="suggestions"></div>
                                 <input type="hidden" id="depart_lat" value="<?= htmlspecialchars($departData['lat']) ?>">
                                <input type="hidden" id="depart_lon" value="<?= htmlspecialchars($departData['lon']) ?>">
                         </div>

                    <div class="field">
                        <div class="labelOrange">Destination</div>
                        <input type="text" id="destination" placeholder="Saisir un arrêt ou une adresse" class="form-input" value="<?= htmlspecialchars($destination) ?>" autocomplete="off">
                        <div id="suggestions-destination" class="suggestions"></div>
                        <input type="hidden" id="destination_lat" value="<?= htmlspecialchars($destinationData['lat']) ?>">
                        <input type="hidden" id="destination_lon" value="<?= htmlspecialchars($destinationData['lon']) ?>">
                    </div>
                </div>
            </div>
        </section>

    <div class="affiche">
        <button type="button" onclick="refreshMap()">Afficher le trajet</button> 
    </div>

        <div class="map-container">
            <div id="map"></div>
        </div>

        <section class="hero" id="sectionDuree" style="display:none;">
            <div class="card">
                <div class="title">Durée approximative</div>
                <div id="dureeTrajet">...</div>
            </div>
        </section>

        <section class="hero">
            <div class="card">
                <div class="title">Informations de trajet</div>

                <form class="trip-form" method="post" action="CreerTrajet.php">
                    <input type="hidden" name="action" value="creation">
                    <input type="hidden" name="depart" id="depart_value" value="<?= htmlspecialchars($depart) ?>">
                    <input type="hidden" name="depart_lat" id="depart_lat_value" value="<?= htmlspecialchars($departData['lat']) ?>">
                    <input type="hidden" name="depart_lon" id="depart_lon_value" value="<?= htmlspecialchars($departData['lon']) ?>">
                    <input type="hidden" name="destination" id="destination_value" value="<?= htmlspecialchars($destination) ?>">
                    <input type="hidden" name="destination_lat" id="destination_lat_value" value="<?= htmlspecialchars($destinationData['lat']) ?>">
                    <input type="hidden" name="destination_lon" id="destination_lon_value" value="<?= htmlspecialchars($destinationData['lon']) ?>">


                    <div class = "trip-fields"><?php //nouv field?>

                    <div class="form-group">
                        <label for="date" class="form-label">Date du trajet</label>
                        <input type="date" id="date" name="date" class="form-input" value="<?= htmlspecialchars($date) ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Heure de départ</label>
                        <input type="time" name="start" class="form-input" value="<?= htmlspecialchars($start) ?>">
                    </div>

                    <div class="form-group">
                        <label for="places" class="form-label">Places disponibles</label>
                        <select id="places" name="places" class="form-input">
                            <option value="1" <?= $places === '1' ? 'selected' : '' ?>>1</option>
                            <option value="2" <?= $places === '2' ? 'selected' : '' ?>>2</option>
                            <option value="3" <?= $places === '3' ? 'selected' : '' ?>>3</option>
                            <option value="4" <?= $places === '4' ? 'selected' : '' ?>>4</option>
                            <option value="5" <?= $places === '5' ? 'selected' : '' ?>>5</option>
                            <option value="6" <?= $places === '6' ? 'selected' : '' ?>>6</option>
                        </select>
                    </div>

                    </div> <?php //FIN nouv? field>?>

                    <div class="infosCertif">
                        <span style="font-size:13px; font-weight:bold; color:#555;">Je certifie que les informations ci-dessus sont exactes</span>
                        <label class="custom-checkbox">
                            <input type="checkbox" name="certify">
                            <span class="checkmark"></span>
                        </label>
                    </div>

                    <?php if (!empty($errors)): ?>
                        <div class="errors" style="background: #ffe6e6; padding: 15px; border-radius: 8px; margin-top: 15px; border-left: 4px solid #d93025;">
                            <?php foreach ($errors as $error): ?>
                                <p style="color: #d93025; margin: 2px 0; font-size:14px;">• <?= htmlspecialchars($error) ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($success): ?>
                        <div class="success" style="background: #e6fffa; padding: 15px; border-radius: 8px; margin-top: 15px; border-left: 4px solid #00796b;">
                            <p style="color: #00796b; margin: 0; font-weight:bold;">Trajet créé avec succès !</p>
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
        
    </main>

    <?php require("../includes/footer.php")?>

    <script src="../js/autocomplete.js"></script>
    <script src="../js/reinitialiser.js"></script>
    <script src="../js/CreerTrajet.js"></script>

    <script>
    // Initialisation de la carte
    const map = L.map('map').setView([49.8942, 2.2957], 13);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    let routingControl = null; // Variable globale pour la gestion du contrôle d'itinéraire

    function hasCoords(pt) { return !isNaN(pt.lat) && !isNaN(pt.lon); }

    /**
     * Lit les coordonnées des champs cachés actuels et rafraîchit la carte.
     * Cette fonction est appelée par le bouton "Afficher le trajet" et par l'autocomplétion.
     */
    function refreshMap() {
        // Lecture des coordonnées et noms des champs non soumis
        const departName = document.getElementById('depart').value;
        const destinationName = document.getElementById('destination').value;
        const departLat = parseFloat(document.getElementById('depart_lat').value);
        const departLon = parseFloat(document.getElementById('depart_lon').value);
        const destinationLat = parseFloat(document.getElementById('destination_lat').value);
        const destinationLon = parseFloat(document.getElementById('destination_lon').value);
        
        // Nettoyer la carte (enlever le contrôle précédent)
        if (routingControl) {
            map.removeControl(routingControl);
            routingControl = null;
        }
        
        // Supprimer tous les marqueurs existants
        map.eachLayer(function (layer) {
            if (layer instanceof L.Marker) {
                map.removeLayer(layer);
            }
        });
        
        // Cacher la section de durée
        document.getElementById('sectionDuree').style.display = "none";
        
        const depart = { name: departName, lat: departLat, lon: departLon };
        const destination = { name: destinationName, lat: destinationLat, lon: destinationLon };

        const latlngs = [];
        if (hasCoords(depart)) {
            L.marker([depart.lat, depart.lon]).addTo(map).bindPopup("Départ : " + depart.name);
            latlngs.push([depart.lat, depart.lon]);
        }
        if (hasCoords(destination)) {
            L.marker([destination.lat, destination.lon]).addTo(map).bindPopup("Destination : " + destination.name);
            latlngs.push([destination.lat, destination.lon]);
        }

        if (latlngs.length === 2) {
            // Afficher et calculer l'itinéraire
            routingControl = L.Routing.control({
                waypoints: [ L.latLng(depart.lat, depart.lon), L.latLng(destination.lat, destination.lon) ],
                lineOptions: { styles: [{ color: 'red', opacity: 0.8, weight: 3 }] },
                show: false, addWaypoints: false, draggableWaypoints: false, fitSelectedRoutes: 'smart'
            })
            .on('routesfound', function(e) {
                const route = e.routes[0];
                const dureeMin = Math.round(route.summary.totalTime / 60);
                document.getElementById('dureeTrajet').textContent = dureeMin + " minutes";
                document.getElementById('sectionDuree').style.display = "block";
            })
            .addTo(map);
        } else if (latlngs.length > 0) {
            // Centrer sur le seul point connu
            map.setView(latlngs[0], 14);
        } else {
            // Aucun point, revenir à la vue par défaut
            map.setView([49.8942, 2.2957], 13);
        }
    }


    document.addEventListener('DOMContentLoaded', function() {
        // Au chargement, si des coordonnées existent (suite à un rechargement par PHP), affichez la carte.
        // On vérifie le champ caché principal du formulaire de création.
        const initialDepartLat = parseFloat(document.getElementById('depart_lat_value').value);
        if (!isNaN(initialDepartLat)) {
            refreshMap();
        }
    });
</script>
</body>
</html>