<?php
global $db;
require "pdoSAE3.php";

if ($db->connect_error) {
    die("Erreur de connexion : " . $db->connect_error);
}

// Vérification que les champs existent
if (
    isset($_POST['nom']) &&
    isset($_POST['prenom']) &&
    isset($_POST['email']) &&
    isset($_POST['password'])
) {

    $nom = htmlspecialchars($_POST['nom']);
    $prenom = htmlspecialchars($_POST['prenom']);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Préparation de la requête SQL sécurisée
    $stmt = $db->prepare("INSERT INTO users (last_name, first_name, mail, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $nom, $prenom, $email, $password);

    if ($stmt->execute()) {
        echo "✔ Compte créé avec succès !";
    } else {
        echo "❌ Erreur : " . $stmt->error;
    }

    $stmt->close();
}

$db->close();

?>
