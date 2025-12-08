<?php
require __DIR__ . '/../vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

try {
    // Configuration SMTP
    $mail->isSMTP();
    $mail->Host       = 'smtp.exemple.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'ton-email@example.com';
    $mail->Password   = 'ton-motdepasse';
    $mail->SMTPSecure = 'tls';
    $mail->Port       = 587;

    // Destinataire et contenu
    $mail->setFrom('ton-email@example.com', 'Ton Nom');
    $mail->addAddress('destinataire@example.com', 'Destinataire');
    $mail->Subject = 'Test PHPMailer';
    $mail->Body    = 'Ceci est un test d\'envoi d\'email via PHPMailer.';

    $mail->send();
    echo 'Message envoyé avec succès !';
} catch (Exception $e) {
    echo "Erreur lors de l'envoi : {$mail->ErrorInfo}";
}

