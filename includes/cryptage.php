<?php

function hacherMotDePasse($motDePasseEnClair) {
    $hash = password_hash($motDePasseEnClair, PASSWORD_DEFAULT);

    if ($hash === false) {
        throw new Exception("Erreur lors du hachage du mot de passe.");
    }

    return $hash;
}

function verifierMotDePasse($motDePasseSaisi, $hashStockeEnBDD) {
    if (password_verify($motDePasseSaisi, $hashStockeEnBDD)) {
        return true;
    } else {
        return false;
    }
}

?>