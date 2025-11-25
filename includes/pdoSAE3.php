<?php

$dbname='studygo_bd';
$host='mysql-studygo.alwaysdata.net';
$port='3306';
$nom_utilisateur='studygo';
$mdp='SAEStudyGo3?';

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
