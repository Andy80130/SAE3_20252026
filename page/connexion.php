<?php
session_start();
include('../includes/validerChamps.php');
include('../includes/GestionBD.php');
include('../includes/cryptage.php');

$errors = [];

// Traitement de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $errors;

    try {
        if (isset($_POST['submit'])) {
            // Stockage des données
            $data['email'] = $_POST['email'] ?? '';
            $data['password'] = $_POST['password'] ?? '';

            validateField($data, 'email', 'Email', ['required' => true, 'email' => true]);
            validateField($data, 'password', 'Mot de passe', ['required' => true]);

            if(MailExist($data['email'])){
                $userInfo = GetUserInfo($data['email']);
                $password = verifierMotDePasse($data['password'], $userInfo['password']);

                if ($password) {
                    $_SESSION['user_id'] = $userInfo['user_id'];
                    $_SESSION['mail'] = $userInfo['mail'];

                    if (isset($_SESSION['user_id']) || isset($_SESSION['mail'])) {
                        header('Location: accueil.php');
                        exit();
                    }
                } else {
                    throw new Exception("Mot de passe incorrect.");
                }
            }else {
                throw new Exception("Aucun compte n'est associé à cet email.");
            }
        } else {
            throw new Exception("Une erreur s'est produite.");
        }
    } catch (Exception $e) {
        // Capture de l'exception et ajout d'un message d'erreur
        $errors[] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <title>Connexion à StudyGo</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <link rel="stylesheet" href="../css/styleCompte.css">
</head>
<body>
<main class="my-5 text-start container container-infos">
    <header class="shadow mb-3 p-3 rounded d-flex justify-content-center align-items-center titre">
        <h2>Connexion à StudyGo</h2>
        <div class="text-center">
            L'appli de co-voiturage dans la région ammiennoise pour les étudiants de l'IUT UPJV !
        </div>
    </header>
    <?php
    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<p style='color:red; text-align: center;'>$error</p>";
        }
    }
    ?>
    <section class="shadow p-4 rounded saisie-infos">

        <form method="POST" action="">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" required />
            <label for="password">Mot de passe</label>
            <input type="password" id="password" name="password" required />
            <button type="submit" name="submit">Connexion</button>
        </form>

        <div class="text-center">
            <p class="fs-6 form-text">Vous n'avez pas de compte ?
                <a href="inscription.php" class="text-decoration-none">Créer un compte !</a>
            </p>
        </div>

        <div class="text-center">
            <p class="fs-6 form-text">
                En accédant à l'application, vous acceptez nos conditions d’utilisation qui garantissent une expérience
                respectueuse et responsable pour tous. Nous vous invitons à consulter nos règles de sécurité,
                nos politiques d’annulation et notre charte de bonne conduite afin de profiter du service en toute confiance.
            </p>
        </div>

    </section>
</main>
</body>

</html>