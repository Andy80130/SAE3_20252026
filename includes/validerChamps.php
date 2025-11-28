<?php
function validateField($data, $field, $label, $rules) {

    foreach ($rules as $rule => $value) {
        switch ($rule) {
            case 'required':
                if (empty($data[$field])) {
                    throw new Exception("Le champ $label doit être rempli !");
                }
                break;
            case 'min_length':
                if (empty($data['email']) && strlen($data[$field]) < $value) {
                    throw new Exception("$label doit contenir au moins $value caractères");
                }
                break;
            case 'max_length':
                if (strlen($data[$field]) > $value) {
                    throw new Exception("$label ne doit pas dépasser $value caractères");
                }
                break;
            case 'regex':
                if (!preg_match($value, $data[$field])) {
                    throw new Exception("$label n'est pas valide !");
                }
                break;
            case 'min_value':
                if ($data[$field] < $value) {
                    throw new Exception("$label ne doit pas être en dessous de $value");
                }
                break;
            case 'validate_float':
                if (filter_var($data[$field], FILTER_VALIDATE_FLOAT) === false && empty($data['email'])) {
                    throw new Exception("$label doit être un nombre décimal");
                }
                break;
            case 'verify_password':
                if($data[$field] != $data['password']) {
                    throw new Exception("$label est différent de ton mot de passe !");
                }
            case 'email':
                // Vérification si l'email est valide
                if (!filter_var($data[$field], FILTER_VALIDATE_EMAIL) && empty($data['phone'])) {
                    throw new Exception("$label doit être un email valide !");
                }
                break;
        }
    }
}
?>