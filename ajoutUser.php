<?php	require('../../includes/pdoSAE3.php');

$stmt = $db->prepare("insert into utilisateur values(:nom,:prenom,:mail,:mdp)");

//Quand le mec appuie sur "s'inscrire", les infos sont recup et envoye dans le execute qui enverra les donnees dans la base


$stmt->execute(array(:nom'=>,null':prenom'=>null,':mail'=>null,':mdp'=>null));

?>