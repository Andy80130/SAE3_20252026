<?php

$dbname='sae3_20252026';
$host='localhost';
$port='3306';
$nom_utilisateur='root';
$mdp='';

try{
    $db = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8",
        $nom_utilisateur,
        $mdp
    );
    $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
}catch(Throwable $e){
    die($e->getMessage());
}

?>