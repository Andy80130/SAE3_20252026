<?php	require('includes/pdoSAE3.php');

$stmt = $db->prepare("insert into utilisateur values(:nom,:prenom,:mail,:mdp)");

//Quand le mec appuie sur "s'inscrire", les infos sont recup et envoye dans le "execute" qui enverra les donnees dans la base

$nom = null;
$prenom = null;
$mail = null;
$mdp = null;

//Recup des infos + vérif (à réussir mdr)
list <listInfosRequises> (
    $nom=>$_SESSION['nom'],
    $prenom=>$_SESSION['prenom'],
    $mail=>$_SESSION['mail'],
    $mdp=>$_SESSION['mdp']
): array

foreach (listInfosRequises as $val) {
    if($val == NULL) {

    } else {

    }
}

//Envoi des données pour créer l'utilisateur
$stmt->execute(array(:nom=>$_SESSION['nom'],:prenom=>$_SESSION['prenom'],:mail=>$_SESSION['mail'],:mdp=>$_SESSION['mdp']));

?>