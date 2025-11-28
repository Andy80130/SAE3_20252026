<?php





//début du script
require "pdoSAE3.php";
session_start();


die("Fonction appelée !");




// Router
$action = $_GET['action'] ?? $_POST['action'] ?? null;

$routes = [
    'permut'   => 'InverseDepDest',
    'reinitialiser'  => ' ',
    'creation' => ' '
];
//A faire






if (isset($routes[$action])) {
    call_user_func($routes[$action]);
} else {
    redirectToForm(); // fallback
    //apparament utilisé uniquement en POST
}




//inverser départ/destination
function InverseDepDest() {
    $depart = $_POST['depart'] ?? '';
    $destination = $_POST['destination'] ?? '';
    $depart_lat = $_POST['depart_lat'] ?? '';
    $depart_lon = $_POST['depart_lon'] ?? '';
    $destination_lat = $_POST['destination_lat'] ?? '';
    $destination_lon = $_POST['destination_lon'] ?? '';
    $date = $_POST['date'] ?? '';
    $start = $_POST['start'] ?? '';
    $end = $_POST['end'] ?? '';
    $places = $_POST['places'] ?? '1';

    $params = http_build_query([
        'depart' => $destination,
        'depart_lat' => $destination_lat,
        'depart_lon' => $destination_lon,
        'destination' => $depart,
        'destination_lat' => $depart_lat,
        'destination_lon' => $depart_lon,
        'date' => $date,
        'start' => $start,
        'end' => $end,
        'places' => $places
    ]);

    header("Location: creerTrajet.php?" . $params);
    exit();

}

/*

//Reset
function handleReset() {
    header("Location: creerTrajet.php");
    exit();
}

// 🛣️ Fonction pour créer un trajet
function handleCreate() {
    global $db;

    if (!isset($_SESSION['user_id'])) {
        header("Location: accueil.html");
        exit();
    }

    $depart = $_POST['depart'] ?? '';
    $destination = $_POST['destination'] ?? '';
    $date = $_POST['date'] ?? '';
    $start = $_POST['start'] ?? '';
    $end = $_POST['end'] ?? '';
    $places = $_POST['places'] ?? '';
    $certify = isset($_POST['certify']);

    $errors = validateTrip($depart, $destination, $date, $start, $end, $certify);

    if (!empty($errors)) {
        redirectToForm([
            'depart' => $depart,
            'destination' => $destination,
            'date' => $date,
            'start' => $start,
            'end' => $end,
            'places' => $places,
            'errors' => implode('|', $errors)
        ]);
    }

    try {
        $sql = "INSERT INTO journeys (user_id, depart, destination, date_trajet, heure_debut, heure_fin, nb_places, date_creation)
                VALUES (:u, :d, :dst, :dt, :hd, :hf, :nb, NOW())";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':u' => $_SESSION['user_id'],
            ':d' => $depart,
            ':dst' => $destination,
            ':dt' => $date,
            ':hd' => $start,
            ':hf' => $end,
            ':nb' => $places
        ]);

        header("Location: creerTrajet.php?success=1");
        exit();
    } catch (PDOException $e) {
        $errors[] = "Erreur lors de la création du trajet.";
        redirectToForm([
            'depart' => $depart,
            'destination' => $destination,
            'date' => $date,
            'start' => $start,
            'end' => $end,
            'places' => $places,
            'errors' => implode('|', $errors)
        ]);
    }
}

// ✅ Validation des champs
function validateTrip($depart, $destination, $date, $start, $end, $certify) {
    $errors = [];

    if ($depart === $destination) {
        $errors[] = "Départ et destination identiques.";
    }
    if (!$date) {
        $errors[] = "Date obligatoire.";
    }
    if (!$start) {
        $errors[] = "Heure début obligatoire.";
    }
    if (!$end) {
        $errors[] = "Heure fin obligatoire.";
    }
    if ($start >= $end) {
        $errors[] = "Heure début après l'heure de fin.";
    }
    if (!$certify) {
        $errors[] = "Veuillez certifier les informations.";
    }

    return $errors;
}

// 🔁 Redirection vers le formulaire avec paramètres
function redirectToForm($params = []) {
    $query = !empty($params) ? '?' . http_build_query($params) : '';
    header("Location: creerTrajet.php" . $query);
    exit();
}



