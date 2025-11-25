<?php
global $db;
require "pdoSAE3.php";

if ($db->connect_error) {
    die("Erreur de connexion : " . $db->connect_error);
}

// Vérification que les champs existent
/* Gestion d'erreur + connexion à modif par rapport à notre bdd
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = $_POST['email'];
        $password = $_POST['password'];

        if ($inscription->isInscriptionExists($email)){
            $inscriptionData = $inscription->login($email);

            if($inscriptionData['status'] == 'A traiter') {
                throw new Exception('Ce compte est en cours de traitement.');
            } elseif ($inscriptionData['status'] == 'Refuse') {
                throw new Exception('Ce compte n\'est pas autorisée.');
            } elseif ($inscriptionData['status'] == 'Accepte' && $user->login($email) === false) {
                throw new Exception('Ce compte n\'est pas encore activé.');
            }
        } else {
            $_SESSION['email'] = $email;
            throw new Exception('Identifiant ou mot de passe incorrect.');
        }

        // Vérification des informations d'authentification
        $userData = $user->login($email);

        // Hacher le mot de passe entré par l'utilisateur avec SHA256
        $hashedPassword = hash('sha256', $password);*/

$db->close();

?>