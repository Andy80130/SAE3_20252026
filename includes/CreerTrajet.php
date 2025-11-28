<?php
require "pdoSAE3.php";
session_start();


// Router
$action = $_GET['action'] ?? $_POST['action'] ?? null;

$routes = [
    'afficher' => 'afficheTrajet',
    'permut'   => 'InverseDepDest',
    'reinitialiser'  => 'reinitialiserTrajet',
    'creation' => 'creerTrajet'
];
//A faire



if (isset($routes[$action])) {
    call_user_func($routes[$action]);
} else {
    redirectToForm(); // fallback
    //apparament utilisé uniquement en POST
}




//Afficher trajet
//inverser départ/destination
function afficheTrajet() {
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
        'depart' => $depart,
        'depart_lat' => $depart_lat,
        'depart_lon' => $depart_lon,
        'destination' => $destination,
        'destination_lat' => $destination_lat,
        'destination_lon' => $destination_lon,
        'date' => $date,
        'start' => $start,
        'end' => $end,
        'places' => $places
    ]);

    header("Location: creerTrajet.php?" . $params);
    exit();

}



function validateTrajetForm($data) {

    $errors = [];

    if (empty($data['depart'])){
        $errors[] = "Le champ 'Départ' est requis.";
    }            

    if (empty($data['destination'])){
        $errors[] = "Le champ 'Destination' est requis.";
    }

    if (empty($data['depart_lat']) || empty($data['depart_lon'])){
        $errors[] = "Les coordonnées du départ sont manquantes.";

    }

    if (empty($data['destination_lat']) || empty($data['destination_lon'])){
        $errors[] = "Les coordonnées de la destination sont manquantes.";

    }

    if (empty($data['date'])){
        $errors[] = "Veuillez renseigner une date.";
    }             

    if (empty($data['start']) || empty($data['end'])){
        $errors[] = "Veuillez saisir une plage horaire.";
    }

    if (empty($data['places'])){
        $errors[] = "Le nombre de places est obligatoire.";
    }            
    if (!isset($data['certify'])){
        $errors[] = "Vous devez certifier que les informations sont exactes.";
    }          

    return $errors;
}


function creerTrajet() {

    $errors = validateTrajetForm($_POST);

    if (!empty($errors)) {
        $params = http_build_query([
            'errors' => implode('|', $errors),
            'depart' => $_POST['depart'] ?? '',
            'destination' => $_POST['destination'] ?? '',
            'depart_lat' => $_POST['depart_lat'] ?? '',
            'depart_lon' => $_POST['depart_lon'] ?? '',
            'destination_lat' => $_POST['destination_lat'] ?? '',
            'destination_lon' => $_POST['destination_lon'] ?? '',
            'date' => $_POST['date'] ?? '',
            'start' => $_POST['start'] ?? '',
            'end' => $_POST['end'] ?? '',
            'places' => $_POST['places'] ?? '1'
        ]);

        header("Location: creerTrajet.php?$params");
        exit();
    }

    // Ajouter le trajet à la base de données( je ferais après)


    header("Location: creerTrajet.php?success=1");
    exit();
}

