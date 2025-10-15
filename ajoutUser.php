<?php	require('../../includes/pdoSAE3.php');

$stmt = $db->prepare("insert into utilisateur values(:nom,:prenom,:mail,:mdp)");




$stmt->execute(array(:nom'=>,null':prenom'=>null,':mail'=>null,':mdp'=>null));

?>