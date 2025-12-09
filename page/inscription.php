<?php
session_start();
include('../includes/validerChamps.php');
include('../includes/GestionBD.php');
include('../includes/cryptage.php');
require '../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$errors = [];

// Traitement de la requête POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    global $errors;

    try {
        if (isset($_POST['submit'])) {
            // Stockage des données
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
            validateField($data, 'telephone', 'Téléphone', ['required' => true, 'max_length' => 10, 'min_length' => 10, 'validate_float' => true]);
            validateField($data, 'password', 'Mot de passe', ['required' => true]);
            validateField($data, 'verifPassword', '2ème mot de passe', ['required' => true, 'verify_password' => true]);

            $password = hacherMotDePasse($data['password']);

            //addUser
            if(empty($errors) && !IsMailBL($data['email']) && !MailExist($data['email'])) {
                AddUser($data['nom'], $data['prenom'], $data['email'], $data['telephone'], $password);

                $userInfo = GetUserInfo($data['email']);

                if($userInfo) {
                    $_SESSION['user_id'] = $userInfo['user_id'];
                    $_SESSION['nom'] = $userInfo['last_name'];
                    $_SESSION['prenom'] = $userInfo['first_name'];
                    $_SESSION['mail'] = $userInfo['mail'];
                    $_SESSION['admin_flag'] = $userInfo['admin_flag'];

                    //Envoi du mail
                    $mail = new PHPMailer(true);

                    try {
                        // Configuration SMTP
                        $mail->isSMTP();
                        $mail->Host       = 'smtp.gmail.com';
                        $mail->SMTPAuth   = true;
                        $mail->Username   = 'StudyGoSAE@gmail.com';
                        $mail->Password   = 'eqvj gioa rcko rddi';
                        $mail->SMTPSecure = 'tls';
                        $mail->Port       = 587;
                        $mail->SMTPOptions = [
                            'ssl' => [
                                'verify_peer' => false,
                                'verify_peer_name' => false,
                                'allow_self_signed' => true,
                            ],
                        ];

                        // Destinataire et contenu
                        $mail->setFrom($mail -> Username, 'StudyGo');
                        $mail->addAddress($data['email']);
                        $mail->Subject = 'Bienvenue sur mon application !';
                        $mail->isHTML(true);
                        $mail->Body = htmlspecialchars("
                                        <h2>Merci pour votre inscription !</h2>
                                        <p>Nous sommes très heureux de vous compter parmi nous et 
                                        vous souhaitons une bonne expérience sur notre application !</p>
                                        <p>Cet email est automatique, merci de ne pas répondre.</p>
                                    ");
                        $mail->send();
                        echo 'Message envoyé avec succès !';
                    } catch (Exception $e) {
                        $errors[] = "Erreur lors de l'envoi" . $mail->ErrorInfo;
                    }
                }

                if (isset($_SESSION['user_id']) || isset($_SESSION['mail'])) {
                    header('Location: accueil.php');
                    exit();
                }
            } else {
                throw new Exception("Un compte existe déjà avec cet email.");
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
    <title>Inscription à StudyGo</title>
    <meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        <?php
        if (!empty($errors)) {
            foreach ($errors as $error) {
                echo "<p style='color:red; text-align: center;'>$error</p>";
            }
        }
        ?>
        <section class="shadow p-4 rounded saisie-infos">

            <form method="POST" action="">
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

                <button type="submit" name="submit">Créer le compte</button>
            </form>

            <div class="text-center">
                <p class="fs-6 form-text">Vous avez déjà un compte ?
                    <a href="connexion.php" class="text-decoration-none">Se connecter</a>
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