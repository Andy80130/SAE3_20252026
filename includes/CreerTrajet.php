<?php
require "pdoSAE3.php";
session_start();

$action = $_GET['action'] ?? $_POST['action'] ?? null;

switch ($action) {
    
    // BOUTON POUR SWITCHER LES VALEURS
    case "swap":
        $depart = $_POST['depart'] ?? 'iut';
        $destination = $_POST['destination'] ?? 'gare';
        $date = $_POST['date'] ?? '';
        $start = $_POST['start'] ?? '';
        $end = $_POST['end'] ?? '';
        $places = $_POST['places'] ?? '1';
        
        // Construction de l'URL avec tous les paramètres
        $params = http_build_query([
            'depart' => $destination,  // INVERSION ICI
            'destination' => $depart,  // INVERSION ICI
            'date' => $date,
            'start' => $start,
            'end' => $end,
            'places' => $places
        ]);
        
        header("Location: creerTrajet.php?" . $params);
        exit();
    
    // CRÉATION D'UN TRAJET
    case "create":
        // Vérifier la connexion
        if (!isset($_SESSION['user_id'])) {
            header("Location: accueil.html");
            exit();
        }
        
        // Récupération des données
        $depart = $_POST['depart'] ?? '';
        $destination = $_POST['destination'] ?? '';
        $date = $_POST['date'] ?? '';
        $start = $_POST['start'] ?? '';
        $end = $_POST['end'] ?? '';
        $places = $_POST['places'] ?? '';
        $certify = isset($_POST['certify']);
        
        // Validation
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
        
        // Si erreurs, redirection avec les données
        if (!empty($errors)) {
            $params = http_build_query([
                'depart' => $depart,
                'destination' => $destination,
                'date' => $date,
                'start' => $start,
                'end' => $end,
                'places' => $places,
                'errors' => implode('|', $errors)
            ]);
            header("Location: creerTrajet.php?" . $params);
            exit();
        }
        
        // INSERTION SQL
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
            $params = http_build_query([
                'depart' => $depart,
                'destination' => $destination,
                'date' => $date,
                'start' => $start,
                'end' => $end,
                'places' => $places,
                'errors' => implode('|', $errors)
            ]);
            header("Location: creerTrajet.php?" . $params);
            exit();
        }
}

// FAILSAFE - Si aucune action ou action inconnue
header("Location: creerTrajet.php");
exit();