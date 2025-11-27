<?php include('../includes/validerChamps.php');

$errors = []; // Initialise le tableau des erreurs

// Traitement de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $errors;

    try {
        // Stockage des données dans la session
        $data['nom'] = $_POST['nom'] ?? '';
        $data['prenom'] = $_POST['prenom'] ?? '';
        $data['email'] = $_POST['email'] ?? '';
        $data['telephone'] = $_POST['telephone'] ?? '';
        $data['password'] = $_POST['password'] ?? '';
        $data['verifPassword'] = $_POST['verifPassword'] ?? '';

        // Validation des champs
        validateField($data, 'nom', 'Nom', ['required' => true]);
        validateField($data, 'prenom', 'Prenom', ['required' => true]);
        validateField($data, 'email', 'Email', ['required' => true, 'email' => true]);
        validateField($data, 'telephone', 'Téléphone', ['required' => true, 'max_length' => 10, 'min_value' => 10]);
    } catch (Exception $e) {
        // Capture de l'exception et ajout d'un message d'erreur
        $errors[] = $e->getMessage();
    }

    // Affichage des erreurs (si nécessaire)
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red;'>$error</p>";
        }
    }
}

// Affichage des erreurs (si nécessaire)
if (!empty($errors)) {
    foreach ($errors as $error) {
        echo "<p style='color:red;'>$error</p>";
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Inscription à StudyGo</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="../css/styleCompte.css">
</head>
<body>
    <main class="container container-infos">
        <header class="titre">
            <h2>Inscription à StudyGo</h2>
            <div class="text-center">
                L'appli de co-voiturage dans la région ammiennoise pour les étudiants de l'IUT UPJV que vous allez adorer !
            </div>
            <div class="text-center">
                Vous êtes nouveau ? Alors n'hésitez pas à vous inscrire, c'est rapide et 100% gratuit !
            </div>
        </header>
        <section class="shadow p-4 rounded saisie-infos">

            <form method="creerCompte.php" action="POST">
                <div class="row">
                    <div class="col">
                        <label for="nom">Nom</label>
                        <input type="text" id="nom" name="nom" required />
                    </div>
                    <div class="col">
                        <label for="prenom">Prénom</label>
                        <input type="text" id="prenom" name="prenom" required />
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required />
                    </div>
                    <div class="col">
                        <label for="telephone">Téléphone</label>
                        <input type="text" id="telephone" name="telephone" placeholder="0699994810" required />
                    </div>
                </div>

                <div class="row">
                    <div class="col">
                        <label for="password">Mot de passe</label>
                        <input type="password" id="password" name="password" required />
                    </div>
                    <div class="col">
                        <label for="verifPassword">Confirmer votre mot de passe</label>
                        <input type="password" id="verifPassword" name="verifPassword" required />
                    </div>
                </div>

                <button type="submit">Créer le compte</button>
            </form>

            <div class="text-center">
                <p class="fs-6 form-text">Vous avez déjà un compte ?
                    <a href="connexion.html" class="text-decoration-none">Se connecter</a>
                </p>
            </div>

            <div class="text-center">
                <p class="fs-6 form-text">
                    En créant un compte, vous acceptez nos conditions d’utilisation qui garantissent une expérience
                    respectueuse et responsable pour tous. Nous vous invitons à consulter nos règles de sécurité,
                    nos politiques d’annulation et notre charte de bonne conduite afin de profiter du service en toute confiance.
                </p>
            </div>

        </section>
    </main>
</body>

</html>
